<?php
/**
 * Created by PhpStorm.
 * User: etienne
 * Date: 12/11/15
 * Time: 22:59
 */

namespace Enneite\SwaggerBundle\Tests\Creator;

use Enneite\SwaggerBundle\Creator\ApiRoutingCreator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Yaml\Parser;
use Enneite\SwaggerBundle\Creator\ApiSecurityCreator;

class ApiSecurityCreatorTest extends WebTestCase
{

    public function testGetters()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiSecurityCreator($twig, new ApiRoutingCreator($twig));
        $this->assertInstanceOf('\Twig_Environment', $creator->getTwig(), 'twig property must be an instance of \Twig_Environment');
        $this->assertInstanceOf('Enneite\SwaggerBundle\Creator\ApiRoutingCreator', $creator->getApiRoutingCreator(), 'twig property must be an instance of \Twig_Environment');
    }

    public function testBuildAuthenticatorId()
    {
        $this->assertEquals('enneite_swagger.api_autenticator_app_auth', ApiSecurityCreator::buildAuthenticatorId('app_auth'));
    }

    public function testBuildSecurityDefinitionServiceId()
    {
        $this->assertEquals('enneite_swagger.api_security_definition_app_auth', ApiSecurityCreator::buildSecurityDefinitionServiceId('app_auth'));
    }

    public function testCreateSecurityYaml()
    {
        $twig    = $this->getMockBuilder('\Twig_Environment')->disableOriginalConstructor()->getMock();
        $creator = new ApiSecurityCreator($twig, new ApiRoutingCreator($twig));

        $security = array(
            0 => array('app_auth' => array(0 => 'application:all'))
        );

        $swCnf = json_decode(file_get_contents(realpath(__DIR__ . '/../../Resources/example/config/swagger.json')), true);

        $conf = $creator->createSecurityYaml($swCnf['basePath'], $security, $swCnf['paths']);
        $yaml = new Parser();
        $this->assertInternalType('array', $yaml->parse($conf));

        $confArray = $yaml->parse($conf);
        $this->assertTrue(isset($confArray['security']['firewalls']));

        $firewalls = $confArray['security']['firewalls'];

        $this->assertTrue(isset($firewalls['api_base_path']));
        $this->assertEquals('^/v2', $firewalls['api_base_path']['pattern']);
        $this->assertEquals(true, $firewalls['api_base_path']['stateless']);
        $this->assertTrue(isset($firewalls['api_base_path']['simple_preauth']));
        $this->assertEquals('api_app_auth', $firewalls['api_base_path']['simple_preauth']['provider']);
        $this->assertEquals('enneite_swagger.api_autenticator_app_auth', $firewalls['api_base_path']['simple_preauth']['authenticator']);
    }

} 