<?php
/**
 * Created by PhpStorm.
 * User: etienne
 * Date: 12/11/15
 * Time: 23:59
 */

namespace Enneite\SwaggerBundle\Tests\DependencyInjection\Compiler;


use Enneite\SwaggerBundle\DependencyInjection\Compiler\ApiSecurityPass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiSecurityPassTest extends WebTestCase
{
    public function testLoadSecurityDefinitions()
    {
        $foo = self::getMethod('loadSecurityDefinitions');
        $obj = new ApiSecurityPass();

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->disableOriginalConstructor()->getMock();
        $container->expects($this->any())
            ->method('getParameter')
            ->will($this->returnValue(realpath(__DIR__ . '/../../../Resources/example')));

        $swaggerConf = array(
            'config_file' => '%kernel.root_dir%/config/swagger.json'
        );
        $cnf = $foo->invokeArgs($obj, array($swaggerConf, $container));
        $this->assertInternalType('array', $cnf);


        $this->assertEquals(array(
            "type"=> "oauth2",
            "authorizationUrl"=> "http://petstore.swagger.wordnik.com/api/oauth/dialog",
            "flow"=> "implicit",
            "scopes"=> array(
                "write_pets"=> "modify pets in your account",
                "read_pets"=> "read your pets"
    )), $cnf['petstore_auth']);

        $swaggerConf = array(
            'config_file' => '%kernel.root_dir%/config/swagger.yml'
        );
        $cnf = $foo->invokeArgs($obj, array($swaggerConf, $container));
        $this->assertInternalType('array', $cnf);

    }

    protected static function getMethod($name) {
        $class = new \ReflectionClass('Enneite\SwaggerBundle\DependencyInjection\Compiler\ApiSecurityPass');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}