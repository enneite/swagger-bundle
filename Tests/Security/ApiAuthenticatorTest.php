<?php
/**
 * Created by PhpStorm.
 * User: etienne
 * Date: 12/11/15
 * Time: 23:50
 */

namespace Enneite\SwaggerBundle\Tests\Security;


use Enneite\SwaggerBundle\Security\ApiAuthenticator;
use Enneite\SwaggerBundle\Security\SecurityDefinition;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ApiAuthenticatorTest extends WebTestCase
{
    public function setUp()
    {
        $def = new SecurityDefinition();
        $def->setAuthorizationUrl('http://test.com/auth')
            ->setDescription('some description...')
            ->setFlow('password')
            ->setType('oauth2')
            ->setName('Authorization')
            ->setIn('header')
            ->setTokenUrl('http://test.com/token')
            ->setScopes(array());

        $this->authenticator = new ApiAuthenticator($def);
    }

    public function testCreateToken()
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer 12345');
        $token = $this->authenticator->createToken($request, 'abc123');

        $this->assertInstanceOf('Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken', $token);
        $this->assertEquals('Bearer 12345', $token->getCredentials());
    }

} 