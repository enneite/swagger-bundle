<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 03/07/15
 * Time: 09:46
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Tests\Tests\Generator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ControllerGeneratorTest extends WebTestCase
{
    protected $controllerGenerator;

    protected function setUp()
    {
        static::bootKernel();
        $this->controllerGenerator = static::$kernel->getContainer()->get('enneite_swagger.controller_generator');
    }

    public function testGenerate()
    {
        $path = array(
            'get' => array(
                'description' => 'test',
                'consumes' => array(
                    0 => 'application/json',
                ),
                'parameters' => array(
                    0 => array(
                        'name' => 'test',
                        'in' => 'header',
                        'description' => 'test parameters',
                        'required' => false,
                        'type' => 'string',
                    ),
                    1 => array(
                        'name' => 'test2',
                        'in' => 'path',
                        'description' => 'test2 parameters',
                        'required' => true,
                        'type' => 'int',
                    ),
                ),
                'responses' => array(
                    200 => array(
                        'description' => 'ok',
                        'schema' => array(
                            '$ref' => '#/definitions/Pet',
                        ),
                    ),
                    401 => array(
                        'description' => 'erreur 401',
                    ),
                    500 => array(
                        'description' => 'erreur 500',
                    ),
                ),
            ),
        );
        $this->controllerGenerator->generate(__DIR__.'/../../src/', 'testNamespace', 'testClassName', '/test', $path, false);

        $path = array(
            'get' => array(
                'description' => 'test',
                'consumes' => array(
                    0 => 'application/json',
                ),
                'parameters' => array(
                    0 => array(
                        'name' => 'test',
                        'in' => 'header',
                        'description' => 'test parameters',
                        'required' => false,
                        'type' => 'string',
                    ),
                    1 => array(
                        'name' => 'test2',
                        'in' => 'path',
                        'description' => 'test2 parameters',
                        'required' => true,
                        'type' => 'int',
                    ),
                ),
                'responses' => array(
                    200 => array(
                        'description' => 'ok',
                        'schema' => array(
                            'type' => 'array',
                            'items' => array(
                                '$ref' => '#/definitions/Pet',
                            ),
                        ),
                    ),
                    401 => array(
                        'description' => 'erreur 401',
                    ),
                    500 => array(
                        'description' => 'erreur 500',
                    ),
                ),
            ),
        );
        $this->controllerGenerator->generate(__DIR__.'/../../src/', 'testNamespace', 'testClassName', '/test', $path, false);

        $path = array(
            'get' => array(
                'description' => 'test',
                'consumes' => array(
                    0 => 'application/json',
                ),
                'parameters' => array(
                    0 => array(
                        'name' => 'test',
                        'in' => 'header',
                        'description' => 'test parameters',
                        'required' => false,
                        'type' => 'string',
                    ),
                    1 => array(
                        'name' => 'test2',
                        'in' => 'path',
                        'description' => 'test2 parameters',
                        'required' => true,
                        'type' => 'int',
                    ),
                ),
                'responses' => array(
                    200 => array(
                        'description' => 'ok',
                        'schema' => array(
                            'type' => 'array',
                            'items' => array(
                                '$ref' => '#/definitions/Pet',
                            ),
                        ),
                    ),
                    401 => array(
                        'description' => 'erreur 401',
                    ),
                    500 => array(
                        'description' => 'erreur 500',
                    ),
                ),
            ),
        );
        $this->controllerGenerator->generate(__DIR__.'/../../src/', 'testNamespace', 'testClassName', '/test', $path, false);
    }

    public function testGenerateWithAnnotation()
    {
        $path = array(
            'get' => array(
                'description' => 'test',
                'consumes' => array(
                    0 => 'application/json',
                ),
                'parameters' => array(
                    0 => array(
                        'name' => 'test',
                        'in' => 'header',
                        'description' => 'test parameters',
                        'required' => false,
                        'type' => 'string',
                    ),
                    1 => array(
                        'name' => 'test2',
                        'in' => 'header',
                        'description' => 'test2 parameters',
                        'required' => true,
                        'type' => 'int',
                    ),
                ),
                'responses' => array(
                    200 => array(
                        'description' => 'ok',
                    ),
                    401 => array(
                        'description' => 'erreur 401',
                    ),
                    500 => array(
                        'description' => 'erreur 500',
                    ),
                ),
            ),
        );
        $this->controllerGenerator->generate(__DIR__.'/../../src/', 'testNamespace', 'testClassName', '/test', $path, true);
    }

    public function testGenerateWithoutResponse()
    {
        $path = array(
            'get' => array(
                'description' => 'test',
                'consumes' => array(
                    0 => 'application/json',
                ),
                'parameters' => array(
                    0 => array(
                        'name' => 'test',
                        'in' => 'header',
                        'description' => 'test parameters',
                        'required' => false,
                        'type' => 'string',
                    ),
                    1 => array(
                        'name' => 'test2',
                        'in' => 'header',
                        'description' => 'test2 parameters',
                        'required' => true,
                        'type' => 'int',
                    ),
                ),
            ),
        );
        $this->setExpectedException('Exception', '"responses" attribute not found !');
        $this->controllerGenerator->generate(__DIR__.'/../../src/', 'testNamespace', 'testClassName', '/test', $path, false);
    }
    public function testGenerateWithoutGoodResponse()
    {
        $path = array(
            'get' => array(
                'description' => 'test',
                'consumes' => array(
                    0 => 'application/json',
                ),
                'parameters' => array(
                    0 => array(
                        'name' => 'test',
                        'in' => 'header',
                        'description' => 'test parameters',
                        'required' => false,
                        'type' => 'string',
                    ),
                    1 => array(
                        'name' => 'test2',
                        'in' => 'header',
                        'description' => 'test2 parameters',
                        'required' => true,
                        'type' => 'int',
                    ),
                ),
                'responses' => array(
                    300 => array(
                        'description' => 'erreur 401',
                    ),
                    500 => array(
                        'description' => 'erreur 500',
                    ),
                ),
            ),
        );
        $this->controllerGenerator->generate(__DIR__.'/../../src/', 'testNamespace', 'testClassName', '/test', $path, false);

        $path = array(
            'get' => array(
                'description' => 'test',
                'consumes' => array(
                    0 => 'application/json',
                ),
                'parameters' => array(
                    0 => array(
                        'name' => 'test',
                        'in' => 'header',
                        'description' => 'test parameters',
                        'required' => false,
                        'type' => 'string',
                    ),
                    1 => array(
                        'name' => 'test2',
                        'in' => 'header',
                        'description' => 'test2 parameters',
                        'required' => true,
                        'type' => 'int',
                    ),
                ),
                'responses' => array(
                    401 => array(
                        'description' => 'erreur 401',
                    ),
                    500 => array(
                        'description' => 'erreur 500',
                    ),
                ),
            ),
        );
        $this->controllerGenerator->generate(__DIR__.'/../../src/', 'testNamespace', 'testClassName', '/test', $path, false);

        $path = array(
            'get' => array(
                'description' => 'test',
                'consumes' => array(
                    0 => 'application/json',
                ),
                'parameters' => array(
                    0 => array(
                        'name' => 'test',
                        'in' => 'header',
                        'description' => 'test parameters',
                        'required' => false,
                        'type' => 'string',
                    ),
                    1 => array(
                        'name' => 'test2',
                        'in' => 'header',
                        'description' => 'test2 parameters',
                        'required' => true,
                        'type' => 'int',
                    ),
                ),
                'responses' => array(
                    500 => array(
                        'description' => 'erreur 500',
                    ),
                ),
            ),
        );
        $this->controllerGenerator->generate(__DIR__.'/../../src/', 'testNamespace', 'testClassName', '/test', $path, false);
    }

    public function testGetAvailableExceptions()
    {
        $exceptions = $this->controllerGenerator->getAvailableExceptions();
        $this->assertInternalType('array', $exceptions);

        foreach (array(401, 403, 404) as $status) {
            $this->assertArrayHasKey($status, $exceptions);
        }
    }

    public function testHasResponseSchema()
    {
        $response = array(
            'description' => 'successful operation',
        );

        $this->assertFalse($this->controllerGenerator->hasResponseSchema($response));

        $response = array(
                'description' => 'successful operation',
                'schema' => array(
                    'type' => 'array',
                ),
                'items' => array(
                    '$ref' => '#/definitions/Pet',
                ),
            );

        $this->assertTrue($this->controllerGenerator->hasResponseSchema($response));
    }

    public function testhasResponseEntity()
    {
        $response = array(
            'description' => 'successful operation',
        );

        $this->assertFalse($this->controllerGenerator->hasResponseEntity($response));

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
            ),
            'items' => array(
                '$ref' => '#/definitions/Pet',
            ),
        );

        $this->assertFalse($this->controllerGenerator->hasResponseEntity($response));

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                '$ref' => '#/definitions/Pet',
            ),
        );

        $this->assertTrue($this->controllerGenerator->hasResponseEntity($response));
    }

    public function testHasResponseCollection()
    {
        $response = array(
            'description' => 'successful operation',
        );

        $this->assertFalse($this->controllerGenerator->hasResponseCollection($response));

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
                'items' => array(
                    '$ref' => '#/definitions/Pet',
                ),
            ),
        );

        $this->assertTrue($this->controllerGenerator->hasResponseCollection($response));

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                '$ref' => '#/definitions/Pet',
            ),
        );

        $this->assertfalse($this->controllerGenerator->hasResponseCollection($response));
    }

    /**
     * @expectedException \Exception
     */
    public function testHasResponseCollectionWithException()
    {
        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
            ),

        );
        $res = $this->controllerGenerator->hasResponseCollection($response);
    }

    public function testGetAssociatedEntity()
    {
        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                '$ref' => '#/definitions/Pet',
            ),
        );
        $res = $this->controllerGenerator->getAssociatedEntity($response);
        $this->assertEquals('Pet', $res);

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
                'items' => array(
                    '$ref' => '#/definitions/Pet',
                ),
            ),
        );

        $res = $this->controllerGenerator->getAssociatedEntity($response);
        $this->assertEquals(false, $res);
    }

    public function testGetAssociatedCollection()
    {
        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                '$ref' => '#/definitions/Pet',
            ),
        );
        $res = $this->controllerGenerator->getAssociatedCollection($response);
        $this->assertEquals(false, $res);

        $response = array(
            'description' => 'successful operation',
            'schema' => array(
                'type' => 'array',
                'items' => array(
                    '$ref' => '#/definitions/Pet',
                ),
            ),
        );

        $res = $this->controllerGenerator->getAssociatedCollection($response);
        $this->assertEquals('PetCollection', $res);
    }

    public function testExtractCodesbyStatus()
    {
        $this->assertEquals(array(200), $this->controllerGenerator->extractCodesByStatus(array(200, 401, 403, 500), 2));
        $this->assertEquals(array(), $this->controllerGenerator->extractCodesByStatus(array(200, 401, 403, 500), 3));
        $this->assertEquals(array(401, 403), $this->controllerGenerator->extractCodesByStatus(array(200, 401, 403, 500), 4));
        $this->assertEquals(array(500), $this->controllerGenerator->extractCodesByStatus(array(200, 401, 403, 500), 5));
    }

    public function testGetActionParameters()
    {
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
                'name' => 'code',
                'in' => 'path',
                'type' => 'integer',
                'required' => true,
            ),
            array(
                'description' => 'user language',
                'name' => 'acceptLanguage',
                'in' => 'header',
                'type' => 'string',
                'required' => false,
            ),
        ), $this->controllerGenerator->getActionParameters('get', $objects));
    }

    /**
     * @expectedException \Exception
     */
    public function testGetActionParametersWithException()
    {
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

        $res = $this->controllerGenerator->getActionParameters('get', $objects);
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateActionParametersWithException()
    {
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

        $res = $this->controllerGenerator->createActionParameters(200, $objects);
    }

    /**
     *
     */
    public function testGetRoutingAnnotation()
    {
        $this->assertEquals(array(
            'route' => '/pets',
            'parameters' => array(
                'name' => 'pets_get',
            ),
            'method' => 'GET',
        ), $this->controllerGenerator->getRoutingAnnotation('get', '/pets'));
    }

    /**
     *
     */
    public function testGetActionComments()
    {
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
        ), $this->controllerGenerator->getActionComments('get', $objects, '/pets', false));

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
                'parameters' => array(
                    'name' => 'pets_get', ),
                'method' => 'GET',
            ),
            'params' => array('id'),
        ), $this->controllerGenerator->getActionComments('get', $objects, '/pets', true));
    }

    public function testExtractArguments()
    {
        include_once __DIR__.'/../Controller/Mock/ProductController.php';
        $reflexion = new \ReflectionClass('Enneite\SwaggerBundle\Tests\Tests\Controller\Mock\ProductController');
        $method = $reflexion->getMethod('getAction');

        $this->assertEquals(array('id'), $this->controllerGenerator->extractArguments($method));
    }
}
