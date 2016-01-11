<?php
/**
 * Created by PhpStorm.
 * User: bersiroth
 * Date: 10/01/2016
 * Time: 22:28.
 */
namespace Enneite\SwaggerBundle\Manipulator;

use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;

class SwaggerKernelManipulator extends KernelManipulator
{
    public function removeBundle($bundle)
    {
        if (!$this->reflected->getFilename()) {
            return false;
        }

        $src = file($this->reflected->getFilename());
        $method = $this->reflected->getMethod('registerBundles');
        $lines = array_slice($src, $method->getStartLine(), $method->getEndLine() - $method->getStartLine());

        if (false === strpos(implode('', $lines), $bundle)) {
            throw new \RuntimeException(sprintf('Bundle "%s" is not defined in "AppKernel::registerBundles()".', $bundle));
        }

        foreach ($lines as $key => $value) {
            if (false !== strpos($value, $bundle)) {
                unset($lines[$key]);
                continue;
            }
        }

        $lines = array_merge(
            array_slice($src, 0, $method->getStartLine()),
            $lines,
            array_slice($src, $method->getEndLine())
        );

        file_put_contents($this->reflected->getFilename(), implode('', $lines));

        return true;
    }
}
