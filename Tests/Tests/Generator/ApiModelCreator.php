<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 02/07/15
 * Time: 10:56
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Tests\Generator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiModelCreator extends WebTestCase
{
    public $generator;

    public function setUp()
    {
        //        self::bootKernel();
//        $this->generator = static::$kernel->getContainer()->get('enneite_swagger.entity_generator');
    }

//    public function testIsEntity()
//    {
//        $this->assertTrue($this->generator->isEntity(array('$ref' => '#/definitions/product~1theme')));
//        $this->assertFalse($this->generator->isEntity(array('type' => 'string')));
//    }
//
//    public function testIsArray()
//    {
//        $this->assertFalse($this->generator->isTypeArray(array('$ref' => '#/definitions/product~1theme')));
//        $this->assertFalse($this->generator->isTypeArray(array('type' => 'string')));
//        $this->assertTrue($this->generator->isTypeArray(array('type' => 'array')));
//    }
//
//    public function testExtractModel()
//    {
//        $this->assertEquals('Product\Theme', $this->generator->extractEntity(array('$ref' => '#/definitions/product~1theme')));
//    }
//
//    public function testGetVarType()
//    {
//        $this->assertEquals('mixed', $this->generator->getVarType(array()));
//        $this->assertEquals('string', $this->generator->getVarType(array('type' => 'string')));
//        $this->assertEquals('Product\Theme', $this->generator->getVarType(array('$ref' => '#/definitions/product~1theme')));
//    }
//
//    public function testFormatproperty()
//    {
//        $this->assertEquals('product', $this->generator->formatProperty('product'));
//        $this->assertEquals('productTheme', $this->generator->formatProperty('product-theme'));
//        $this->assertEquals('productTheme', $this->generator->formatProperty('product_theme'));
//        $this->assertEquals('producttheme', $this->generator->formatProperty('product*$$$*theme###'));
//    }

//    public function testCreateModel()
//    {
//        $php = '<?php /*php code */;
//        $template = $this->getMockBuilder('\Twig_Template')->disableOriginalConstructor()->getMock();
//        $template->expects($this->any())
//            ->method('render')
//            ->will($this->returnvalue($php));
//        $twig = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
//        $twig->expects($this->any())
//            ->method('loadTemplate')
//            ->will($this->returnvalue($template));
//        $this->generator = new ApiModelCreator($twig);
//
//        $model = array(
//            'properties' => array(
//                'id' => array(
//                    'type' => 'integer',
//                    'format' => 'int64',
//                ),
//                'category' => array(
//                    '$ref' => '#/definitions/Category',
//                ),
//                'name' => array(
//                    'type' => 'string',
//                    'example' => 'doggie',
//                ),
//                'photoUrls' => array(
//                    'type' => 'array',
//                    'item' => array(
//                        'type' => 'string',
//                    ),
//                ),
//                'tags' => array(
//                    'type' => 'array',
//                    'item' => array(
//                        '$ref' => '#/definitions/Tag',
//                    ),
//                ),
//                'status' => array(
//                    'type' => 'string',
//                    'description' => 'pet status in the store',
//                ),
//            ),
//        );
//
//        $this->assertEquals($php, $this->generator->createModel('Product', 'Enneite\Swagger', 'Enneite\Swagger\Model', $model));
//    }
}
