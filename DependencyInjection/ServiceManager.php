<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 30/06/15
 * Time: 08:43
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Enneite\SwaggerBundle\Creator\ApiControllerCreator;
use Enneite\SwaggerBundle\Creator\ApiModelCreator;
use Enneite\SwaggerBundle\Creator\ApiRoutingCreator;
use Enneite\SwaggerBundle\Creator\ApiSecurityCreator;
use Enneite\SwaggerBundle\Creator\FileCreator;
use Enneite\SwaggerBundle\Creator\Manager;

class ServiceManager implements ContainerAwareInterface
{

    /**
     * @var
     */
    protected $twigEnv;

    /**
     * @var
     */
    protected $fileCreator;

    /**
     * resource (model) Api creation service.
     *
     * @var
     */
    protected $apiModelCreator;

    /**
     * @var
     */
    protected $apiRoutingCreator;

    /**
     * @var
     */
    protected $apiControllerCreator;

    /**
     * @var
     */
    protected $apiSecurityCreator;

    /**
     * @var
     */
    protected $creatorsManager;

    public function __construct()
    {
    }

    /**
     * @param mixed $twigEnv
     */
    public function setTwigEnv($twigEnv)
    {
        $this->twigEnv = $twigEnv;
    }

    /**
     * @return mixed
     */
    public function getTwigEnv()
    {
        return $this->twigEnv;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }

    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param mixed $apiControllerCreator
     */
    public function setApiControllerCreator($apiControllerCreator)
    {
        $this->apiControllerCreator = $apiControllerCreator;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiControllerCreator()
    {
        return $this->apiControllerCreator;
    }

    /**
     * @param mixed $apiModelCreator
     */
    public function setApiModelCreator($apiModelCreator)
    {
        $this->apiModelCreator = $apiModelCreator;
    }

    /**
     * @return mixed
     */
    public function getApiModelCreator()
    {
        return $this->apiModelCreator;
    }

    /**
     * @param mixed $apiRoutngCreator
     */
    public function setApiRoutingCreator($apiRoutngCreator)
    {
        $this->apiRoutngCreator = $apiRoutngCreator;

        return $this;
    }

    /**
     * @param mixed $apiSecurityCreator
     * @return $this;
     */
    public function setApiSecurityCreator($apiSecurityCreator)
    {
        $this->apiSecurityCreator = $apiSecurityCreator;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiSecurityCreator()
    {
        return $this->apiSecurityCreator;
    }



    /**
     * @return mixed
     */
    public function getApiRoutingCreator()
    {
        return $this->apiRoutngCreator;
    }

    /**
     * @param mixed $creatorsManager
     */
    public function setCreatorsManager($creatorsManager)
    {
        $this->creatorsManager = $creatorsManager;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatorsManager()
    {
        return $this->creatorsManager;
    }

    /**
     * @param mixed $fileCreator
     */
    public function setFileCreator($fileCreator)
    {
        $this->fileCreator = $fileCreator;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileCreator()
    {
        return $this->fileCreator;
    }

    public function init()
    {
        $this->initTwig();

        $this->setFileCreator($this->getContainer()->get('enneite_swagger.file_creator'));
        $this->setApiModelCreator(new ApiModelCreator($this->getTwigEnv()));
        $this->setApiRoutingCreator(new ApiRoutingCreator($this->getTwigEnv()));
        $this->setApiSecurityCreator(new ApiSecurityCreator($this->getTwigEnv(), $this->getApiRoutingCreator()));
        $this->setApiControllerCreator(new ApiControllerCreator($this->getTwigEnv(), $this->getApiModelCreator(), $this->getApiRoutingCreator()));
        $this->setCreatorsManager(new Manager($this->getContainer(), $this->getFileCreator(), $this->getApiModelCreator(), $this->getApiRoutingCreator(), $this->getApiSecurityCreator(), $this->getApiControllerCreator()));

        return $this;
    }

    protected function initTwig()
    {
        \Twig_Autoloader::register();

        $templatesPath = realpath(__DIR__ . '/../Resources/templates/');
        $loader        = new \Twig_Loader_Filesystem($templatesPath);
        $twig          = new \Twig_Environment($loader, array('autoescape' => false));
        $twig->addExtension(new \Symfony\Bridge\Twig\Extension\ProfilerExtension(new \Twig_Profiler_Profile()));
        $this->setTwigEnv($twig);

        return $this;
    }
}
