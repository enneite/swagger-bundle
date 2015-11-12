<?php
/**
 * Created by PhpStorm.
 * User: etienne
 * Date: 12/11/15
 * Time: 23:33
 */

namespace Enneite\SwaggerBundle\Tests\Security;


use Enneite\SwaggerBundle\Security\SecurityDefinition;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class SecurityDefinitionTest extends WebTestCase
{

    public function testGettersSetters()
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

        $this->assertEquals('http://test.com/auth', $def->getAuthorizationUrl());
        $this->assertEquals('some description...', $def->getDescription());
        $this->assertEquals('password', $def->getFlow());
        $this->assertEquals('oauth2', $def->getType());
        $this->assertEquals('Authorization', $def->getName());
        $this->assertEquals('header', $def->getIn());
        $this->assertEquals('http://test.com/token', $def->getTokenUrl());
        $this->assertEquals(array(), $def->getScopes());



    }

    public function testExtractAccessToken()
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

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer 12345');

        $this->assertEquals('Bearer 12345', $def->extractAccessToken($request));

        $def->setIn('query');
        $this->assertEquals(null, $def->extractAccessToken($request));

        $def->setIn('header')->setName('x-auth');
        $this->assertEquals(null, $def->extractAccessToken($request));

        $def = new SecurityDefinition();
        $def->setAuthorizationUrl('http://test.com/auth')
            ->setDescription('some description...')
            ->setFlow('password')
            ->setType('oauth2')
            ->setName('Authorization')
            ->setIn('header')
            ->setTokenUrl('http://test.com/token')
            ->setScopes(array());

        $request = new Request(array('Authorization' => 'Bearer 12345'));

        $this->assertEquals(null, $def->extractAccessToken($request));

        $def->setIn('query');
        $this->assertEquals('Bearer 12345', $def->extractAccessToken($request));

        $def->setName('x-auth');
        $this->assertEquals(null, $def->extractAccessToken($request));

    }
} 