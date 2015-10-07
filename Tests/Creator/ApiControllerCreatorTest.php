<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 03/07/15
 * Time: 09:46
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Tests\Creator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Enneite\SwaggerBundle\Creator\ApiControllerCreator;

class ApiControllerCreatorTest extends WebTestCase
{
    public function setup()
    {
        //$this->config = json_decode(file_get_contents(realpath( __DIR__ .'/../../Resources/example/config/swagger.json')), true);
    }

    public function testConstructAndGetters()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $this->assertInstanceOf('\Twig_Environment', $creator->getTwig(), 'twigEnv property must be an instance of \Twig_Environment');
        $this->assertInstanceOf('Enneite\SwaggerBundle\Creator\ApiModelCreator', \PHPUnit_Framework_Assert::readAttribute($creator, 'apiModelCreator'));
        $this->assertInstanceOf('Enneite\SwaggerBundle\Creator\ApiRoutingCreator', \PHPUnit_Framework_Assert::readAttribute($creator, 'apiRoutingCreator'));
    }

    public function testGetAvailableExceptions()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $exceptions = $creator->getAvailableExceptions();
        $this->assertInternalType('array', $exceptions);

        foreach (array(401, 403, 404) as $status) {
            $this->assertArrayHasKey($status, $exceptions);
        }
    }

    public function testHasResponseSchema()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $response = array(
            'description' => 'successful operation',
        );

        $this->assertFalse($creator->hasResponseSchema($response));

        $response = array(
                'description' => 'successful operation',
                'schema' => array(
                    'type' => 'array',
                ),
                'items' => array(
                    "\$ref" => '#/definitions/Pet',
                ),
            );

        $this->assertTrue($creator->hasResponseSchema($response));
    }

    public function testHasResponseModel()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $response = array(
            'description' => 'successful operation',
        );

        $this->assertFalse($creator->hasResponseModel($response));

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
            ),
            'items' => array(
                "\$ref" => '#/definitions/Pet',
            ),
        );

        $this->assertFalse($creator->hasResponseModel($response));

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                "\$ref" => '#/definitions/Pet',
            ),
        );

        $this->assertTrue($creator->hasResponseModel($response));
    }

    public function testHasResponseCollection()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $response = array(
            'description' => 'successful operation',
        );

        $this->assertFalse($creator->hasResponseCollection($response));

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
                'items' => array(
                    "\$ref" => '#/definitions/Pet',
                ),
            ),
        );

        $this->assertTrue($creator->hasResponseCollection($response));

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                "\$ref" => '#/definitions/Pet',
            ),
        );

        $this->assertfalse($creator->hasResponseCollection($response));
    }

    /**
     * @expectedException \Exception
     */
    public function testHasResponseCollectionWithException()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
            ),

        );
        $res = $creator->hasResponseCollection($response);
    }

    public function testGetAssociatedModel()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiModelCreator->expects($this->any())
            ->method('extractModel')
            ->will($this->returnValue('Pet'));
        $apiRoutingCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                "\$ref" => '#/definitions/Pet',
            ),
        );
        $res = $creator->getAssociatedModel($response);
        $this->assertEquals('Pet', $res);

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
                'items' => array(
                    "\$ref" => '#/definitions/Pet',
                ),
            ),
        );

        $res = $creator->getAssociatedModel($response);
        $this->assertEquals(false, $res);
    }

    public function testGetAssociatedCollection()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiModelCreator->expects($this->any())
            ->method('extractModel')
            ->will($this->returnValue('Pet'));
        $apiRoutingCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                "\$ref" => '#/definitions/Pet',
            ),
        );
        $res = $creator->getAssociatedCollection($response);
        $this->assertEquals(false, $res);

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
                'items' => array(
                    "\$ref" => '#/definitions/Pet',
                ),
            ),
        );

        $res = $creator->getAssociatedCollection($response);
        $this->assertEquals('PetCollection', $res);
    }

    public function testExtractCodesbyStatus()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $this->assertEquals(array(200), $creator->extractCodesByStatus(array(200, 401, 403, 500), 2));
        $this->assertEquals(array(), $creator->extractCodesByStatus(array(200, 401, 403, 500), 3));
        $this->assertEquals(array(401, 403), $creator->extractCodesByStatus(array(200, 401, 403, 500), 4));
        $this->assertEquals(array(500), $creator->extractCodesByStatus(array(200, 401, 403, 500), 5));
    }

    public function testGetActionParameters()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiModelCreator->expects($this->any())
            ->method('formatProperty')
            ->will($this->returnValue('myVariable'));
        $apiRoutingCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $objects = array(
            'parameters' => array(

                array(
                    'description' => 'titi tata',
                    'name' => 'code',
                    'in' => 'path',
                    'type' => 'integer',
                    'required' => true,
                ),
                array(
                    'description' => 'user language',
                    'name' => 'Accept-language',
                    'in' => 'header',
                    'type' => 'string',
                ),
            ),
        );

        $this->assertEquals(array(

            array(
                'description' => null,
                'name' => 'myVariable',
                'in' => 'path',
                'type' => 'integer',
                'required' => true,
            ),
            array(
                'description' => 'user language',
                'name' => 'myVariable',
                'in' => 'header',
                'type' => 'string',
                'required' => false,
            ),
        ), $creator->getActionParameters('get', $objects));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetActionParametersWithException()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $objects = array(
            'parameters' => array(
                array(
                    'description' => 'titi tata',
                    'name' => 'code',
                    // 'in' => 'path', // parameter 'in' is missing => will throw an exception
                    'type' => 'integer',
                    'required' => true,
                ),
            ),
        );

        $res = $creator->getActionParameters('get', $objects);
    }

    public function testCreateActionParameters()
    {
        $php      = '(Request $request, $id)';
        $template = $this->getMockBuilder('\Twig_Template')->disableOriginalConstructor()->getMock();
        $template->expects($this->any())
            ->method('render')
            ->will($this->returnvalue($php));
        $twig = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $twig->expects($this->any())
            ->method('loadTemplate')
            ->will($this->returnvalue($template));
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $objects = array(
            'parameters' => array(
                array(
                    'name' => 'id',
                    'in' => 'path',
                    'type' => 'integer',
                    'required' => true,
                ),
            ),
        );

        $this->assertEquals($php, $creator->createActionParameters('get', $objects));
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateActionParametersWithException()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $objects = array(
            'parameters' => array(
                array(
                    'name' => 'id',
                    // 'in' => 'path', // parameter 'in' is missing => will throw an exception
                    'type' => 'integer',
                    'required' => true,
                ),
            ),
        );

        $res = $creator->createActionParameters(200, $objects);
    }

    /**
     *
     */
    public function testGetRoutingAnnotation()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator->expects($this->any())
            ->method('getRouteParametersAsArray')
            ->will($this->returnvalue(array()));

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $objects = array(
            'parameters' => array(
                array(
                    'name' => 'id',
                    // 'in' => 'path', // parameter 'in' is missing => will throw an exception
                    'type' => 'integer',
                    'required' => true,
                ),
            ),
        );

        $this->assertEquals(array(
            'route' => '/pets',
            'parameters' => array(),
            'method' => 'GET',
        ), $creator->getRoutingAnnotation('get',  $objects, '/pets'));
    }

    /**
     *
     */
    public function testGetActionComments()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiModelCreator->expects($this->any())
            ->method('formatProperty')
            ->will($this->returnvalue('id'));
        $apiRoutingCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator->expects($this->any())
            ->method('getRouteParametersAsArray')
            ->will($this->returnvalue(array()));

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        // case 1 : without routing annotation generation and with description set
        $objects = array(
            'description' => 'this is a good description',
            'parameters' => array(
                array(
                    'name' => 'id',
                    'in' => 'path', // parameter 'in' is missing => will throw an exception
                    'type' => 'integer',
                    'required' => true,
                ),
            ),
        );
        $this->assertEquals(array(
            'description' => 'this is a good description',
            'routing' => null,
            'params' => array('id'),
        ), $creator->getActionComments('get', $objects, '/pets', false));

        // case 2 : with routing annotation generation and no description set
        $objects = array(
            'parameters' => array(
                array(
                    'name' => 'id',
                    'in' => 'path', // parameter 'in' is missing => will throw an exception
                    'type' => 'integer',
                    'required' => true,
                ),
            ),
        );
        $this->assertEquals(array(
            'description' => 'empty',
            'routing' => array(
                'route' => '/pets',
                'parameters' => array(),
                'method' => 'GET',
            ),
            'params' => array('id'),
        ), $creator->getActionComments('get', $objects, '/pets', true));
    }

    public function testExtractArguments()
    {
        include_once __DIR__ . '/../Controller/Mock/ProductController.php';
        $reflexion = new \ReflectionClass('Enneite\SwaggerBundle\Tests\Controller\Mock\ProductController');
        $method    = $reflexion->getMethod('getAction');

        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $this->assertEquals(array('id'), $creator->extractArguments($method));
    }

    public function testGetSchemaResponse()
    {
        $php      = '(Request $request, $id)';
        $template = $this->getMockBuilder('\Twig_Template')->disableOriginalConstructor()->getMock();
        $template->expects($this->any())
            ->method('render')
            ->will($this->returnvalue($php));
        $twig = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $twig->expects($this->any())
            ->method('loadTemplate')
            ->will($this->returnvalue($template));
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiModelCreator->expects($this->any())
            ->method('extractModel')
            ->will($this->returnvalue('Product'));
        $apiRoutingCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        // tests the template twig arguments generation :
        $this->assertEquals(array('model' => null, 'method' => null), $creator->getSchemaPhpCode(array()));

        $this->assertEquals(array('model' => null, 'method' => null), $creator->getSchemaPhpCode(array('schema' => array('type' => 'string'))));

        $this->assertEquals(array('model' => 'Product', 'method' => 'buildResource'), $creator->getSchemaPhpCode(array(
            'schema' => array(
                '$ref' => '#/definitions/Product',
            ),
        )));

        $this->assertEquals(array('model' => 'Product', 'method' => 'buildCollection'), $creator->getSchemaPhpCode(array(
            'schema' => array(
                'type' => 'array',
                'items' => array(
                    '$ref' => '#/definitions/Product',
                ),
            ),
        )));

        // test the php generation code :
        $this->assertEquals($php, $creator->createSchemaPhpCode(array()));
    }

    public function testCreateClassName()
    {
        $twig               = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();

        $creator = new ApiControllerCreator($twig, $apiModelCreator, $apiRoutingCreator);

        $this->assertEquals('PetsController', $creator->getClassName('/pets'));
        $this->assertEquals('PetsIdController', $creator->getClassName('/pets/{id}'));
        $this->assertEquals('PetsIdController', $creator->getClassName('/pets/*/{id}$$$'));
    }
}
