<?php
/**
* Generate by SwaggerBundle (you can still edit this file!).
*/
namespace testNamespace\Controller;

use Enneite\SwaggerBundle\Controller\SwaggerApiController;
use Symfony\Component\HttpFoundation\Request;

class testClassNameController extends SwaggerApiController
{
    /**
     * description: test.
     *
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAction(Request $request)
    {
        $status = 500;
        // @todo : implement your business model here :";
        $data = array();

        return $this->sendJsonResponse($data, $status);
    }
}
