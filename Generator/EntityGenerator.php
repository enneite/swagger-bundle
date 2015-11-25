<?php

/**
 * Created by JetBrains PhpStorm.
 * User: bernard.caron
 * Date: 11/11/15
 * Time: 15:23
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Generator;

class EntityGenerator extends Generator
{
    private $className;
    private $namespace;

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param $path
     * @param $namespace
     * @param $entityName
     * @param $entity
     *
     * @return string
     */
    public function generate($path, $namespace, $entityName, $entity)
    {
        // explode entity name for the namespace construction
        // for example :
        //entity name = "Offer/Program"
        $names = explode('/', $entityName);

        // get file / class name
        $this->className = ucfirst(array_pop($names));

        $path .= 'Entity/';
        $namespace .= '\\Entity';
        if (count($names) > 0) {
            foreach ($names as $d) {
                $path .= ucfirst($d).'/';
                $namespace = $namespace.'\\'.ucfirst($d);
            }
        }

        $this->namespace = $namespace;

        $template = 'Entity.php.twig';
        $target = $path.$this->className.'.php';
        $params = $this->getEntityParams($this->className, $this->namespace, $entity);

        return $this->renderFile($template, $target, $params);
    }

    /**
     * @param $className
     * @param $namespace
     * @param $entity
     *
     * @return array
     */
    private function getEntityParams($className, $namespace, $entity)
    {
        $useArray = array(
            'Enneite\SwaggerBundle\Entity\EntityInterface',
            'Enneite\SwaggerBundle\Entity\Collection',
        );

        $propertiesConf = array();

        $mapping = array();

        $hasParent = false;
        if (array_key_exists('allOf', $entity)) {
            // legacy (extending an other entity) :
            $hasParent = true;
            // find parent class :
            $parentContainer = array_values(array_filter($entity['allOf'], function ($obj) {
                return array_key_exists('$ref', $obj);
            }));

            $parentVarType = $this->getVartype($parentContainer[0]);
            $className = $className.' extends '.$parentVarType;
            $parentVarType = '\\Entity\\'.$parentVarType;
            array_push($useArray, $parentVarType);

            // new properties :
            $propertiesContainer = array_values(array_filter($entity['allOf'], function ($obj) {
                return array_key_exists('properties', $obj);
            }));
            $entityProperties = $propertiesContainer[0]['properties'];
        } else { // no legacy found:
            $entityProperties = $entity['properties'];
        }

        // create property, getter and setter for each property defined for this entity

        foreach ($entityProperties as $property => $value) {
            $originalProperty = $property;
            $property = $this->formatProperty($property);
            $mapping[$property] = $originalProperty;

            $varType = $this->getVartype($value);

            // update use: and constructor :
            if ($this->isEntity($value)) {
                $varType = '\\Entity\\'.$varType;
                array_push($useArray, $varType);
                $varTypeArray = explode('\\', $varType);
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
                'toArray' => ($this->isEntity($value) || $this->isTypeArray($value)),
            );
        }

        return array(
            'namespace' => $namespace,
            'use' => array_unique($useArray),
            'className' => $className,
            'propertiesConf' => $propertiesConf,
            'mapping' => $mapping,
            'hasParent' => $hasParent,
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
            $varType = $this->extractEntity($value);
        }

        return $varType;
    }

    public function extractEntity($value)
    {
        $entity = str_replace(array('#/definitions/', '~1'), array('', '\\'), $value['$ref']);
        $entity = str_replace(' ', '\\', ucwords(str_replace('\\', ' ', $entity)));

        return $entity;
    }

    /**
     * return true if the property is a entity pointer.
     *
     * @param $value
     *
     * @return bool
     */
    public function isEntity($value)
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
        $str = str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $property)));
        $str[0] = strtolower($str[0]);
        $str = preg_replace('/[^a-zA-Z0-9]+/', '', $str);

        return $str;
    }
}
