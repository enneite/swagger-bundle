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
use Enneite\SwaggerBundle\Creator\ApiModelCreator;

class ApiModelCreatorTest extends WebTestCase
{
    public function testGetters()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiModelCreator($twig);
        $this->assertInstanceOf('\Twig_Environment', $creator->getTwig(), 'twig property must be an instance of \Twig_Environment');
    }

    public function testIsModel()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiModelCreator($twig);
        $this->assertTrue($creator->isModel(array('$ref' => '#/definitions/product~1theme')));
        $this->assertFalse($creator->isModel(array('type' => 'string')));
    }

    public function testIsArray()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiModelCreator($twig);
        $this->assertFalse($creator->isTypeArray(array('$ref' => '#/definitions/product~1theme')));
        $this->assertFalse($creator->isTypeArray(array('type' => 'string')));
        $this->assertTrue($creator->isTypeArray(array('type' => 'array')));
    }

    public function testExtractModel()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiModelCreator($twig);
        $this->assertEquals('Product\Theme', $creator->extractModel(array('$ref' => '#/definitions/product~1theme')));
    }

    public function testGetVarType()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiModelCreator($twig);
        $this->assertEquals('mixed', $creator->getVarType(array()));
        $this->assertEquals('string', $creator->getVarType(array('type' => 'string')));
        $this->assertEquals('Product\Theme', $creator->getVarType(array('$ref' => '#/definitions/product~1theme')));
    }

    public function testFormatproperty()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiModelCreator($twig);
        $this->assertEquals('product', $creator->formatProperty('product'));
        $this->assertEquals('productTheme', $creator->formatProperty('product-theme'));
        $this->assertEquals('productTheme', $creator->formatProperty('product_theme'));
        $this->assertEquals('producttheme', $creator->formatProperty('product*$$$*theme###'));
    }


    public function testCreateModel()
    {
        $php      = '<?php /*php code */?>';
        $template = $this->getMockBuilder('\Twig_Template')->disableOriginalConstructor()->getMock();
        $template->expects($this->any())
            ->method('render')
            ->will($this->returnvalue($php));
        $twig = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $twig->expects($this->any())
            ->method('loadTemplate')
            ->will($this->returnvalue($template));
        $creator = new ApiModelCreator($twig);

        $model = array(
            'properties' => array(
                'id' => array(
                    'type' => 'integer',
                    'format' => 'int64',
                ),
                'category' => array(
                    "\$ref" => '#/definitions/Category',
                ),
                'name' => array(
                    'type' => 'string',
                    'example' => 'doggie',
                ),
                'photoUrls' => array(
                    'type' => 'array',
                    'item' => array(
                        'type' => 'string',
                    ),
                ),
                'tags' => array(
                    'type' => 'array',
                    'item' => array(
                        "\$ref" => '#/definitions/Tag',
                    ),
                ),
                'status' => array(
                    'type' => 'string',
                    'description' => 'pet status in the store',
                ),
            ),
        );

        $this->assertEquals($php, $creator->createModel('Product', 'Enneite\Swagger', 'Enneite\Swagger\Model', $model));
    }
}
