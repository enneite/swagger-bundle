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
    private $nameSpace;
    private $bundleName;

    /**
     * @param $outpuPath
     * @param $nameSpace
     * @param $bundleName
     */
    public function generate($outpuPath, $nameSpace, $bundleName)
    {
        $this->nameSpace = $nameSpace;
        $this->bundleName = $bundleName;

        $this->createBundleFile($outpuPath);

        if (!$this->bundleExists($nameSpace.$bundleName)) {
            $this->addToKernel();
        }
    }

    /**
     * @param $outpuPath
     */
    private function createBundleFile($outpuPath)
    {
        $target = $outpuPath.$this->nameSpace.$this->bundleName.'.php';
        $template = 'Bundle.php.twig';
        $parameter = array(
            'namespace' => $this->nameSpace.'\\'.$this->bundleName,
            'className' => $this->nameSpace.$this->bundleName,
        );
        $this->renderFile($template, $target, $parameter);
    }

    /**
     *
     */
    private function addToKernel()
    {
        $kernelManipulator = new KernelManipulator($this->container->get('kernel'));
        $kernelManipulator->addBundle($this->nameSpace.'\\'.$this->bundleName.'\\'.$this->nameSpace.$this->bundleName);
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
            $this->container->getParameter('kernel.bundles')
        );
    }
}
