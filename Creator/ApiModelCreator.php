<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 23/06/15
 * Time: 11:42
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Creator;

class ApiModelCreator
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

    /**
     * generate PHP code for a model who is define in API definitions.
     *
     * @param $className
     * @param $namespace
     * @param $model
     *
     * @return string
     */
    public function createModel($className, $namespace, $modelsNamespace, $model)
    {
        $template = $this->getTwig()->loadTemplate('Model.php.twig');

        return $template->render($this->getModel($className, $namespace, $modelsNamespace, $model));
    }

    /**
     * @param $className
     * @param $namespace
     * @param $modelsNamespace
     * @param $model
     *
     * @return array
     */
    public function getModel($className, $namespace, $modelsNamespace, $model)
    {
        $useArray = array(
            'Enneite\SwaggerBundle\Model\ModelInterface',
            'Enneite\SwaggerBundle\Model\Collection',
        );

        $propertiesConf = array();

        $mapping = array();

        $hasParent = false;
        if(array_key_exists('allOf', $model)) {// legacy (extending an other model) :
            $hasParent = true;
            // find parent class :
            $parentContainer = array_values(array_filter($model['allOf'], function($obj) {
                return array_key_exists('$ref', $obj);
            }));
            var_dump($parentContainer[0]['$ref']);
            $parentVarType = $this->getVartype($parentContainer[0]);
            $className = $className . ' extends '.$parentVarType;
            $parentVarType = '\\' . $modelsNamespace . '\\' . $parentVarType;
            array_push($useArray, $parentVarType);

            // new properties :
            $propertiesContainer = array_values(array_filter($model['allOf'], function($obj) {
                return array_key_exists('properties', $obj);
            }));
            $modelProperties = $propertiesContainer[0]['properties'];
        }
        else { // no legacy found:
            $modelProperties = $model['properties'];
        }

        // create property, getter and setter for each property defined for this model

        foreach ($modelProperties as $property => $value) {
            $originalProperty   = $property;
            $property           = $this->formatProperty($property);
            $mapping[$property] = $originalProperty;

            $varType = $this->getVartype($value);

            // update use: and constructor :
            if ($this->isModel($value)) {
                $varType = '\\' . $modelsNamespace . '\\' . $varType;
                array_push($useArray, $varType);
                $varTypeArray      = explode('\\', $varType);
                $propertyClassName = array_pop($varTypeArray);
            } elseif ($this->isTypeArray($value)) {
                $propertyClassName = 'Collection';
            } else {
                $propertyClassName = null;
            }

            // build properties;
            $propertiesConf[$property] = array(
                'description' => isset($value['description']) ? $value['description'] : 'empty',
                'varType' => $varType,
                'class' => $propertyClassName,
                'toArray' => ($this->isModel($value) || $this->isTypeArray($value)),
            );
        }

        return array(
            'namespace' => $namespace,
            'use' => array_unique($useArray),
            'className' => $className,
            'propertiesConf' => $propertiesConf,
            'mapping' => $mapping,
            'hasParent' => $hasParent
        );
    }




    /**
     * find var type.
     *
     * @param $value
     *
     * @return mixed|string
     */
    public function getVarType($value)
    {
        $varType = 'mixed';
        if (isset($value['type'])) {
            $varType = $value['type'];
        }
        if (isset($value['$ref'])) {
            $varType = $this->extractModel($value);
        }

        return $varType;
    }

    public function extractModel($value)
    {
        $model = str_replace(array('#/definitions/', '~1'), array('', '\\'), $value['$ref']);
        $model = str_replace(' ', '\\', ucwords(str_replace('\\', ' ', $model)));

        return $model;
    }

    /**
     * return true if the property is a model pointer.
     *
     * @param $value
     *
     * @return bool
     */
    public function isModel($value)
    {
        return isset($value['$ref']);
    }

    /**
     * return true if the swagger property type is "array".
     *
     * @param $value
     *
     * @return bool
     */
    public function isTypeArray($value)
    {
        return isset($value['type']) && 'array' == $value['type'];
    }

    /**
     * format property to respect camelCase.
     *
     * @param $property
     *
     * @return mixed
     */
    public function formatProperty($property)
    {
        $str    = str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $property)));
        $str[0] = strtolower($str[0]);
        $str    = preg_replace('/[^a-zA-Z0-9]+/', '', $str);

        return $str;
    }    
}
