<?php

/**
 * Created by JetBrains PhpStorm.
 * User: bernard.bernard
 * Date: 04/11/15
 * Time: 00:30
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Manager;

use Enneite\SwaggerBundle\Creator\BundleCreator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class CreatorManager
{
    private $container;
    private $filesystem;

    private $mainNamespace;
    private $outputPath;
    private $routingType;
    private $swaggerFilePath;

    const BUNDLENAME = 'ApiBundle';

    public function __construct(ContainerInterface $container, Filesystem $filesystem)
    {
        $this->container = $container;
        $this->filesystem = $filesystem;

        $this->loadConfig($this->container->getParameter('swagger'));
    }

    /**
     * @return mixed
     */
    public function getMainNamespace()
    {
        return $this->mainNamespace;
    }

    /**
     * @return mixed
     */
    public function getOutputPath()
    {
        return $this->outputPath;
    }

    /**
     * @return mixed
     */
    public function getRoutingType()
    {
        return $this->routingType;
    }

    /**
     * @return mixed
     */
    public function getSwaggerFilePath()
    {
        return $this->swaggerFilePath;
    }

    private function loadConfig($swaggerConf)
    {
        //@todo : faire un arbre de config
        if (null == $swaggerConf) {
            throw new \Exception(' swagger conf not found ');
        }

        if (!isset($swaggerConf['destination_namespace'])) {
            throw new \Exception(' swagger destination namespace not found');
        }
        $this->mainNamespace = $swaggerConf['destination_namespace'];

        $outPath = realpath($this->container->get('kernel')->getRootDir() . '/../src/' . str_replace('\\', '/', $swaggerConf['destination_namespace'])) . '/' . self::BUNDLENAME;
        if (null == $outPath) {
            throw new \Exception(" output path : $outPath not found");
        }
        $this->outputPath = $outPath . '/';

        $type = (isset($swaggerConf['routing_type'])) ? $swaggerConf['routing_type'] : 'yaml';
        $this->routingType = $type;

        $swaggerFilePath = array_key_exists('config_file', $swaggerConf) ? $swaggerConf['config_file'] : null;
        $this->swaggerFilePath = $swaggerFilePath;

        return $this;
    }

    public function getConfig()
    {
        $swaggerFilePath = $this->swaggerFilePath;

        // @todo : refactoriser pour eviter la repetition
        if ($swaggerFilePath != null) {
            if (file_exists($swaggerFilePath)) {
                if (preg_match("/\.(yaml|yml)$/i", $swaggerFilePath)) {
                    $yaml = new Parser();
                    return $yaml->parse(file_get_contents($swaggerFilePath));
                } else {
                    return json_decode(file_get_contents($swaggerFilePath), true);
                }
            }
        } else {
            $swaggerFilePath = $this->container->get('kernel')->getRootDir() . '/config/swagger';
            if (file_exists($swaggerFilePath . '.json')) {
                return json_decode(file_get_contents($swaggerFilePath . '.json'), true);
            }
            if (file_exists($swaggerFilePath . '.yml')) {
                $yaml = new Parser();
                return $yaml->parse(file_get_contents($swaggerFilePath . '.yml'));
            }
        }

        throw new \Exception('Swagger file not found!');
    }

    public function createBundle()
    {
        $creator = new BundleCreator($this->container, $this->filesystem);
        $creator->createBundle($this->outputPath, $this->mainNamespace, self::BUNDLENAME);

    }


}
