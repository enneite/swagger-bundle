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

class Article implements EntityInterface
{
    protected $id = 123;
    protected $name = 'myName';
    protected $date = '2015-12-12 12:00:00';
    protected $params = array(
        'key' => 'value',
    );
    protected $pet;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->date = new \DateTime('2015-12-12 12:00:00');
        $this->pet = new Pet();
    }

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

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param Pet $pet
     */
    public function setPet(Pet $pet)
    {
        $this->pet = $pet;
    }

    /**
     * @return string
     */
    public function getPet()
    {
        return $this->pet;
    }

    /**
     * @param String $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'date' => $this->getDate(),
            'pet' => $this->getPet()->toArray(),
        );
    }
}
