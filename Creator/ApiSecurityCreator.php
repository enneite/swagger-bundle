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
        foreach($paths as $pathName => $path) {
            foreach($path as  $verb => $objects) {
                $parameters = $this->apiRoutingCreator->getRouteParametersAsArray($verb, $objects, $pathName);
                $firewallName = 'api_'.$parameters['name'];
                if(isset($objects['security'])) {
                    $a = $this->apiRoutingCreator->getPathParameterAssoc($this->apiRoutingCreator->extractPathParameters($objects));

                    foreach($a as $name=>$regex) {
                        $pathName = str_replace('{'.$name.'}', $regex, $pathName);
                    }
                    $b.= "      ".$firewallName.":\n";
                    $b.= "        ".'stateless: true'."\n";
                    $b.= "        ".'pattern: ' . '^'.$basePath. $pathName ."\n";
                    $b.= "        ".'methods: ['.strtoupper($verb).']'."\n";
                    $b.= "        ".'simple_preauth: '."\n";
                    $b.= "          ".'provider: ' . 'enneite_swagger.api_default_provider' . "\n";
                    $b.= "          ".'authenticator: ' . 'enneite_swagger.api_oauth2_authenticator' . "\n";
                }
                else {
                    $b.= "      ".$firewallName.":\n";
                    $b.=  "       ".'pattern: ' . '^'.$basePath. $pathName ."\n";
                    $b.=  "       ".'methods: ['.strtoupper($verb).']'."\n";
                    $b.=  "       ".'security: false'."\n";
                }
            }
        }

        $firewall = "api";

        $a = 'security: '."\n"
            ."  ". 'firewalls:'."\n"
            ."    ".$firewall.":"."\n"
            .$b;

        return $a;
    }

} 