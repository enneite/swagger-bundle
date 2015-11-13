<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 01/07/15
 * Time: 16:20
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Tests\Creator;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Enneite\SwaggerBundle\Creator\Manager;

class ManagerTest extends WebTestCase
{
    public function setup()
    {
    }

    public function testInit()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();
        $fileCreator->expects($this->any())
            ->method('realpath')
            ->will($this->returnValue(realpath((__DIR__ . '/../../Resources/example/config/swagger.json'))));

        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $kernel               = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')->disableOriginalConstructor()->getMock();
        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../Resources/example'));

        // case 1 : swagger file not found!
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $manager   = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);
        $caught    = false;
        try {
            $manager->init();
        } catch (\Exception $e) {
            $caught = true;
        }
        $this->assertTrue($caught);

        // case 2 swagger file founs but sometime incomplete
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();

        $container->expects($this->at(0))
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'config_file' => '%kernel.root_dir%/config/swagger.yml',
                        'routing' => 'yaml',
                        'destination_namespace' => 'Demo\AcmeBundle',
                        );
                }
            }));
        $container->expects($this->at(1))
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'config_file' => '%kernel.root_dir%/config/swagger.yml',
                        'routing' => 'yaml',
                        'destination_bundle' => 'DemoAcmeBundle',
                    );
                }
            }));
        $container->expects($this->at(2))
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'config_file' => '%kernel.root_dir%/config/swagger.yml',
                        'routing' => 'yaml',
                        'destination_bundle' =>  'DemoAcmeBundle',
                        'destination_namespace' => 'Demo\AcmeBundle',
                    );
                }
            }));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($paramName) use ($kernel) {
                if (func_get_arg(0) == 'kernel') {
                    return $kernel;
                }
            }));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);

        // first call :
        $caught = false;
        try {
            $manager->init();
        } catch (\Exception $e) {
            $caught = true;
        }
        $this->assertTrue($caught);

        // second call:
        $caught = false;
        try {
            $manager->init();
        } catch (\Exception $e) {
            $caught = true;
        }
        $this->assertTrue($caught);

        //last call :
        $manager->init();
        $this->assertEquals('Demo\AcmeBundle', \PHPUnit_Framework_Assert::readAttribute($manager, 'mainNamespace'));
        $this->assertEquals('DemoAcmeBundle', \PHPUnit_Framework_Assert::readAttribute($manager, 'bundleName'));
        $this->assertEquals('Demo\AcmeBundle\Controller\Api', \PHPUnit_Framework_Assert::readAttribute($manager, 'controllersNamespace'));
        $this->assertEquals('Demo\AcmeBundle\Api\Model', \PHPUnit_Framework_Assert::readAttribute($manager, 'modelsNamespace'));
        $this->assertEquals('yaml', \PHPUnit_Framework_Assert::readAttribute($manager, 'routingType'));
        $this->assertEquals('yaml', $manager->getRoutingType());
    }

    /**
     * @expectedException \Exception
     */
    public function testInitRealPathNoFound()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();
        $fileCreator->expects($this->any())
            ->method('realpath')
            ->will($this->returnValue(null));

        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $kernel               = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')->disableOriginalConstructor()->getMock();
        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue('/this/is/a/bad/path'));

        // case 1 : swagger file not found!
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'config_file' => '%kernel.root_dir%/config/swagger.yml',
                        'routing' => 'yaml',
                        'destination_bundle' => 'DemoAcmeBundle',
                        'destination_namespace' => 'Demo\AcmeBundle',
                    );
                }
            }));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($paramName) use ($kernel) {
                if (func_get_arg(0) == 'kernel') {
                    return $kernel;
                }
            }));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);
        $manager->init();
    }

    public function testGetConfigConfigFileYaml()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();

        $fileCreator->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $fileCreator->expects($this->any())
            ->method('get')
            ->will($this->returnValue(file_get_contents(realpath(__DIR__ . '/../../Resources/example/config/swagger.yaml'))));

        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $kernel               = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')->disableOriginalConstructor()->getMock();
        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../Resources/example'));

        // case 1 : swagger file not found!
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'config_file' => '%kernel.root_dir%/config/swagger.yaml',
                        'routing' => 'yaml',
                        'destination_bundle' =>  'DemoAcmeBundle',
                        'destination_namespace' => 'Demo\AcmeBundle',
                    );
                }
            }));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);
        $config  = $manager->getConfig();
        $this->assertInternalType('array', $config);
        $this->assertEquals('1.0.0', $config['info']['version']);
    }

    public function testGetConfigConfigFileJson()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();

        $fileCreator->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $fileCreator->expects($this->any())
            ->method('get')
            ->will($this->returnValue(file_get_contents(realpath(__DIR__ . '/../../Resources/example/config/swagger.json'))));

        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $kernel               = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')->disableOriginalConstructor()->getMock();
        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../Resources/example'));

        // case 1 : swagger file not found!
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'config_file' => '%kernel.root_dir%/config/swagger.json',
                        'routing' => 'yaml',
                        'destination_bundle' => 'DemoAcmeBundle',
                        'destination_namespace' => 'Demo\AcmeBundle',
                    );
                }
            }));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);
        $config  = $manager->getConfig();
        $this->assertInternalType('array', $config);
        $this->assertEquals('1.0.0', $config['info']['version']);
    }

    public function testGetConfigConfigFileNotExistsDefaultJson()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();

        $fileCreator->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(true));

        $fileCreator->expects($this->any())
            ->method('get')
            ->will($this->returnValue(file_get_contents(realpath(__DIR__ . '/../../Resources/example/config/swagger.json'))));

        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $kernel               = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')->disableOriginalConstructor()->getMock();
        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../Resources/example'));

        // case 1 : swagger file not found!
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'routing' => 'yaml',
                        'destination_bundle' => 'DemoAcmeBundle',
                        'destination_namespace' => 'Demo\AcmeBundle',
                    );
                }
            }));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($paramName) use ($kernel) {
                if (func_get_arg(0) == 'kernel') {
                    return $kernel;
                }
            }));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator,$apiSecurityCreator, $apiControllerCreator);
        $config  = $manager->getConfig();
        $this->assertInternalType('array', $config);
        $this->assertEquals('1.0.0', $config['info']['version']);
    }

    public function testGetConfigConfigFileNotExistsDefaultYml()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();

        $fileCreator->expects($this->at(0))
            ->method('exists')
            ->will($this->returnValue(false));
        $fileCreator->expects($this->at(1))
            ->method('exists')
            ->will($this->returnValue(true));

        $fileCreator->expects($this->any())
            ->method('get')
            ->will($this->returnValue(file_get_contents(realpath(__DIR__ . '/../../Resources/example/config/swagger.yml'))));

        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $kernel               = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')->disableOriginalConstructor()->getMock();
        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../Resources/example'));

        // case 1 : swagger file not found!
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'routing' => 'yaml',
                        'destination_bundle' => 'DemoAcmeBundle',
                        'destination_namespace' => 'Demo\AcmeBundle',
                    );
                }
            }));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($paramName) use ($kernel) {
                if (func_get_arg(0) == 'kernel') {
                    return $kernel;
                }
            }));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiSecurityCreator, $apiRoutingCreator, $apiControllerCreator);
        $config  = $manager->getConfig();
        $this->assertInternalType('array', $config);
        $this->assertEquals('1.0.0', $config['info']['version']);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetConfigConfigFileNotExistsException()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();

        $fileCreator->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));

        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $kernel               = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')->disableOriginalConstructor()->getMock();
        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../Resources/example'));

        // case 1 : swagger file not found!
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'routing' => 'yaml',
                        'destination_bundle' => 'DemoAcmeBundle',
                        'destination_namespace' => 'Demo\AcmeBundle',
                    );
                }
            }));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($paramName) use ($kernel) {
                if (func_get_arg(0) == 'kernel') {
                    return $kernel;
                }
            }));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);
        $config  = $manager->getConfig();
    }

    /**
     * @expectedException \Exception
     */
    public function testGetConfigConfigFileNotExistsException2()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();

        $fileCreator->expects($this->any())
            ->method('exists')
            ->will($this->returnValue(false));

        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $kernel               = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')->disableOriginalConstructor()->getMock();
        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnValue(__DIR__ . '/../../Resources/example'));

        // case 1 : swagger file not found!
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnCallback(function ($paramName) {
                if (func_get_arg(0) == 'swagger') {
                    return array(
                        'config_file' => '/path/not/exists/swagger;json',
                        'routing' => 'yaml',
                        'destination_bundle' =>  'DemoAcmeBundle',
                        'destination_namespace' => 'Demo\AcmeBundle',
                    );
                }
            }));

        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($paramName) use ($kernel) {
                if (func_get_arg(0) == 'kernel') {
                    return $kernel;
                }
            }));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);
        $config  = $manager->getConfig();
    }

    public function testCreateNamespace()
    {
        $manager   = new Manager(null, null, null, null, null, null);
        $namespace = $manager->createNamespace('Enneite\SwaggerBundle', array('api', 'model', 'product'));
        $this->assertEquals('Enneite\SwaggerBundle\Api\Model\Product', $namespace);
    }

    public function testCreateRoutingYamlFile()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();
        $fileCreator->expects($this->any())
            ->method('createDirectory')
            ->will($this->returnvalue(true));
        $fileCreator->expects($this->any())
            ->method('createFile')
            ->will($this->returnvalue(true));
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator->expects($this->any())
            ->method('createYamlConf')
            ->will($this->returnvalue('#comments ...'));
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator->expects($this->any())
            ->method('getClassName')
            ->will($this->returnvalue('Product'));
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $output    = $this->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')->disableOriginalConstructor()->getMock();
        $output->expects($this->any())
            ->method('writeln')
            ->will($this->returnvalue(null));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator,$apiSecurityCreator, $apiControllerCreator);
        $manager->setOutputPath('/destination/path');

        $json  = json_decode(file_get_contents(realpath(__DIR__ . '/../../Resources/example/config/swagger.json')), true);
        $paths = $json['paths'];

        $caught = false;
        try {
            $manager->createRoutingYamlFile($paths, $output);
        } catch (\Exception $e) {
            $caught = true;
        }
        $this->assertfalse($caught);
    }

    public function testCreateSecurityYamlFile()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();
        $fileCreator->expects($this->any())
            ->method('createDirectory')
            ->will($this->returnvalue(true));
        $fileCreator->expects($this->any())
            ->method('createFile')
            ->will($this->returnvalue(true));
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator  = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator->expects($this->any())
            ->method('createYamlConf')
            ->will($this->returnvalue('#comments ...'));
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator->expects($this->any())
            ->method('createSecurityYaml')
            ->will($this->returnvalue('#comments ...'));
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator->expects($this->any())
            ->method('getClassName')
            ->will($this->returnvalue('Product'));
        $kernel = $this->getMockBuilder('Symfony\Component\HttpKernel\Kernel')->disableOriginalConstructor()->getMock();
        $kernel->expects($this->any())
            ->method('getRootDir')
            ->will($this->returnvalue('/var/www/demo/app'));
        return $kernel;
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function($val) use($kernel){
                return $kernel;
            }));

        $output    = $this->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')->disableOriginalConstructor()->getMock();
        $output->expects($this->any())
            ->method('writeln')
            ->will($this->returnvalue(null));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator,$apiSecurityCreator, $apiControllerCreator);
        $manager->setOutputPath('/destination/path');

        $json  = json_decode(file_get_contents(realpath(__DIR__ . '/../../Resources/example/config/swagger.json')), true);
        $paths = $json['paths'];

        $caught = false;
        try {
            $manager->createSecurityYamlFile($json['basePath'], null, $paths, $output);
        } catch (\Exception $e) {
            $caught = true;
        }
        $this->assertfalse($caught);
    }

    public function testCreateDefinitions()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();
        $fileCreator->expects($this->any())
            ->method('createDirectory')
            ->will($this->returnvalue(true));
        $fileCreator->expects($this->any())
            ->method('createFile')
            ->will($this->returnvalue(true));
        $apiModelCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiModelCreator->expects($this->any())
            ->method('createModel')
            ->will($this->returnvalue('<?php ?>'));
        $apiModelCreator->expects($this->any())
            ->method('createModelFactory')
            ->will($this->returnvalue('<?php ?>'));
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $container            = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $output               = $this->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')->disableOriginalConstructor()->getMock();
        $output->expects($this->any())
            ->method('writeln')
            ->will($this->returnvalue(null));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);
        $manager->setOutputPath('/destination/path');

        $json        = json_decode(file_get_contents(realpath(__DIR__ . '/../../Resources/example/config/swagger.json')), true);
        $definitions = $json['definitions'];

        $caught = false;
        try {
            $manager->createDefinitions($definitions, $output);
        } catch (\Exception $e) {
            $caught = true;
        }
        $this->assertfalse($caught);
    }

    public function testCreateControllers()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();
        $fileCreator->expects($this->any())
            ->method('createDirectory')
            ->will($this->returnvalue(true));
        $fileCreator->expects($this->any())
            ->method('createFile')
            ->will($this->returnvalue(true));
        $fileCreator->expects($this->any())
            ->method('exists')
            ->will($this->returnvalue(false));
        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator->expects($this->any())
            ->method('createController')
            ->will($this->returnvalue('<?php ?>'));
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $output    = $this->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')->disableOriginalConstructor()->getMock();
        $output->expects($this->any())
            ->method('writeln')
            ->will($this->returnvalue(null));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);
        $manager->setOutputPath('/destination/path');

        $json  = json_decode(file_get_contents(realpath(__DIR__ . '/../../Resources/example/config/swagger.json')), true);
        $paths = $json['paths'];

        $caught = false;
        try {
            $manager->createControllers($paths, false, $output);
        } catch (\Exception $e) {
            $caught = true;
        }
        $this->assertfalse($caught);
    }

    public function testRegenerateControllers()
    {
        $fileCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\FileCreator')->disableOriginalConstructor()->getMock();
        $fileCreator->expects($this->any())
            ->method('createDirectory')
            ->will($this->returnvalue(true));
        $fileCreator->expects($this->any())
            ->method('createFile')
            ->will($this->returnvalue(true));
        $fileCreator->expects($this->any())
            ->method('exists')
            ->will($this->returnvalue(true));
        $apiModelCreator   = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiModelCreator')->disableOriginalConstructor()->getMock();
        $apiRoutingCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiRoutingCreator')->disableOriginalConstructor()->getMock();
        $apiSecurityCreator    = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiSecurityCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator = $this->getMockBuilder('Enneite\SwaggerBundle\Creator\ApiControllerCreator')->disableOriginalConstructor()->getMock();
        $apiControllerCreator->expects($this->any())
            ->method('createAction')
            ->will($this->returnvalue('public function getAction(){}'));
        $apiControllerCreator->expects($this->any())
            ->method('getClassName')
            ->will($this->returnvalue('ProductController'));
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $output    = $this->getMockBuilder('Symfony\Component\Console\Output\ConsoleOutput')->disableOriginalConstructor()->getMock();
        $output->expects($this->any())
            ->method('writeln')
            ->will($this->returnvalue(null));

        $manager = new Manager($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator);
        $manager->setOutputPath('/destination/path');
        $manager->setControllersNamespace('Enneite\SwaggerBundle\Tests\Controller\Mock');

        $paths = array(
            '/product' => array(
                'get' => array(
                    'responses' => array(),
                ),
                'post' => array(
                    'responses' => array(),
                ),
            ),
        );

        include_once __DIR__ . '/../Controller/Mock/ProductController.php';

        $caught = false;
        try {
            $manager->createControllers($paths, false, $output);
        } catch (\Exception $e) {
            $caught = true;
        }
        $this->assertfalse($caught);
    }
}
