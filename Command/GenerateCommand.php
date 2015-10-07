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
use Symfony\Component\Console\Output\OutputInterface;
use Enneite\SwaggerBundle\DependencyInjection\ServiceManager;

class GenerateCommand extends ContainerAwareCommand
{
    /**
     * configuration.
     */
    protected function configure()
    {
        $this->setName('swagger:generate')
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
        $style = new OutputFormatterStyle('red', 'yellow', array('bold', 'blink'));
        $output->getFormatter()->setStyle('fire', $style);

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
