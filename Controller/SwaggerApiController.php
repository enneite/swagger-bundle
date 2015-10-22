<?php

namespace Enneite\SwaggerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractController.
 */
class SwaggerApiController extends Controller
{
    /**
     * @param array $data
     * @param int   $status
     *
     * @return JsonResponse
     */
    protected function sendJsonResponse(array $data, $status = 200)
    {
        $response = new JsonResponse($data);
        $response->setStatusCode($status);

        return $response;
    }

    /**
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getJsonContent(Request $request)
    {
        $content = json_decode($request->getContent(), true);
        if (!is_array($content)) {
            throw new \InvalidArgumentException('bad json flow!');
        }

        return $content;
    }

    /**
     * @param string|\Symfony\Component\Form\FormTypeInterface $type
     * @param null                                             $data
     * @param array                                            $options
     *
     * @return \Symfony\Component\Form\Form
     */
    public function createForm($type, $data = null, array $options = array())
    {
        $options = $this->upgradeFormOptions($options);

        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    /**
     * Get request data with control for required field
     * @todo : add unit test
     *
     * @param Request $request
     * @param $dataName
     * @param $type
     * @param bool $required
     * @param null $defaultValue
     * @return null
     * @throws \InvalidArgumentException
     */
    protected function getRequestData(Request $request, $dataName, $type, $required = true, $defaultValue = null)
    {
        $types = array('json', 'query', 'files', 'request');

        if ($type == 'json') {
            $json = $this->getJsonContent($request);
            if (isset($json[$dataName])) {
                return $json[$dataName];
            }
        } elseif (in_array($type,$types)) {
            if ($request->$type->has($dataName)) {
                if($request->$type->get($dataName) == null && $required == true && $type == 'files') {
                    throw new \InvalidArgumentException($dataName . ' file is empty ');
                } else {
                    return $request->$type->get($dataName);
                }
            }
        } else {
            throw new \InvalidArgumentException('unknown type ' . $type);
        }

        if ($required) {
            throw new \InvalidArgumentException('parameter ' . $dataName . ' is missing in request!');
        } else {
            return $defaultValue;
        }
    }

    /**
     * no csrf token protection for REST API.
     *
     * @param $options
     *
     * @return mixed
     */
    protected function upgradeFormOptions($options)
    {
        $res = $options;
        unset($res['crsf_protection']);
        $res['csrf_protection'] = false;

        return $res;
    }

    /**
     * @param \Exception $e
     *
     * @return JsonResponse
     */
    protected function sendInternalError(\Exception $e)
    {
        $resp = $this->sendJsonResponse(array('error' => $e->getMessage(), 'class' => get_class($e)), 500); //var_dump($e->getMessage());
        return $resp;
    }
}
