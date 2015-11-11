<?php
/**
 * Created by PhpStorm.
 * User: etienne
 * Date: 11/11/15
 * Time: 07:46
 */

namespace Enneite\SwaggerBundle\Security;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;

class ApiAuthenticator implements SimplePreAuthenticatorInterface
{
    protected $securityDefinition;

    /**
     *
     * @param SecurityDefinition $securityDefinition
     */
    public function __construct(SecurityDefinition $securityDefinition)
    {
        $this->securityDefinition = $securityDefinition;
    }

    /**
     * @param Request $request
     * @param $providerKey
     * @return PreAuthenticatedToken
     */
    public function createToken(Request $request, $providerKey)
    {
        $user = 'anon';
        $credentials = $this->securityDefinition->extractAccessToken($request);
        return new PreAuthenticatedToken($user, $credentials, $providerKey);
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $accessToken = $token->getCredentials();
        $user = $userProvider->loadByUserName($accessToken);

        $token->setUser($user);

        return $token;
    }


    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken  && $token->getProviderKey() === $providerKey;
    }

} 