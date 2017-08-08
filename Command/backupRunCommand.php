<?php

namespace Frcho\Bundle\BackupManagerBundle\Command;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Config\FileLocator;
use BackupManager\Filesystems\Destination;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

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
        $datetime = $date->format('Y-m-d_H:i:s');
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
            if (function_exists("gzopen") || function_exists("gzopen64")) {
                $compression = 'gzip';
            } else {
                $compression = 'null';
            }
        }

        $container->get('backup_manager')->makeBackup()->run($database, array(
            new Destination($destination, 'backups/' . $destinationFileName . '.sql'),
                ), $compression);

        $this->uploadS3($database, $destinationFileName, $compression);

        $this->removeLocalFiles();
    }

    protected function uploadS3($database, $destinationFileName, $compression) {
        $container = $this->getContainer();

        $yaml = new Parser();
        $content = $yaml->parse(file_get_contents($container->get('kernel')->getRootDir() . '/config/config.yml'));

        if ($content["frcho_backup_manager"] && isset($content["frcho_backup_manager"]["storage"]["s3"])) {
            $params = $content["frcho_backup_manager"]["storage"]["s3"];
            $i = 0;
            $stdS3 = true;

            foreach ($params as $contentFile) {
                if (!empty($contentFile)) {

                    $paramKey = str_replace("%", "", $contentFile);

                    if (in_array($i, [1, 2, 3, 4]) && empty($container->getParameter($paramKey))) {
                        $stdS3 = FALSE;
                        break;
                    }

                    $i++;
                }
            }
            if ($stdS3) {
                $container->get('backup_manager')->makeBackup()->run($database, array(
                    new Destination('s3', 'backups/' . $destinationFileName . '.sql')
                        ), $compression);
            }
        }
    }

    protected function removeLocalFiles() {
        $container = $this->getContainer();

        $path = $container->getParameter('kernel.root_dir') . '/data/backups/';

        $handle = opendir($path);
        if ($handle !== false) {

            while (false !== ($file = readdir($handle))) {
                $filelastmodified = filemtime($path . $file);
                //24 hours in a day * 3600 seconds per hour
                if ((time() - $filelastmodified) > 20 * 24 * 3600) {
                    unlink($path . $file);
                }
            }

            closedir($handle);
        }
    }

}
