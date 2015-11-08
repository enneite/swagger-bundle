<?php

/**
 * Created by JetBrains PhpStorm.
 * User: bernard.caron
 * Date: 03/11/15
 * Time: 23:36
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;

class BundleGenerator extends Generator
{
    private $namespace;
    private $bundleName;

    /**
     * @param $outpuPath
     * @param $namespace
     * @param $bundleName
     *
     * @return int
     */
    public function generate($outpuPath, $namespace, $bundleName)
    {
        $this->namespace = $namespace;
        $this->bundleName = $bundleName;

        $this->createBundleFile($outpuPath);

        if (!$this->bundleExists($namespace.$bundleName)) {
            $this->addToKernel();

            return 1; // add to kernal
        }

        return 2; // already in kernel
    }

    /**
     * @param $outpuPath
     */
    private function createBundleFile($outpuPath)
    {
        $target = $outpuPath.$this->namespace.$this->bundleName.'.php';
        $template = 'Bundle.php.twig';
        $parameter = array(
            'namespace' => $this->namespace.'\\'.$this->bundleName,
            'className' => $this->namespace.$this->bundleName,
        );
        $this->renderFile($template, $target, $parameter);
    }

    /**
     *
     */
    private function addToKernel()
    {
        $kernelManipulator = new KernelManipulator($this->container->get('kernel'));
        $kernelManipulator->addBundle($this->namespace.'\\'.$this->bundleName.'\\'.$this->namespace.$this->bundleName);
    }

    /**
     * @param $bundleName
     *
     * @return bool
     */
    private function bundleExists($bundleName)
    {
        return array_key_exists(
            $bundleName,
            $this->container->get('kernel')->getBundles()
        );
    }
}
