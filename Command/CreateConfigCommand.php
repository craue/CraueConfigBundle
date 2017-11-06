<?php

namespace Craue\ConfigBundle\Command;

use Craue\ConfigBundle\Entity\Setting;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateConfigCommand
 * @package Craue\ConfigBundle\Command
 * @author Benjamin Vison <bvisonl@gmail.com>
 *
 * Example of use:
 *
 * php app/console craude:config:create defaults.company.rate company 0.5 (or bin/console if sf2 3.0+)
 *
 */
Class CreateConfigCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('craude:config:create')
            ->setDescription('Creates a new setting in the database.')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the config')
            ->addArgument('section', InputArgument::REQUIRED, 'Section of the config')
            ->addArgument('value', InputArgument::REQUIRED, 'Value of the config')
            ->setHelp(<<<'EOT'
The <info>craude:config:create</info> command creates a new craude configuration  
EOT
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument("name");
        $section = $input->getArgument("section");
        $value = $input->getArgument("value");

        $setting = new Setting();
        $setting->setName($name);
        $setting->setSection($section);
        $setting->setValue($value);

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($setting);

        try {
            $em->flush();
            $output->writeln('<success>The config has been successfully created.</success>');
        } catch (\Exception $ex) {
            $output->writeln('<error>An error occurred creating the configuration.</error>');
            $output->writeln('<error>Message: '.$ex->getMessage().'</error>');
        }
    }
}