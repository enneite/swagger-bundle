<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 03/07/15
 * Time: 11:03
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Tests\Model;

include_once __DIR__ . '/Mock/Pet.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Enneite\SwaggerBundle\Model\Collection;
use Enneite\SwaggerBundle\Tests\Model\Mock\Pet;

class CollectionTest extends WebTestCase
{
    public function testCollection()
    {
        $collection = new Collection();

        $this->assertEquals(0, $collection->count());
        $this->assertEquals(array(), $collection->toArray());

        $a = new Pet();

        $collection->push($a);
        $this->assertEquals(1, $collection->count());
        $this->assertEquals(array(
            array(
                'id' => 123,
                'name' => 'myName',
            ),
        ), $collection->toArray());

        $this->assertInstanceOf('\ArrayIterator', $collection->getIterator());
    }
}
