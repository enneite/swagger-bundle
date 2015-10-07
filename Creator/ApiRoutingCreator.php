<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 24/06/15
 * Time: 16:34
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Creator;

class ApiRoutingCreator
{

    /**
     * @var
     */
    protected $twig;

    public function __construct($twig)
    {
        $this->setTwig($twig);
    }

    /**
     * @param $twig
     *
     * @return $this
     */
    public function setTwig($twig)
    {
        $this->twig = $twig;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTwig()
    {
        return $this->twig;
    }

    public function getRouteParametersAsArray($verb, $objects, $pathName)
    {
        $parameters = array();

        $parameters['name'] = preg_replace('/[^a-zA-Z0-9]+/', '_', $pathName);
        $parameters['name'] = trim($parameters['name'], '_');
        $parameters['name'] .= '_' . $verb;

        return $parameters;
    }

    public function createYamlConf($verb, $objects, $pathName, $bundleName, $className)
    {
        $parameters = $this->getRouteParametersAsArray($verb, $objects, $pathName);

        $conf = '';
        if (isset($objects['description'])) {
            $conf .= "\n# description: " . str_replace("\n", "\n#", $objects['description']);
        }
        $conf .= "\n" . $parameters['name'] . ':';
        $conf .= "\n    pattern: " . $pathName;
        $conf .= "\n    methods: [" . strtoupper($verb) . ']';
        $conf .= "\n    defaults:";
        $conf .= "\n        _controller: " . $bundleName . ':Api\\' . $className . ':' . $verb;

        $parameters = (isset($objects['parameters'])) ? $objects['parameters'] : array();

        $parameters = array_filter($parameters, function ($parameter) {
            return  'path' == $parameter['in'] && isset($parameter['required']) && true == $parameter['required'];
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
                $conf .= "\n         " . $parameter['name'] . ':' . ' "' . $regex . '" ';
            }
        }

        $conf .= "\n\n";

        return $conf;
    }
}
