<?php

namespace Frcho\Bundle\BackupManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use BackupManager\Filesystems\Destination;

class backupRunCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('backup:manager:backup')
                ->setDescription('Runs backup database config')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {


        $container = $this->getContainer();
        $container->get('backup_manager')->makeBackup()->run('development', array(
            new Destination('local', 'backup/test.sql'),
//            new Destination('s3', 'backups/test.sql')
            ), 'null');
//        \Symfony\Component\VarDumper\VarDumper::dump($t6est);
//        die();
    }

}
