<?php

/**
 * Created by JetBrains PhpStorm.
 * User: bernard.caron
 * Date: 03/11/15
 * Time: 23:36
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Generator;

use Enneite\SwaggerBundle\Manipulator\SwaggerKernelManipulator;

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

            return 1; // add to kernel
        }

        return 2; // already in kernel
    }

    /**
     * @param $outpuPath
     * @param $namespace
     * @param $bundleName
     *
     * @return int
     */
    public function deleteBundle($outpuPath, $namespace, $bundleName)
    {
        $this->namespace = $namespace;
        $this->bundleName = $bundleName;
        if (!$this->filesystem->exists($outpuPath)) {
            return false;
        }
        $namespacePath = realpath($outpuPath.'../');
        $this->filesystem->remove($outpuPath);
        if (count(scandir($namespacePath)) == 2) {
            $this->filesystem->remove($namespacePath);
        }
        if ($this->bundleExists($namespace.$bundleName)) {
            $this->removeToKernel();

            return 1; // remove to kernel
        }

        return 2; // not in kernel
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
        $kernelManipulator = new SwaggerKernelManipulator($this->container->get('kernel'));
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

    /**
     *
     */
    private function removeToKernel()
    {
        $kernelManipulator = new SwaggerKernelManipulator($this->container->get('kernel'));
        $kernelManipulator->removeBundle($this->namespace.'\\'.$this->bundleName.'\\'.$this->namespace.$this->bundleName);
    }
}
