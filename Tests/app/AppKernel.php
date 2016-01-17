<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Enneite\SwaggerBundle\EnneiteSwaggerBundle(),
            new Symfony\Bundle\DebugBundle\DebugBundle(),
            new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle(),
            new testNamespace\testBundleName\testNamespacetestBundleName(),
        );

        return $bundles;
    }

    public function registerTestBundle()
    {
        $bundle = new testNamespace\testBundleName\testNamespacetestBundleName();
        $name = $bundle->getName();
        $this->bundles[$name] = $bundle;
    }

    public function removeRegisterBundle($bundleName)
    {
        unset($this->bundles[$bundleName]);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
