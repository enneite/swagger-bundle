<?php

/**
 * Created by JetBrains PhpStorm.
 * User: bernard.caron
 * Date: 03/11/15
 * Time: 23:36
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Creator;

use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;

class BundleCreator extends Creator
{
    private $nameSpace;
    private $bundleName;

    public function createBundle($outpuPath, $nameSpace, $bundleName)
    {
        $this->nameSpace = $nameSpace;
        $this->bundleName = $bundleName;

        $this->createFiles($outpuPath);

        if (!$this->bundleExists($nameSpace . $bundleName)) {
            $this->addToKernel();
        }
        die;
    }

    private function createFiles($outpuPath)
    {
        $target = $outpuPath . $this->nameSpace . $this->bundleName . '.php';
        $template = 'Bundle.php.twig';
        $parameter = array(
            'namespace' => $this->nameSpace . '\\' . $this->bundleName,
            'className' => $this->nameSpace . $this->bundleName
        );
        $this->renderFile($template, $target, $parameter);
    }

    private function addToKernel()
    {
        $kernelManipulator = new KernelManipulator($this->container->get('kernel'));
        $kernelManipulator->addBundle($this->nameSpace . '\\' . $this->bundleName . '\\' . $this->nameSpace . $this->bundleName);
    }

    private function bundleExists($bundleName)
    {
        return array_key_exists(
            $bundleName,
            $this->container->getParameter('kernel.bundles')
        );
    }

}
