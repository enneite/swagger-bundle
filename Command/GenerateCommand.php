<?php

/**
 * Created by JetBrains PhpStorm.
 * User: etienne.lejariel
 * Date: 19/06/15
 * Time: 16:47
 * To change this template use File | Settings | File Templates.
 */
namespace Enneite\SwaggerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\CS\Config\Config;
use Symfony\CS\Fixer;
use Symfony\CS\FixerFileProcessedEvent;

class GenerateCommand extends ContainerAwareCommand
{
    /**
     * configuration.
     */
    protected function configure()
    {
        $this->setName('swagger:generate')
            ->setDefinition(
                array(
                    new InputOption('no-csfixer', '', InputOption::VALUE_NONE, 'Desable cs fixer on generate files'),
                )
            )
            ->setDescription('generate API from swagger yaml or json file');
    }

    /**
     * Execute the command line :
     * php app/console swagger:generate --file=Resources/example/swagger.json.
     *
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getContainer()->get('enneite_swagger.generator_manager');
        $config = $this->getContainer()->getParameter('swagger');

        foreach ($config as $wsConfig) {
            $manager->loadConfig($wsConfig);

            $output->writeln('');
            $output->writeln('<comment>'.strtoupper($wsConfig['name']).'</comment>');

            $output->writeln('Start creating bundle...');
            $manager->generateBundle($output);
            $output->writeln('Finish creating bundle.');
            $output->writeln('');

            $swaggerConfig = $manager->getSwaggerConfig();

            if (isset($swaggerConfig['definitions'])) {
                $output->writeln('Start creating definitions...');
//                $manager->generateEntities($swaggerConfig['definitions'], $output);
                $output->writeln('Finish creating definitions.');
                $output->writeln('');
            } else {
                $output->writeln('');
                $output->writeln('<error>WARNING: definitions not found</error>');
            }

            if (isset($swaggerConfig['paths'])) {
                $output->writeln('Start creating controllers...');
                $manager->generateControllers($swaggerConfig['paths'], $output);
                $output->writeln('Finish creating controllers.');
                $output->writeln('');
                if ('yaml' == $manager->getRoutingType()) {
                    $output->writeln('Start creating routing yaml file...');
                    $manager->generateYamlRouter($swaggerConfig['paths'], $output);
                    $output->writeln('Finish creating routing yaml file.');
                    $output->writeln('');
                }
            } else {
                $output->writeln('');
                $output->writeln('<error>WARNING: paths not found</error>');
            }

            // @todo : apeler directement la command
            if (!$input->getOption('no-csfixer')) {
                $watcher = new Stopwatch();
                $eventDispatcher = new EventDispatcher();

                $dirs = array(
                    $manager->getOutputPath(),
                );

                $fixer = new fixer();

                $fixer->setStopwatch(new Stopwatch());
                $fixer->registerBuiltInFixers();
                $fixer->registerBuiltInConfigs();

                $config = new config();
                $config->level('symfony');
                $config->fixers($fixer->getFixers());
                foreach ($dirs as $dir) {
                    if (file_exists($dir) && is_dir($dir)) {
                        $config->getFinder()->setDir($dir);
                    }
                }

                $fileProcessedEventListener = function (FixerFileProcessedEvent $event) use ($output) {
                    $output->write($event->getStatusAsString());
                };

                $fixer->setEventDispatcher($eventDispatcher);
                $eventDispatcher->addListener(FixerFileProcessedEvent::NAME, $fileProcessedEventListener);

                $watcher->start('fixFiles');
                $changed = $fixer->fix($config);
                $watcher->stop('fixFiles');

                $fixer->setEventDispatcher(null);
                $eventDispatcher->removeListener(FixerFileProcessedEvent::NAME, $fileProcessedEventListener);
                $output->writeln('');

                $legend = array();
                foreach (FixerFileProcessedEvent::getStatusMap() as $status) {
                    if ($status['symbol'] && $status['description']) {
                        $legend[] = $status['symbol'].'-'.$status['description'];
                    }
                }
                $output->writeln('Legend: '.implode(', ', array_unique($legend)));

                if ($output->isVeryVerbose()) {
                    $i = 1;
                    foreach ($changed as $file => $fixResult) {
                        $output->writeln(sprintf('%4d) %s', $i++, $file));
                    }

                    $output->writeln('');
                    $fixEvent = $watcher->getEvent('fixFiles');
                    $output->writeln(sprintf('Fixed all files in %.3f seconds, %.3f MB memory used', $fixEvent->getDuration() / 1000, $fixEvent->getMemory() / 1024 / 1024));
                }
            }
        }
    }

    /**
     * @return mixed
     */
    protected function getCreatorsManager()
    {
        $serviceManager = $this->getContainer()->get('enneite_swagger.service_manager');
        $serviceManager->setContainer($this->getContainer())->init();

        $manager = $serviceManager->getCreatorsManager();
        $manager->init();

        return $manager;
    }
}
