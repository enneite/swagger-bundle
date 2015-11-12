<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 02/07/15
 * Time: 10:56
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Tests\Creator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Parser;
use Enneite\SwaggerBundle\Creator\ApiRoutingCreator;

class ApiRoutingCreatorTest extends WebTestCase
{
    public function testGetters()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiRoutingCreator($twig);
        $this->assertInstanceOf('\Twig_Environment', $creator->getTwig(), 'twig property must be an instance of \Twig_Environment');
    }

    public function testGetRouteParametersAsArray()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiRoutingCreator($twig);

        $this->assertEquals(array('name' => 'product_get'), $creator->getRouteParametersAsArray('get', array(), '/product'));
        $this->assertEquals(array('name' => 'product_id_get'), $creator->getRouteParametersAsArray('get', array(), '/product/{id}_'));
    }

    public function testCreateYamlConf()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiRoutingCreator($twig);

        $objects = array(
            'description' => 'this is my description!',
            'parameters' => array(
                array(
                    'name' => 'date',
                    'in' => 'path',
                    'type' => 'string',
                    'format' => 'date',
                    'required' => true,
                ),
                array(
                    'name' => 'time',
                    'in' => 'path',
                    'type' => 'string',
                    'format' => 'datetime',
                    'required' => true,
                ),
                array(
                    'name' => 'status',
                    'in' => 'path',
                    'type' => 'string',
                    'required' => true,
                ),
                array(
                    'name' => 'id',
                    'in' => 'path',
                    'type' => 'integer',
                    'format' => 'int32',
                    'required' => true,
                ),
                array(
                    'name' => 'code',
                    'in' => 'path',
                    'type' => 'number',
                    'format' => 'float',
                    'required' => true,
                ),
            ),
        );

        $conf = $creator->createYamlConf('get', $objects, '/product', 'EnneiteSwaggerBundle', 'Product');
        $yaml = new Parser();
        $this->assertInternalType('array', $yaml->parse($conf));
    }

    public function testGetPathParameterRegex()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiRoutingCreator($twig);

        $parameter = array(
            'name' => 'date',
            'in' => 'path',
            'type' => 'string',
            'format' => 'date',
            'required' => true,
        );
        $this->assertEquals("[0-9]{4}\-[0-9]{2}\-[0-9]{2}", $creator->getPathParameterRegex($parameter));

        $parameter = array(
            'name' => 'time',
            'in' => 'path',
            'type' => 'string',
            'format' => 'datetime',
            'required' => true,
        );
        $this->assertEquals(".+", $creator->getPathParameterRegex($parameter));

        $parameter = array(
            'name' => 'status',
            'in' => 'path',
            'type' => 'string',
            'required' => true,
        );
        $this->assertEquals(".+", $creator->getPathParameterRegex($parameter));

        $parameter = array(
            'name' => 'id',
            'in' => 'path',
            'type' => 'integer',
            'format' => 'int32',
            'required' => true,
        );
        $this->assertEquals("[0-9]+", $creator->getPathParameterRegex($parameter));

        $parameter = array(
            'name' => 'code',
            'in' => 'path',
            'type' => 'number',
            'format' => 'float',
            'required' => true,
        );
        $this->assertEquals("[0-9]+\.?[0-9]*", $creator->getPathParameterRegex($parameter));
    }

    public function testGetPathParameterAssoc()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiRoutingCreator($twig);
        $parameters = array(
            array(
                'name' => 'date',
                'in' => 'path',
                'type' => 'string',
                'format' => 'date',
                'required' => true,
            ),
            array(
                'name' => 'time',
                'in' => 'path',
                'type' => 'string',
                'format' => 'datetime',
                'required' => true,
            ),
            array(
                'name' => 'status',
                'in' => 'path',
                'type' => 'string',
                'required' => true,
            ),
            array(
                'name' => 'id',
                'in' => 'path',
                'type' => 'integer',
                'format' => 'int32',
                'required' => true,
            ),
            array(
                'name' => 'code',
                'in' => 'path',
                'type' => 'number',
                'format' => 'float',
                'required' => true,
            ),
        );

        $a = array(
            'date' => '[0-9]{4}\-[0-9]{2}\-[0-9]{2}',
            'time' => '.+',
            'status' => '.+',
            'id' =>'[0-9]+',
            'code' => '[0-9]+\.?[0-9]*'
        );

        $this->assertEquals($a, $creator->getPathParameterAssoc($parameters));
    }
}
