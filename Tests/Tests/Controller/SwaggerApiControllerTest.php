<?php

namespace Enneite\SwaggerBundle\Tests\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Enneite\SwaggerBundle\Controller\SwaggerApiController;
use Enneite\SwaggerBundle\Tests\Tests\Controller\Mock\FormType;

class SwaggerApiControllerTest extends WebTestCase
{
    public function testSendJsonContent()
    {
        $data = array(
            'id' => 123,
            'name' => 'myName',
        );

        $status = 200;

        $headers = array(
            'cache' => 'true',
            'time' => 1200,
        );

        $class = new \ReflectionClass('Enneite\SwaggerBundle\Controller\SwaggerApiController');
        $method = $class->getMethod('sendJsonResponse');
        $method->setAccessible(true);

        $controller = new SwaggerApiController();

        $res = $method->invokeArgs($controller, array($data, $status, $headers));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);

        $content = json_decode($res->getContent(), true);
        $this->assertEquals($data, $content);
    }

    public function testGetJsonContent()
    {
        $data = array(
            'id' => 123,
            'name' => 'myName',
        );

        $content = json_encode($data);

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->disableOriginalConstructor()->getMock();
        $request->expects($this->any())
            ->method('getContent')
            ->will($this->returnvalue($content));

        $class = new \ReflectionClass('Enneite\SwaggerBundle\Controller\SwaggerApiController');
        $method = $class->getMethod('getJsonContent');
        $method->setAccessible(true);

        $controller = new SwaggerApiController();

        $res = $method->invokeArgs($controller, array($request));

        $this->assertEquals($data, $res);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetJsonContentWithException()
    {
        $content = 'this is not a json format!';

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->disableOriginalConstructor()->getMock();
        $request->expects($this->any())
            ->method('getContent')
            ->will($this->returnvalue($content));

        $class = new \ReflectionClass('Enneite\SwaggerBundle\Controller\SwaggerApiController');
        $method = $class->getMethod('getJsonContent');
        $method->setAccessible(true);

        $controller = new SwaggerApiController();

        $res = $method->invokeArgs($controller, array($request));
    }

    public function testSendInternalError()
    {
        $class = new \ReflectionClass('Enneite\SwaggerBundle\Controller\SwaggerApiController');
        $method = $class->getMethod('sendInternalError');
        $method->setAccessible(true);

        $controller = new SwaggerApiController();

        $res = $method->invokeArgs($controller, array(new HttpException(500, 'an error occurred')));

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);

        $content = json_decode($res->getContent(), true);
        $this->assertEquals(array(
            'error' => 'an error occurred',
            'class' => 'Symfony\Component\HttpKernel\Exception\HttpException',
        ), $content);
    }

    public function testUpgradeOptions()
    {
        $class = new \ReflectionClass('Enneite\SwaggerBundle\Controller\SwaggerApiController');
        $method = $class->getMethod('upgradeFormOptions');
        $method->setAccessible(true);

        $controller = new SwaggerApiController();

        $options = array();
        $res = $method->invokeArgs($controller, array($options));
        $this->assertEquals(array('csrf_protection' => false), $res);

        $options = array(
            'crsf_protection' => true,
        );
        $res = $method->invokeArgs($controller, array($options));
        $this->assertEquals(array('csrf_protection' => false), $res);
    }

    public function testCreateForm()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();

        $formFactory = $this->getMockBuilder('Symfony\Component\Form\FormFactory')->disableOriginalConstructor()->getMock();
        $formFactory->expects($this->any())
            ->method('create')
            ->will($this->returnvalue($form));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnvalue($formFactory));

        $controller = new SwaggerApiController();
        $controller->setContainer($container);

        include_once __DIR__.'/Mock/FormType.php';
        $type = new FormType();

        $this->assertInstanceOf('Symfony\Component\Form\Form', $controller->createForm($type, null, array()));
    }
}
