<?php
/**
 * Created by PhpStorm.
 * User: etienne
 * Date: 11/11/15
 * Time: 21:28
 */

namespace Enneite\SwaggerBundle\DependencyInjection\Compiler;

use Enneite\SwaggerBundle\Creator\ApiSecurityCreator;
use Enneite\SwaggerBundle\Security\ApiAuthenticator;
use Enneite\SwaggerBundle\Security\SecurityDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

class ApiSecurityPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $swaggerConf = $container->getParameter('swagger');
        $conf = $this->loadSecurityDefinitions($swaggerConf);
        if($conf == null) {
            return;
        }

        foreach($conf as $name => $definition) {
            $id = ApiSecurityCreator::buildAuthenticatorId($name);
            $defId = ApiSecurityCreator::buildSecurityDefinitionServiceId($name);

            $container->register($defId, 'Enneite\SwaggerBundle\Security\SecurityDefinition');
            foreach($definition as $key => $value) {
                $methodName = 'set'. ucfirst($key);
                $arguments = array($value);
                $container->getDefinition($defId)
                    ->addMethodCall($methodName, $arguments);
            }
            $container->register($id, 'Enneite\SwaggerBundle\Security\ApiAuthenticator');
            $container->getDefinition($id)
                ->setArguments(array(new Reference($defId)));
        }

    }

    protected function loadSecurityDefinitions(array $swaggerConf)
    {
        $configFile = isset($swaggerConf['config_file']) ? $swaggerConf['config_file'] : '%kernel.root_dir%/config/swagger.yml';

        if(strpos($configFile, '%kernel.root_dir%') !== false) {
            $env = getenv('SYMFONY_ENV');
            if(null == $env) {
                $env = getenv('APPLICATION_ENV');
            }
            if(null == $env) {
                $env = 'dev';
            }
            $kernel = new \AppKernel($env, false);
            $configFile = str_replace('%kernel.root_dir%', $kernel->getRootDir(), $configFile);
        }

        if(strpos($configFile, '.json') !== false) {
            $config = json_decode(file_get_contents($configFile));
        }
        else {
            $conf = Yaml::parse(file_get_contents($configFile));
        }


        return isset($conf['securityDefinitions']) ? $conf['securityDefinitions'] : null;
    }

} 