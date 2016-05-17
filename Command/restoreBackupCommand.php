<?php

namespace Frcho\Bundle\BackupManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;




class restoreBackupCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
                ->setName('backup:manager:restore')
                ->setDescription('This command restore a backup in the database selected')        

        ;
    }
//
//    protected function execute(InputInterface $input, OutputInterface $output) {
//        $container = $this->getContainer();
//        $container->get('backup_manager')->makeRestore()->run('local', 'backup/test.sql', 'production', 'null');
//    }

}
