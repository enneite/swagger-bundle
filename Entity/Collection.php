<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 24/06/15
 * Time: 15:08
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Entity;

class Collection implements EntityInterface, \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    protected $items = array();

    /**
     * @param $item
     */
    public function push($item)
    {
        $this->items[] = $item;
    }

    /**
     * return items in an array (array can be json encoded).
     *
     * @return array
     */
    public function toArray()
    {
        $a = array();
        foreach ($this->items as $item) {
            if ($item instanceof EntityInterface) {
                array_push($a, $item->toArray());
            } elseif (is_object($item)) {
                array_push($a, (array) $item);
            } else {
                array_push($a, $item);
            }
        }

        return $a;
    }

    /**
     * items getter.
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * items setter.
     *
     * @param array $items
     *
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}
