<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 03/07/15
 * Time: 11:03
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Tests\Entity;

include_once __DIR__.'/Mock/Pet.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Enneite\SwaggerBundle\Entity\Collection;
use Enneite\SwaggerBundle\Tests\Entity\Mock\Pet;

class CollectionTest extends WebTestCase
{
    public function testCollection()
    {
        $collection = new Collection();

        $this->assertEquals(0, $collection->count());
        $this->assertEquals(array(), $collection->toArray());

        $a = new Pet();
        $array = array(
            'key' => 'value',
            'key2' => 'value2',
        );
        $object = new \stdClass();
        $object->test = 2;

        $collection->push($a);
        $collection->push($array);
        $collection->push($object);

        $this->assertEquals(3, $collection->count());
        $this->assertEquals(array(
            array(
                'id' => 123,
                'name' => 'myName',
            ), array(
                'key' => 'value',
                'key2' => 'value2',
            ), array(
                'test' => '2',
            )
        ), $collection->toArray());

        $collection->setItems($array);
        $this->assertEquals($array, $collection->getItems());

        $this->assertInstanceOf('\ArrayIterator', $collection->getIterator());
    }
}
