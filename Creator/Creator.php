<?php

/**
 * Created by PhpStorm.
 * User: bersiroth
 * Date: 04/11/2015
 * Time: 02:03
 */

namespace Enneite\SwaggerBundle\Creator;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class Creator
{
    protected $filesystem;
    protected $container;

    public function __construct(ContainerInterface $container, Filesystem $filesystem)
    {
        $this->container = $container;
        $this->filesystem = $filesystem;
    }

    protected function render($template, $parameters)
    {
        $twig = $this->getTwigEnvironment();

        return $twig->render($template, $parameters);
    }

    protected function getTwigEnvironment()
    {
        $templatesPath = realpath(__DIR__ . '/../Resources/templates/');
        return new \Twig_Environment(new \Twig_Loader_Filesystem($templatesPath), array(
            'debug' => true,
            'cache' => false,
            'strict_variables' => true,
            'autoescape' => false,
        ));
    }

    protected function renderFile($template, $target, $parameters)
    {
        if (!is_dir(dirname($target))) {
            mkdir(dirname($target), 0775, true);
        }
        return file_put_contents($target, $this->render($template, $parameters));
    }
}