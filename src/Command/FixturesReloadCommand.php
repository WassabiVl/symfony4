<?php
/**
 * Created by PhpStorm.
 * User: al-atrash
 * Date: 17/01/2018
 * Time: 17:26
 *
 * run> php bin/console app>fixtureReload
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesReloadCommand extends Command
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function configure():void
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:fixturesReload')

            // the short description shown while running "php bin/console list"
            ->setDescription('Drop/Create Database and load Fixtures ....')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to load dummy data by recreating database and loading fixtures...');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|string
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();
        $application->setAutoExit(false);

        $output->writeln([
            '===================================================',
            '*********        Dropping DataBase        *********',
            '===================================================',
            '',
        ]);

        $options = array('command' => 'doctrine:database:drop', '--force' => true);
        try {
            $application->run(new ArrayInput($options));
        } catch (\Exception $e) {
            return " can't drop database: " .$e;
        }

        $output->writeln([
            '===================================================',
            '*********        Creating DataBase        *********',
            '===================================================',
            '',
        ]);

        $options = array('command' => 'doctrine:database:create', '--if-not-exists' => true);
        try {

            $application->run(new ArrayInput($options));
        } catch (\Exception $e) {
            return " can't create database: " .$e;
        }

        $output->writeln([
            '===================================================',
            '*********         Updating Schema         *********',
            '===================================================',
            '',
        ]);

        //Create de Schema
        $options = array('command' => 'doctrine:schema:update', '--force' => true);

        try {
            $application->run(new ArrayInput($options));
        } catch (\Exception $e) {
            return " can't update database: " .$e;
        }

        $output->writeln([
            '===================================================',
            '*********          Load Fixtures          *********',
            '===================================================',
            '',
        ]);

        //Loading Fixtures
        $options = array('command' => 'hautelook:fixtures:load', '--no-interaction' => true, '--append');
        try {

            $application->run(new ArrayInput($options));
        } catch (\Exception $e) {
            return " can't create fixture: " .$e;
        }
        return print 'Success';

    }
}