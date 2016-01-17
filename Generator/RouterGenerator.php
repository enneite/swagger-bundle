<?php

/**
 * Created by JetBrains PhpStorm.
 * User: bernard.caron
 * Date: 08/11/15
 * Time: 18:55
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;

class RouterGenerator extends Generator
{
    /**
     * @param $verb
     * @param $objects
     * @param $pathName
     * @param $bundleName
     * @param $className
     *
     * @return string
     */
    public function createYamlConf($verb, $objects, $pathName, $bundleName, $className)
    {
        $parameters = $this->getRouteParametersAsArray($verb, $objects, $pathName);

        $conf = '';
        if (isset($objects['description'])) {
            $conf .= "\n# description: ".str_replace("\n", "\n#", $objects['description']);
        }
        $conf .= "\n".$parameters['name'].':';
        $conf .= "\n    pattern: ".$pathName;
        $conf .= "\n    methods: [".strtoupper($verb).']';
        $conf .= "\n    defaults:";
        $conf .= "\n        _controller: ".$bundleName.':'.$className.':'.$verb;

        $parameters = (isset($objects['parameters'])) ? $objects['parameters'] : array();

        $parameters = array_filter($parameters, function ($parameter) {
            return 'path' == $parameter['in'] && isset($parameter['required']) && true == $parameter['required'];
        });

        if (count($parameters) > 0) {
            $conf .= "\n    requirements:";
            foreach ($parameters as $parameter) {
                $regex = '.+';
                if ($parameter['type'] == 'integer') {
                    $regex = '[0-9]+';
                } elseif ($parameter['type'] == 'number') {
                    $regex = "[0-9]+\.?[0-9]*";
                } elseif ($parameter['type'] == 'string') {
                    if (isset($parameter['format'])) {
                        if ($parameter['format'] == 'date') {
                            $regex = "[0-9]{4}\-[0-9]{2}\-[0-9]{2}";
                        }
                    }
                }
                $conf .= "\n         ".$parameter['name'].':'.' "'.$regex.'" ';
            }
        }

        $conf .= "\n\n";

        return $conf;
    }

    /**
     * @param $content
     * @param $target
     * @param $bundleName
     * @param $prefix
     *
     * @return int
     */
    public function generate($content, $target, $bundleName, $prefix)
    {
        $this->createFile($content, $target);
        try {
            $this->addToApp($bundleName, $prefix);

            return 1; // add to app
        } catch (\RuntimeException $e) {
            return 2; // already in app
        }
    }

    /**
     * @param $bundleName
     * @param $prefix
     */
    private function addToApp($bundleName, $prefix)
    {
        if ($prefix == '') {
            $prefix = '/';
        }
        $kernelManipulator = new RoutingManipulator($this->container->getParameter('kernel.root_dir').'/config/routing.yml');
        $kernelManipulator->addResource($bundleName, 'yml', $prefix);
    }

    public function getRouteParametersAsArray($verb, $pathName)
    {
        $parameters = array();
        $parameters['name'] = preg_replace('/[^a-zA-Z0-9]+/', '_', $pathName);
        $parameters['name'] = trim($parameters['name'], '_');
        $parameters['name'] .= '_'.$verb;

        return $parameters;
    }
}
