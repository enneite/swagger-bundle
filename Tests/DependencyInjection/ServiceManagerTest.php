<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 01/07/15
 * Time: 11:57
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Tests\DependencyInjection;

use Enneite\SwaggerBundle\Creator\FileCreator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Enneite\SwaggerBundle\DependencyInjection\ServiceManager;
use Symfony\Component\Filesystem\Filesystem;

class ServiceManagerTest extends WebTestCase
{
    public function testInit()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'enneite_swagger.file_creator') {
                    return new FileCreator(new Filesystem());
                }

            }));

        $serviceManager = new ServiceManager();
        $serviceManager->setContainer($container)->init();

        $this->assertInstanceOf('\Twig_Environment', $serviceManager->getTwigEnv(), 'twigEnv property must be an instance of \Twig_Environment');
        $this->assertInstanceOf('Enneite\SwaggerBundle\Creator\FileCreator', $serviceManager->getFileCreator(), 'FileCreator property must be an instance of FileCreator');
        $this->assertInstanceOf('Enneite\SwaggerBundle\Creator\ApiModelCreator', $serviceManager->getApiModelCreator(), 'FileCreator property must be an instance of ApiModelCreator');
        $this->assertInstanceOf('Enneite\SwaggerBundle\Creator\ApiControllerCreator', $serviceManager->getApiControllerCreator(), 'Controller property must be an instance of ApiControllerCreator');
        $this->assertInstanceOf('Enneite\SwaggerBundle\Creator\ApiRoutingCreator', $serviceManager->getApiRoutingCreator(), 'ApiRoutingCreator property must be an instance of ApiRoutingCreator');
        $this->assertInstanceOf('Enneite\SwaggerBundle\Creator\manager', $serviceManager->getCreatorsManager(), 'CreatorsManager property must be an instance of Symfony\Component\DependencyInjection\CreatorsManager');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Container', $serviceManager->getContainer(), 'Container property must be an instance of Symfony\Component\DependencyInjection\Container');
    }
}
