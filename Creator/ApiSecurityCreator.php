<?php
/**
 * Created by PhpStorm.
 * User: etienne
 * Date: 10/11/15
 * Time: 21:48
 */

namespace Enneite\SwaggerBundle\Creator;

use Symfony\Component\Yaml\Parser;

class ApiSecurityCreator {

    const AUTHENTICATOR_ID_PREFIX = 'enneite_swagger.api_autenticator_';
    const SECURITY_DEFINITION_ID_PREFIX = 'enneite_swagger.api_security_definition_';

    /**
     * @var
     */
    protected $twig;

    /**
     * @var \Symfony\Component\Yaml\Parser
     */
    protected $apiRoutingCreator;

    public function __construct($twig, $apiRoutingCreator)
    {
        $this->setTwig($twig);
        $this->setApiRoutingCreator($apiRoutingCreator);

    }

    /**
     * @param \Symfony\Component\Yaml\Parser $apiRoutingCreator
     */
    public function setApiRoutingCreator($apiRoutingCreator)
    {
        $this->apiRoutingCreator = $apiRoutingCreator;

        return $this;
    }

    /**
     * @return \Symfony\Component\Yaml\Parser
     */
    public function getApiRoutingCreator()
    {
        return $this->apiRoutingCreator;
    }



    /**
     * @param $twig
     *
     * @return $this
     */
    public function setTwig($twig)
    {
        $this->twig = $twig;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTwig()
    {
        return $this->twig;
    }

    public function createSecurityYaml($basePath, $security, $paths)
    {
        $b = '';

        if(null != $security){
            $firewallName = 'api_base_path';
            $securityDefinitionsKeys = array();
            foreach($security as $securityItem) {
                $securityKeys = array_keys($securityItem);
                $securityDefinitionsKeys[] = $securityKeys[0];
            }
            $b.= "    ".'api_base_path'.":\n";
            $b.= "      ".'stateless: true'."\n";
            $b.= "      ".'pattern: ' . '^'.$basePath."\n";
            foreach($securityDefinitionsKeys as $securityDefinitionKey) {
                $b.= "      ".'simple_preauth: '."\n";
                $b.= "        ".'provider: ' . 'api_'.$securityDefinitionKey ."\n";
                $b.= "        ".'authenticator: ' . self::buildAuthenticatorId($securityDefinitionKey) . "\n";
            }
        }


        foreach($paths as $pathName => $path) {
            foreach($path as  $verb => $objects) {
                $parameters = $this->apiRoutingCreator->getRouteParametersAsArray($verb, $objects, $pathName);
                $firewallName = 'api_'.$parameters['name'];
                if(isset($objects['security'])) {

                    $securityDefinitionsKeys = array();
                    foreach($objects['security'] as $securityItem) {
                        $securityKeys = array_keys($securityItem);
                        $securityDefinitionsKeys[] = $securityKeys[0];
                    }


                    $a = $this->apiRoutingCreator->getPathParameterAssoc($this->apiRoutingCreator->extractPathParameters($objects));

                    foreach($a as $name=>$regex) {
                        $pathName = str_replace('{'.$name.'}', $regex, $pathName);
                    }
                    $b.= "    ".$firewallName.":\n";
                    $b.= "      ".'stateless: true'."\n";
                    $b.= "      ".'pattern: ' . '^'.$basePath. $pathName ."\n";
                    $b.= "      ".'methods: ['.strtoupper($verb).']'."\n";

                    foreach($securityDefinitionsKeys as $securityDefinitionKey) {
                        $b.= "      ".'simple_preauth: '."\n";
                        $b.= "        ".'provider: ' . 'api_'.$securityDefinitionKey ."\n";
                        $b.= "        ".'authenticator: ' . self::buildAuthenticatorId($securityDefinitionKey) . "\n";
                    }

                }
                else {
                    $b.= "    ".$firewallName.":\n";
                    $b.=  "     ".'pattern: ' . '^'.$basePath. $pathName ."\n";
                    $b.=  "     ".'methods: ['.strtoupper($verb).']'."\n";
                    $b.=  "     ".'security: false'."\n";
                }
            }
        }

        $firewall = "api";

        $a = 'security: '."\n"
            ."  ". 'firewalls:'."\n"
            ."### --- COPY THIS CONTENT IN security.yml file  --- ###" ."\n\n\n"

            .$b;

        return $a;
    }

    static public function buildAuthenticatorId($name)
    {
        return self::AUTHENTICATOR_ID_PREFIX . $name;
    }

    static public function buildSecurityDefinitionServiceId($name)
    {
        return self::SECURITY_DEFINITION_ID_PREFIX . $name;
    }
} 