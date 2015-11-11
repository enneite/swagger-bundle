<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 29/06/15
 * Time: 17:24
 * To change this template use File | Settings | File Templates.
 */

namespace Enneite\SwaggerBundle\Creator;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class Manager implements ContainerAwareInterface
{

    /**
     * path to generate PHP class.
     *
     * @var string
     */
    protected $outputPath = '';

    /**
     * @var string
     */
    protected $mainNamespace;

    /**
     * @var
     */
    protected $bundleName;

    /**
     * namespace for the models.
     *
     * @var string
     */
    protected $modelsNamespace;

    /**
     * namespace for the controllers.
     *
     * @var string
     */
    protected $controllersNamespace;

    /**
     * firectory / file creation service.
     *
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
    protected $apiRoutngCreator;

    /**
     * @var
     */
    protected $apiControllerCreator;

    /**
     * routing type for the api (annotation or yaml).
     *
     * @var string
     */
    protected $routingType;

    /**
     * @var
     */
    protected $container;

    public function __construct($container, $fileCreator, $apiModelCreator, $apiRoutingCreator, $apiSecurityCreator, $apiControllerCreator)
    {
        $this->setContainer($container);
        $this->fileCreator = $fileCreator;
        $this->apiModelCreator = $apiModelCreator;
        $this->apiRoutingCreator = $apiRoutingCreator;
        $this->apiSecurityCreator = $apiSecurityCreator;
        $this->apiControllerCreator = $apiControllerCreator;

        return $this;
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

    /**
     * get container.
     *
     * @return mixed
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * outputPath setter.
     *
     * @param string $outputPath
     */
    public function setOutputPath($outputPath)
    {
        $this->outputPath = $outputPath;

        return $this;
    }

    /**
     * outputPath getter.
     */
    public function getOutputPath()
    {
        return $this->outputPath;
    }

    /**
     * mainSace setter.
     *
     * @param $mainNamespace
     */
    public function setMainNamespace($mainNamespace)
    {
        $this->mainNamespace = $mainNamespace;

        return $this;
    }

    /**
     * @param $controllersNamespace
     *
     * @return $this
     */
    public function setControllersNamespace($controllersNamespace)
    {
        $this->controllersNamespace = $controllersNamespace;

        return $this;
    }

    /**
     * bundelname setter.
     *
     * @param $bundleName
     *
     * @return $this
     */
    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;

        return $this;
    }

    /**
     * routingType setter.
     *
     * @param $routingType
     */
    public function setRoutingType($routingType)
    {
        $this->routingType = $routingType;
    }

    /**
     * routing type getter.
     *
     * @return string
     */
    public function getRoutingType()
    {
        return $this->routingType;
    }

    /**
     * @throws Exception
     *
     * @return $this
     */
    public function init()
    {
        $swaggerConf = $this->getContainer()->getParameter('swagger');

        if (null == $swaggerConf) {
            throw new \Exception(' swagger conf not found ');
        }

        if (!isset($swaggerConf['destination_namespace'])) {
            throw new \Exception(' swagger destination namespace not found');
        }
        $this->setMainNamespace($swaggerConf['destination_namespace']);

        if (!isset($swaggerConf['destination_bundle'])) {
            throw new \Exception(' swagger destination bundle not found');
        }
        $this->setBundleName($swaggerConf['destination_bundle']);

        $outPath = $this->fileCreator->realpath($this->getContainer()->get('kernel')->getRootDir() . '/../src/' . str_replace('\\', '/', $swaggerConf['destination_namespace']));

        if (null == $outPath) {
            throw new \Exception(" output path : $outPath not found");
        }
        $this->setOutputPath($outPath . '/');

        $this->modelsNamespace = $this->mainNamespace . '\Api\Model';
        $this->controllersNamespace = $this->mainNamespace . '\Controller\Api';

        $type = (isset($swaggerConf['routing'])) ? $swaggerConf['routing'] : 'yaml';
        $this->setRoutingType($type);

        return $this;
    }

    /**
     * get swagger configuration file.
     *
     * @param $input
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function getConfig()
    {
        $swaggerConfig = $this->getContainer()->getParameter('swagger');
        //var_dump($swaggerConfig);

        $swaggerConf = array(
            'config_file' => array_key_exists('config_file', $swaggerConfig) ? $swaggerConfig['config_file'] : null,
            'destination' => array(
                'bundle' => $swaggerConfig['destination_bundle'],
                'namespace' => $swaggerConfig['destination_namespace']
            )
        );

        if (isset($swaggerConf['config_file'])) {
            $path = $swaggerConf['config_file'];

            if ($this->fileCreator->exists($path)) {
                if (preg_match("/\.(yaml|yml)$/i", $path)) {
                    $yaml = new Parser();

                    return $yaml->parse($this->fileCreator->get($path));
                } else {
                    return json_decode($this->fileCreator->get($path), true);
                }
            }
        } else {
            $path = $this->getContainer()->get('kernel')->getRootDir() . '/config/swagger';
            if ($this->fileCreator->exists($path . '.json')) {
                return json_decode($this->fileCreator->get($path . '.json'), true);
            }
            if ($this->fileCreator->exists($path . '.yml')) {
                $yaml = new Parser();

                return $yaml->parse($this->fileCreator->get($path . 'yml'));
            }
        }

        throw new \Exception('Swagger file not found!');
    }

    /**
     * create namespace.
     *
     * @param $prefix
     * @param $names
     *
     * @return string
     */
    public function createNamespace($prefix, $names)
    {
        $namespace = $prefix;
        if (count($names) > 0) {
            $namespace .= '\\' . implode('\\', array_map(function ($name) {
                    return ucfirst($name);
                }, $names));
        }

        return $namespace;
    }

    /**
     * create api routing file.
     *
     * @param $paths
     */
    public function createRoutingYamlFile($paths, OutputInterface $output)
    {
        $outputStr = '';

        foreach ($paths as $pathName => $path) {
            $className = $this->apiControllerCreator->getClassName($pathName);
            $className = str_replace('Controller', '', $className);

            foreach ($path as $verb => $objects) {
                $conf = $this->apiRoutingCreator->createYamlConf($verb, $objects, $pathName, $this->bundleName, $className);
                $outputStr .= $conf;
            }
        }

        $path = $this->outputPath;
        $this->fileCreator->createDirectory($path . 'Resources');
        $path = $path . 'Resources/';
        $this->fileCreator->createDirectory($path . 'config');
        $path = $path . 'config/';

        $this->fileCreator->createFile($path . 'api_routing.yml', $outputStr);
        $output->writeln('<info>' . $path . 'api_routing.yml created</info>');
    }

    /**
     *
     */
    public function createSecurityYamlFile($basePath, $security, $paths, OutputInterface $output)
    {

        $yaml = $this->apiSecurityCreator->createSecurityYaml($basePath, $security, $paths,$output);


        $path = $this->outputPath;
        $this->fileCreator->createDirectory($path . 'Resources');
        $path = $path . 'Resources/';
        $this->fileCreator->createDirectory($path . 'config');
        $path = $path . 'config/';

        $this->fileCreator->createFile($path . 'api_security.yml', $yaml);
        $output->writeln('<info>' . $path . 'api_security.yml created</info>');

    }

    /**
     * create Api Resources models from swagger defintions:.
     *
     *
     * @param $definitions
     * @param $output
     *
     * @throws \Exception
     */
    public function createDefinitions($definitions, $output)
    {
        $nbResourcesCreated = 0;
        $nbFactoriesCreated = 0;

        foreach ($definitions as $modelName => $model) {
            // explode model name for the namespace construction
            // for example :
            //model name = "Offer/Program"
            $names = explode('/', $modelName);

            // get file / class name
            $className = ucfirst(array_pop($names));

            // create the path for the resource model class: (respect PSR4 conventions)
            $path = $this->outputPath;

            $this->fileCreator->createDirectory($path . 'Api');
            $path = $path . 'Api/';

            $this->fileCreator->createDirectory($path . 'Model');
            $path = $path . 'Model/';

            if (count($names) > 0) {
                foreach ($names as $d) {
                    $this->fileCreator->createDirectory($path . ucfirst($d));
                    $path = $path . ucfirst($d) . '/';
                }
            }

            $namespace = $this->createnamespace($this->modelsNamespace, $names);

            // generate PHP code
            $php = $this->apiModelCreator->createModel($className, $namespace, $this->modelsNamespace, $model);
            // save PHP code
            $this->fileCreator->createFile($path . $className . '.php', $php);
            $output->writeln('<info>' . $path . $className . '.php generated</info>');
            ++$nbResourcesCreated;


        }

        $output->writeln("RESUME : $nbResourcesCreated resources generated  $nbFactoriesCreated factories generated");
    }

    /**
     * generate controllers.
     *
     * @param $paths
     * @param $buildRoutingAnnotations
     * @param $output
     */
    public function createControllers($paths, $buildRoutingAnnotations, $output)
    {
        $nbNewControllers = 0;
        $nbNewActions = 0;

        foreach ($paths as $pathName => $pathArray) {
            $path = $this->outputPath;
            $this->fileCreator->createDirectory($path . 'Controller');
            $path = $path . 'Controller/';

            $this->fileCreator->createDirectory($path . 'Api');
            $path = $path . 'Api/';

            $className = $this->apiControllerCreator->getClassName($pathName);

            // for new paths : create the associated controller
            if (!$this->fileCreator->exists($path . $className . '.php')) {
                $php = $this->apiControllerCreator->createController($className, $this->controllersNamespace, $this->modelsNamespace, $pathArray, $pathName, $buildRoutingAnnotations);
                $this->fileCreator->createFile($path . $className . '.php', $php);
                ++$nbNewControllers;
                $output->writeln('<info>' . $this->controllersNamespace . '\\' . $className . ' created, path : ' . $path . $className . '.php</info>');
            } else { // for existing paths :
                $reflexion = new \ReflectionClass($this->controllersNamespace . '\\' . $className);

                $actions = '';
                foreach ($pathArray as $verb => $objects) {
                    if (!$reflexion->hasMethod($verb . 'Action')) { // create new Action for new verb :
                        $actions .= $this->apiControllerCreator->createAction($verb, $objects, $pathName, $buildRoutingAnnotations) . "\n";
                        ++$nbNewActions;
                    }
                }

                if ($actions != '') {
                    $php = $this->fileCreator->get($reflexion->getFileName());
                    $end = strrpos($php, '}');
                    $newPhp = substr($php, 0, $end) . "\n" . $actions . "\n" . substr($php, $end);
                    $this->fileCreator->createFile($reflexion->getFileName(), $newPhp);
                    $output->writeln('<info>actions created for ' . $this->controllersNamespace . '\\' . $className . '</info>');
                }
            }
        }
        $output->writeln("RESUME : $nbNewControllers controllers generated, $nbNewActions action added to existing controllers");
    }
}
