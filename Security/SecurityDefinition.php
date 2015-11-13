<?php

namespace Enneite\SwaggerBundle\Security;


use Symfony\Component\HttpFoundation\Request;

class SecurityDefinition
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $in;

    /**
     * @var string
     */
    protected $flow;

    /**
     * @var string
     */
    protected $authorizationUrl;

    /**
     * @var string
     */
    protected $tokenUrl;

    /**
     * @var array
     */
    protected $scopes = array();

    /**
     * @param string $authorizationUrl
     */
    public function setAuthorizationUrl($authorizationUrl)
    {
        $this->authorizationUrl = $authorizationUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorizationUrl()
    {
        return $this->authorizationUrl;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $flow
     */
    public function setFlow($flow)
    {
        $this->flow = $flow;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * @param string $in
     */
    public function setIn($in)
    {
        $this->in = $in;

        return $this;
    }

    /**
     * @return string
     */
    public function getIn()
    {
        return $this->in;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param array $scopes
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;

        return $this;
    }

    /**
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param string $tokenUrl
     */
    public function setTokenUrl($tokenUrl)
    {
        $this->tokenUrl = $tokenUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getTokenUrl()
    {
        return $this->tokenUrl;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * extract access token from the current http request
     *
     * @param Request $request
     * @return array|mixed|null|string
     */
    public function extractAccessToken(Request $request)
    {
        $accessToken = null;

        if($this->getIn() == 'query') {
            $obj = $request->query;
        }
        else {
            $obj = $request->headers;
        }

        if($obj->has($this->getName())) {
            $accessToken = $obj->get($this->getName());
        }

        return $accessToken;
    }


} 