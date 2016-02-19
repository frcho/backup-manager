<?php

namespace Frcho\Bundle\BackupManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use BackupManager\Databases\DatabaseProvider;
use BackupManager\Procedures\RestoreProcedure;
use BackupManager\Filesystems\FilesystemProvider;
use BackupManager\Filesystems\Destination;
use Symfony\Component\Console\Style\OutputStyle;

class restoreBackupCommand extends \Symfony\Component\Console\Command\Command {
//    /**
//     * The input interface implementation.
//     *
//     * @var \Symfony\Component\Console\Input\InputInterface
//     */
//    protected $input;
//
//    /**
//     * The output interface implementation.
//     *
//     * @var Symfony\Component\Console\Style\OutputStyle
//     */
//    protected $output;

    /**
     * The required arguments.
     *
     * @var array
     */
    private $required = ['source', 'sourcePath', 'database', 'compression'];

    /**
     * The missing arguments.
     *
     * @var array
     */
    private $missingArguments;

    /**
     * @var \BackupManager\Filesystems\FilesystemProvider
     */
    private $filesystems;

    /**
     * The default verbosity of output commands.
     *
     * @var int
     */
    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;
    


//    public function __construct($filesystems)
//    {
//        $this->filesystems = $filesystems;
//
////        parent::__construct();
//    }


    protected function configure() {
        $this
                ->setName('backup:manager:restore')
                ->setDescription('This command restore a backup in the database selected')
                ->setDefinition(
//                    new InputOption('source', null, InputOption::VALUE_OPTIONAL, 'Source configuration name', null),
                        $this->getOptions()
                )
//               

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {


        if ($this->isMissingArguments($input)) {
            $this->displayMissingArguments($output);
            $this->promptForMissingArgumentValues();
            $this->validateArguments();
        }

        $this->info($output, 'Downloading and importing backup...');
        $this->restore->run(
                $this->option('source'), $this->option('sourcePath'), $this->option('database'), $this->option('compression')
        );
        $this->line($output, '');
        $root = $this->filesystems->getConfig($this->option('source'), 'root');
        $this->info($output, sprintf('Successfully restored <comment>%s</comment> from <comment>%s</comment> to database <comment>%s</comment>.', $root . $this->option('sourcePath'), $this->option('source'), $this->option('database')
        ));


//        $container = $this->getContainer();
//        $container->get('backup_manager')->makeRestore()->run('local', 'backup/test.sql', 'production', 'null');
//        \Symfony\Component\VarDumper\VarDumper::dump($t6est);
//        die();
    }

    /**
     * @return bool
     */
    private function isMissingArguments($input) {

        foreach ($this->required as $argument) {
            if (!$this->option($input, $argument)) {
                $this->missingArguments[] = $argument;
            }
        }
        return (bool) $this->missingArguments;
    }

    /**
     * @return void
     */
    private function displayMissingArguments(OutputInterface $output) {
        $formatted = implode(', ', $this->missingArguments);
        $this->info($output, "These arguments haven't been filled yet: <comment>{$formatted}</comment>");
        $this->info($output, 'The following questions will fill these in for you.');
        $this->line($output, '');
    }

    /**
     * @return void
     */
    private function promptForMissingArgumentValues() {
        foreach ($this->missingArguments as $argument) {
            if ($argument == 'source') {
                $this->askSource();
            } elseif ($argument == 'sourcePath') {
                $this->askSourcePath();
            } elseif ($argument == 'database') {
                $this->askDatabase();
            } elseif ($argument == 'compression') {
                $this->askCompression();
            }
            $this->line('');
        }
    }

    /**
     *
     */
    private function askSource() {
        $providers = $this->filesystems->getAvailableProviders();
        $formatted = implode(', ', $providers);
        $this->info("Available storage services: <comment>{$formatted}</comment>");
        $source = $this->autocomplete("From which storage service do you want to choose?", $providers);
        $this->input->setOption('source', $source);
    }

    /**
     *
     */
    private function askSourcePath() {
        // ask path
        $root = $this->filesystems->getConfig($this->option('source'), 'root');
        $path = $this->ask("From which path do you want to select?<comment> {$root}</comment>");
        $this->line('');
        // ask file
        $filesystem = $this->filesystems->get($this->option('source'));
        $contents = $filesystem->listContents($path);
        $files = [];
        foreach ($contents as $file) {
            if ($file['type'] == 'dir')
                continue;
            $files[] = $file['basename'];
        }
        if (empty($files)) {
            $this->info('No backups were found at this path.');
            return;
        }
        $rows = [];
        foreach ($contents as $file) {
            if ($file['type'] == 'dir')
                continue;
            $rows[] = [
                $file['basename'],
                $file['extension'],
                $this->formatBytes($file['size']),
                date('D j Y  H:i:s', $file['timestamp'])
            ];
        }
        $this->info('Available database dumps:');
        $this->table(['Name', 'Extension', 'Size', 'Created'], $rows);
        $filename = $this->autocomplete("Which database dump do you want to restore?", $files);
        $this->input->setOption('sourcePath', "{$path}/{$filename}");
    }

    /**
     *
     */
    private function askDatabase() {
        $providers = $this->databases->getAvailableProviders();
        $formatted = implode(', ', $providers);
        $this->info("Available database connections: <comment>{$formatted}</comment>");
        $database = $this->autocomplete("From which database connection you want to restore?", $providers);
        $this->input->setOption('database', $database);
    }

    /**
     *
     */
    private function askCompression() {
        $types = ['null', 'gzip'];
        $formatted = implode(', ', $types);
        $this->info("Available compression types: <comment>{$formatted}</comment>");
        $compression = $this->autocomplete('Which compression type you want to use?', $types);
        $this->input->setOption('compression', $compression);
    }

    /**
     * @return void
     */
    private function validateArguments() {
        $root = $this->filesystems->getConfig($this->option('source'), 'root');
        $this->info('Just to be sure...');
        $this->info(sprintf('Do you want to restore the backup <comment>%s</comment> from <comment>%s</comment> to database <comment>%s</comment> and decompress it from <comment>%s</comment>?', $root . $this->option('sourcePath'), $this->option('source'), $this->option('database'), $this->option('compression')
        ));
        $this->line('');
        $confirmation = $this->confirm('Are these correct? [Y/n]');
        if (!$confirmation) {
            $this->reaskArguments();
        }
    }

    /**
     * Get the console command options.
     *
     * @return void
     */
    private function reaskArguments() {
        $this->line('');
        $this->info('Answers have been reset and re-asking questions.');
        $this->line('');
        $this->promptForMissingArgumentValues();
    }

    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @param  string  $style
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function line(OutputInterface $output, $string, $style = null, $verbosity = null) {
        $styled = $style ? "<$style>$string</$style>" : $string;
        $output->writeln($styled, $this->parseVerbosity($verbosity));
    }

    /**
     * Get the value of a command argument.
     *
     * @param  string  $key
     * @return string|array
     */
    public function argument($key = null) {
        if (is_null($key)) {
            return $this->input->getArguments();
        }
        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param  string  $key
     * @return string|array
     */
    public function option($input, $key = null) {
        if (is_null($key)) {
            return $input->getOptions();
        }
        return $input->getOption($key);
    }

    /**
     * Get the verbosity level in terms of Symfony's OutputInterface level.
     *
     * @param  string|int  $level
     * @return int
     */
    protected function parseVerbosity($level = null) {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (!is_int($level)) {
            $level = $this->verbosity;
        }
        return $level;
    }

    /**
     * Set the verbosity level.
     *
     * @param string|int $level
     * @return void
     */
    protected function setVerbosity($level) {
        $this->verbosity = $this->parseVerbosity($level);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            new InputOption('source', null, InputOption::VALUE_OPTIONAL, 'Source configuration name', null),
            new InputOption('sourcePath', null, InputOption::VALUE_OPTIONAL, 'Source path from service', null),
            new InputOption('database', null, InputOption::VALUE_OPTIONAL, 'Database configuration name', null),
            new InputOption('compression', null, InputOption::VALUE_OPTIONAL, 'Compression type', null),
                ]
        ;
    }

    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @param  null|int|string  $verbosity
     * @return void
     */
    public function info(OutputInterface $output, $string, $verbosity = null) {
        $this->line($output, $string, 'info', $verbosity);
    }

    /**
     * @param $bytes
     * @param int $precision
     * @return string
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

}
