<?php

/**
 * Created by JetBrains PhpStorm.
 * User: bernard.caron
 * Date: 08/11/15
 * Time: 16:21
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Generator;

class ControllerGenerator extends Generator
{
    private $className;
    private $namespace;
    private $outputPath;
    private $entityGenerator;

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    public function getOutputPath()
    {
        return $this->outputPath;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    public function __construct($container, $filesystem)
    {
        parent::__construct($container, $filesystem);
        $this->entityGenerator = $this->container->get('enneite_swagger.entity_generator');
    }

    /**
     * @param $outputPath
     * @param $namespace
     * @param $className
     * @param $pathName
     * @param $path
     * @param $annotation
     */
    public function generate($outputPath, $namespace, $className, $pathName, $path, $annotation)
    {
        $this->className = $className.'Controller';
        $this->outputPath = $outputPath.'Controller/';
        $this->namespace = $namespace.'\Controller';
        $this->createController($this->outputPath, $this->className, $namespace, $pathName, $path, $annotation);
    }

    private function createController($outpuPath, $className, $namespace, $pathName, $path, $annotation)
    {
        $target = $outpuPath.$className.'.php';
        $template = 'Controller.php.twig';
        $params = $this->getControllerParams($className, $this->namespace, $namespace.'\Entity', $path, $pathName, $annotation);

        return $this->renderFile($template, $target, $params);
    }

    private function getControllerParams($className, $namespace, $resourceNamespace, $path, $pathName, $annotation)
    {
        $useArray = array(
            'Enneite\SwaggerBundle\Controller\SwaggerApiController',
            'Symfony\Component\HttpFoundation\Request',
            'Symfony\Component\HttpKernel\Exception',
            $resourceNamespace.' as Entity',
        );
        if ($annotation) {
            array_push($useArray, 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Route');
            array_push($useArray, 'Sensio\Bundle\FrameworkExtraBundle\Configuration\Method');
        }

        $actions = array();
        foreach ($path as $verb => $objects) {
            array_push($actions, $this->createAction($verb, $objects, $pathName, $annotation));
        }

        return array(
            'namespace' => $namespace,
            'use' => $useArray,
            'className' => $className,
            'actions' => $actions,
        );
    }

    /**
     * generate the action method PHP code.
     *
     * @param $verb
     * @param $objects
     * @param $pathName
     * @param $annotation
     *
     * @throws \Exception
     *
     * @return string
     */
    private function createAction($verb, $objects, $pathName, $annotation)
    {
        if (!isset($objects['responses'])) {
            throw new \Exception('"responses" attribute not found !');
        }

        $responses = $objects['responses'];
        $this->createActionLogic($responses, array_keys($responses), $verb, $objects);

        $template = 'controller/action.php.twig';
        $params = $this->getAction($verb, $objects, $pathName, $annotation);

        return $this->render($template, $params);
    }

    /**
     * get arguments for the template comiltation of an actio method.
     *
     * @param $verb
     * @param $objects
     * @param $pathName
     * @param $buildRoutingAnnotations
     *
     * @return array
     */
    public function getAction($verb, $objects, $pathName, $buildRoutingAnnotations)
    {
        $responses = $objects['responses'];
        $action = $this->createActionLogic($responses, array_keys($responses), $verb, $objects);

        return array(
            'verb' => $verb,
            'comments' => $this->getActionComments($verb, $objects, $pathName, $buildRoutingAnnotations),
            'arguments' => $this->getActionArguments($verb, $objects),
            'parameters' => $this->createActionParameters($verb, $objects),
            'action' => $action,
        );
    }

    /**
     * generate php code for the action method content with try/catch loops.
     *
     *
     * @param $responses
     * @param $statusCodes
     *
     * @return mixed
     */
    public function createActionLogic($responses, $statusCodes, $verb, $objects)
    {
        $template = 'controller/action/logic.php.twig';
        $params = $this->getActionLogic($responses, $statusCodes, $verb, $objects);

        return $this->render($template, $params);
    }

    public function getActionLogic($responses, $statusCodes, $verb, $objects)
    {
        $codes2xx = $this->extractCodesByStatus($statusCodes, 2);
        $codes3xx = $this->extractCodesByStatus($statusCodes, 3);
        $codes4xx = $this->extractCodesByStatus($statusCodes, 4);
        $codes5xx = $this->extractCodesByStatus($statusCodes, 5);

        if (count($codes2xx) > 0) {
            $status = $codes2xx[0];
            $response = $responses[$status];
            $schema = $this->createSchemaPhpCode($response);
            $exceptions = $this->createExceptions($responses);
        } else {
            $status = 500;
            if (isset($codes3xx[0])) {
                $status = $codes3xx[0];
            } elseif (isset($codes4xx[0])) {
                $status = $codes4xx[0];
            } elseif (isset($codes5xx[0])) {
                $status = $codes5xx[0];
            }
            $schema = null;
            $exceptions = '';
        }

        return array(
            'success' => (count($codes2xx) > 0),
            'parameters' => $this->createActionParameters($verb, $objects),
            'status' => $status,
            'schema' => $schema,
            'exceptions' => $exceptions,
        );
    }

    /**
     * generate Php code for the exceptions.
     *
     * @param $responses
     *
     * @return mixed
     */
    public function createExceptions($responses)
    {
        $template = 'controller/action/exceptions.php.twig';

        return $this->render($template, array(
            'exceptions' => $this->getExceptions($responses),
        ));
    }

    /**
     * @param $responses
     *
     * @return array
     */
    public function getExceptions($responses)
    {
        $exceptionsArray = array();

        $availableExceptions = $this->getAvailableExceptions();
        foreach ($responses as $code => $res) {
            if ($code >= 400) {
                $exceptionsArray[] = array(
                    'class' => (array_key_exists($code, $availableExceptions)) ? $availableExceptions[$code] : 'HttpException',
                    'status' => $code,
                    'schema' => $this->createSchemaPhpCode($res),
                );
            }
        }

        return $exceptionsArray;
    }

    /**
     * generate PHP code to imlements a schema for the response.
     *
     * @param $response
     *
     * @return string
     */
    public function createSchemaPhpCode($response)
    {
        $template = 'controller/action/schema.php.twig';
        $params = $this->getSchemaPhpCode($response);

        return $this->render($template, $params);
    }

    /**
     * @param $response
     *
     * @return array
     */
    public function getSchemaPhpCode($response)
    {
        if ($this->hasResponseEntity($response)) {
            $entity = $this->getAssociatedEntity($response);
            $method = 'buildResource';
        } elseif ($this->hasResponseCollection($response)) {
            $entity = $this->getAssociatedCollection($response);
            $entity = str_replace('Collection', '', $entity);
            $method = 'buildCollection';
        } elseif ($this->hasResponseSchema($response)) {
            $entity = null;
            $method = null;
        } else {
            $entity = null;
            $method = null;
        }

        return array(
            'entity' => $entity,
            'method' => $method,
        );
    }

    /**
     * get arguments for the action in an array of string (with $ prefix).
     *
     * @param $verb
     * @param $objects
     *
     * @return array
     */
    public function getActionArguments($verb, $objects)
    {
        $args = array();
        $parameters = (isset($objects['parameters'])) ? $objects['parameters'] : array();
        $parameters = array_filter($parameters, function ($parameter) {
            return 'path' == $parameter['in'];
        });

        foreach ($parameters as $parameter) {
            array_push($args, $this->entityGenerator->formatProperty($parameter['name']));
        }

        return $args;
    }

    /**
     * extract arguments from an Reflectionmethod object and format it like the getActionArguments($verb, $objects) method return!
     *
     * @param \ReflectionMethod $method
     *
     * @return array
     */
    public function extractArguments(\ReflectionMethod $method)
    {
        $a = array();
        $parameters = $method->getParameters();
        foreach ($parameters as $object) {
            $name = $object->getname();
            if ('request' != $name) {
                array_push($a, $name);
            }
        }

        return $a;
    }

    /**
     * generate php code for action method comments and annotations.
     *
     * @param $verb
     * @param $objects
     *
     * @return string
     */
    public function getActionComments($verb, $objects, $pathName, $buildRoutingAnnotations)
    {
        return array(
            'description' => (isset($objects['description'])) ? $objects['description'] : 'empty',
            'routing' => ($buildRoutingAnnotations) ? $this->getRoutingAnnotation($verb, $objects, $pathName) : null,
            'params' => $this->getActionArguments($verb, $objects),
        );
    }

    /**
     * generate routing annotation for this controller.
     *
     * @param $verb
     * @param $objects
     * @param $pathName
     */
    public function getRoutingAnnotation($verb, $objects, $pathName)
    {
        return array(
            'route' => $pathName,
//            'parameters' => $this->apiRoutingCreator->getRouteParametersAsArray($verb, $objects, $pathName),
            'method' => strtoupper($verb),
        );
    }

    /**
     * generate parameters PHP code  for the action method.
     *
     * @param $verb
     * @param $objects
     *
     * @return string
     */
    public function createActionParameters($verb, $objects)
    {
        $template = 'controller/action/parameters.php.twig';
        $parameters = $this->getActionParameters($verb, $objects);

        return $this->render($template, array(
            'parameters' => $parameters,
        ));
    }

    /**
     * @param $verb
     * @param $objects
     */
    public function getActionParameters($verb, $objects)
    {
        $parameters = (isset($objects['parameters'])) ? $objects['parameters'] : array();
        foreach ($parameters as $i => $parameter) {
            if (!isset($parameter['in'])) {
                throw new \Exception(' "in" attribute missing for parameter');
            }
            $parameters[$i]['description'] = (isset($parameter['description']) && $parameter['in'] != 'path') ? $parameter['description'] : null;
            $parameters[$i]['name'] = $this->entityGenerator->formatProperty($parameter['name']);
            $parameters[$i]['required'] = (isset($parameter['required'])) ? (bool) $parameter['required'] : false;
        }

        return $parameters;
    }

    /**
     * extract codes by status : 2 (OK), 3 (redirection) , (4 client error), 5 ((server error).
     *
     * @param $statusCodes
     * @param $statusPrefix => enabled values : 2, 3, 4
     *
     * @return array
     */
    public function extractCodesByStatus($statusCodes, $statusPrefix)
    {
        return array_values(array_filter($statusCodes, function ($value) use ($statusPrefix) {

            return 0 === strpos((string) $value, (string) $statusPrefix);
        }));
    }

    /**
     * return true if the response object has an "schema" attribute".
     *
     * @param array $response
     *
     * @return bool
     */
    public function hasResponseSchema($response)
    {
        return isset($response['schema']);
    }

    /**
     * return true if the response object has an "schema" attribute" and is associated to a entity definition in swagger configuration file.
     *
     * @param array $response
     *
     * @return bool
     */
    public function hasResponseEntity($response)
    {
        return $this->hasResponseSchema($response) && isset($response['schema']['$ref']);
    }

    public function hasResponseCollection($response)
    {
        $res = $this->hasResponseSchema($response) && isset($response['schema']['type']) && 'array' == $response['schema']['type'];

        if ($res) {
            if (!isset($response['schema']['items'])) {
                throw new \Exception(' "items" attribute is missing, schema\'s type is array');
            }
            $res = $res && isset($response['schema']['items']['$ref']);
        }

        return $res;
    }

    /**
     * get the entity associated to the response schema in swagger configuration file.
     *
     * @param $response
     *
     * @return bool
     */
    public function getAssociatedEntity($response)
    {
        if (!$this->hasResponseEntity($response)) {
            return false;
        }

        return $this->entityGenerator->extractEntity($response['schema']);
    }

    /**
     * get the collection entity associated to the response schema in swagger configuration file.
     *
     * @param $response
     *
     * @return bool
     */
    public function getAssociatedCollection($response)
    {
        if (!$this->hasResponseCollection($response)) {
            return false;
        }

        return $this->entityGenerator->extractEntity($response['schema']['items']).'Collection';
    }

    /**
     * get available symfony httpkernel exceptions.
     *
     * @return array
     */
    public function getAvailableExceptions()
    {
        return array(
            403 => 'AccessDeniedHttpException',
            400 => 'BadRequestHttpException',
            409 => 'ConflictHttpException',
            410 => 'GoneHttpException',
            411 => 'LengthRequiredHttpException',
            405 => 'MethodNotAllowedHttpException',
            406 => 'NotAcceptableHttpException',
            404 => 'NotFoundHttpException',
            412 => 'PreconditionFailedHttpException',
            428 => 'PreconditionRequiredHttpException',
            503 => 'ServiceUnavailableHttpException',
            429 => 'TooManyRequestsHttpException',
            401 => 'UnauthorizedHttpException',
            422 => 'UnprocessableEntityHttpException',
            415 => 'UnsupportedMediaTypeHttpException',
        );
    }
}
