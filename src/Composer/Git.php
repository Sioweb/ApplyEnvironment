<?php

namespace Sioweb\ApplyEnvironment\Composer;

use Composer\Installer\PackageEvent as Event;
use Composer\Util\Filesystem;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

use Composer\Package\Dumper\ArrayDumper;

class Git {

    public static function getDefinition() {
        $InputDefinition = new InputDefinition;
        $InputDefinition->setDefinition([
            new InputOption('repository', 'r', InputOption::VALUE_REQUIRED, 'url to git repo'),
            new InputOption('xyz', 'x', InputOption::VALUE_OPTIONAL, 'url to git repo'),
        ]);

        return $InputDefinition;
    }

    public static function init($event): void
    {
        $Input = new StringInput(implode(' ', $event->getArguments()));
        $Input->bind(self::getDefinition());
        echo "\nArguments: ".print_r($Input->getOptions(), 1);
        echo "\nGIT Init: " . $event->getName() . "\n";
        
        $operation = $event->getOperation();

        $package = method_exists($operation, 'getPackage')
        ? $operation->getPackage()
        : $operation->getInitialPackage();

        $Dumper = new ArrayDumper;
        $EventDispatcher = $event->getComposer()->getEventDispatcher();

        echo "\t\t- root: ".$event->getComposer()->getPackage()."\n";
        echo "\t\t- getTargetDir: ".$package->getTargetDir()."\n";
        echo "\t\t- getSourceType: ".$package->getSourceType()."\n";
        echo "\t\t- getSourceUrl: ".$package->getSourceUrl()."\n";
        echo "\t\t- getVersion: ".$package->getVersion()."\n";
        echo "\t\t- getUrls: ".print_r($package->getSourceUrls(),1)."\n";
        echo "\t\t- getVendorPath: ".$event->getComposer()->getConfig()->get('vendor-dir')."\n";
        echo "\t\t- ArrayDump: ".print_r($Dumper->dump($package),1)."\n";
    }

    private static function getWebDir(Event $event): string
    {
        $extra = $event->getComposer()->getPackage()->getExtra();

        return $extra['symfony-web-dir'] ?? 'web';
    }

    /**
     * @throws \RuntimeException
     */
    private static function executeCommand(string $cmd, Event $event): void
    {
        $phpFinder = new PhpExecutableFinder();

        if (false === ($phpPath = $phpFinder->find())) {
            throw new \RuntimeException('The php executable could not be found.');
        }

        $process = new Process(
            sprintf(
                '%s %s%s %s%s --env=%s',
                escapeshellarg($phpPath),
                escapeshellarg($event->getComposer()->getConfig()->get('vendor-dir').'/bin/contao-console'),
                $event->getIO()->isDecorated() ? ' --ansi' : '',
                $cmd,
                self::getVerbosityFlag($event),
                getenv('SYMFONY_ENV') ?: 'prod'
            )
        );

        // Increase the timeout according to terminal42/background-process (see #54)
        $process->setTimeout(500);

        $process->run(
            function (string $type, string $buffer) use ($event): void {
                $event->getIO()->write($buffer, false);
            }
        );

        if (!$process->isSuccessful()) {
            throw new \RuntimeException(
                sprintf('An error occurred while executing the "%s" command: %s', $cmd, $process->getErrorOutput())
            );
        }
    }

    private static function getVerbosityFlag(Event $event): string
    {
        $io = $event->getIO();

        switch (true) {
            case $io->isDebug():
                return ' -vvv';

            case $io->isVeryVerbose():
                return ' -vv';

            case $io->isVerbose():
                return ' -v';

            default:
                return '';
        }
    }
}
