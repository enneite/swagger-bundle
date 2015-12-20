<?php

/**
 * Created by JetBrains PhpStorm.
 * User: bernard.caron
 * Date: 20/12/15
 * Time: 18:30
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Tests\Mapping;

include_once __DIR__.'/Mock/Pet.php';

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Enneite\SwaggerBundle\Entity\Mapping;
use Enneite\SwaggerBundle\Tests\Entity\Mock\Pet;
use Enneite\SwaggerBundle\Tests\Entity\Mock\Article;

class MappingTest extends WebTestCase
{
    protected $article;

    protected function setUp()
    {
        $article = new Article();
        $article->setId(42);
        $article->setName('testName');
        $article->setDate(date('2015-12-24 12:00:00'));
        $article->setParams(array(
            'keyTest' => 'valueTest',
        ));
        $pet = new Pet();
        $pet->setId(12);
        $pet->setName('petName');
        $article->setPet($pet);
        $this->article = $article;
    }

    public function testBuildFromArray()
    {
        $array = array(
            'id' => 42,
            'name' => 'testName',
            'date' => date('2015-12-24 12:00:00'),
            'pet' => array(
                'id' => 12,
                'name' => 'petName',
            ), 'params' => array(
                'keyTest' => 'valueTest',
            ),
        );

        $this->assertEquals($this->article, Mapping::buildFromArray(new Article(), $array));
    }

    public function testBuildFromObject()
    {
        $object = new \stdClass();
        $object->id = 42;
        $object->name = 'testName';
        $object->date = new \DateTime('2015-12-24 12:00:00');
        $object->params = array('keyTest' => 'valueTest');
        $objectPet = new \stdClass();
        $objectPet->id = 12;
        $objectPet->name = 'petName';
        $object->pet = $objectPet;

        $this->assertEquals($this->article, Mapping::buildFromObject(new Article(), $object, 'Y-m-d h:i:s'));
    }
}
