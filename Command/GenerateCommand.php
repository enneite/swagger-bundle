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
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
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
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
        $output->getFormatter()->setStyle('fire', $style);
        $verbosity = $output->getVerbosity();

        $manager = $this->getCreatorsManager();

        $conf = $manager->getConfig();

        if (isset($conf['definitions'])) {
            $output->writeln('Start creating definitions...');
            $manager->createDefinitions($conf['definitions'], $output);
            $output->writeln('Finish creating definitions.');
        } else {
            $output->writeln('');
            $output->writeln('WARNING: definitions not found');
        }

        if (isset($conf['paths'])) {
            $output->writeln('Start creating controllers...');
            $buildRoutingAnnotations = ('annotation' === $manager->getRoutingType());
            $manager->createControllers($conf['paths'], $buildRoutingAnnotations, $output);
            $output->writeln('Finish creating controllers.');

            if ('yaml' == $manager->getRoutingType()) {
                $output->writeln('Start creating routing yaml file...');

                $manager->createRoutingYamlFile($conf['paths'], $output);
                $output->writeln('Finish creating routing yaml file.');
            }
        } else {
            $output->writeln('');
            $output->writeln('WARNING: paths not found');
        }

        if (!$input->getOption('no-csfixer')) {
            $watcher = new Stopwatch();
            $eventDispatcher = new EventDispatcher();

            $dirs = array(
                $manager->getOutputPath() . 'Controller/Api/',
                $manager->getOutputPath() . 'Api/Model',
            );

            $fixer = new fixer();

            $fixer->setStopwatch(new Stopwatch());
            $fixer->registerBuiltInFixers();
            $fixer->registerBuiltInConfigs();

            $config = new config();
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
                    $legend[] = $status['symbol'] . '-' . $status['description'];
                }
            }
            $output->writeln('Legend: ' . implode(', ', array_unique($legend)));

            if (OutputInterface::VERBOSITY_VERBOSE <= $verbosity) {
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
