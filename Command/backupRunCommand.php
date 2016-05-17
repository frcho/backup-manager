<?php

namespace Frcho\Bundle\BackupManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use BackupManager\Filesystems\Destination;

class backupRunCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('backup:manager:backup')
                ->setDescription('Runs backup database config')
                ->addArgument('database', InputArgument::OPTIONAL, 'Database configuration name', null)
                ->addArgument('destination', InputArgument::OPTIONAL, 'Destination configuration name', null)
                ->addArgument('destinationFileName', InputArgument::OPTIONAL, 'File destination path', null)
                ->addArgument('compression', InputArgument::OPTIONAL, 'Compression type: gzip, null', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {


        $container = $this->getContainer();

        $date = new \DateTime();
        $datetime = $date->format('Y-m-d H:i:s');
        $fileName = $container->getParameter('database_name') . '-' . $datetime;

        $database = $input->getArgument('database');

        if (!empty($database)) {
            $database = $input->getArgument('database');
        } else {
            $database = 'development';
        }

        $destination = $input->getArgument('destination');
        if (!empty($destination)) {
            $destination = $input->getArgument('destination');
        } else {
            $destination = 'local';
        }

        $destinationFileName = $input->getArgument('destinationFileName');
        if (!empty($destinationFileName)) {
            $destinationFileName = $input->getArgument('destinationFileName');
        } else {
            $destinationFileName = $fileName;
        }

        $compression = $input->getArgument('compression');
        if (!empty($compression)) {
            $compression = $input->getArgument('compression');
        } else {
            $compression = 'null';
        }

        $container->get('backup_manager')->makeBackup()->run($database, array(
            new Destination($destination, 'backups/' . $destinationFileName . '.sql'),
//            new Destination('s3', 'backups/test.sql')
                ), $compression);
    }

}
