<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 03/07/15
 * Time: 11:07
 * To change this template use File | Settings | File Templates.
 */
namespace  Enneite\SwaggerBundle\Tests\Tests\Entity\Mock;

use Enneite\SwaggerBundle\Entity\EntityInterface;

class Pet implements EntityInterface
{
    protected $id = 123;
    protected $name = 'myName';

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
        );
    }
}
