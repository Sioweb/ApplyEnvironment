<?php

namespace Sioweb\ApplyEnvironment\Composer;

use Composer\Package\Dumper\ArrayDumper;
use Sioweb\CCEvent\Composer\Installer\PackageEvent as Event;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class Git
{

    public static function getInitDefinition()
    {
        $InputDefinition = new InputDefinition;
        $InputDefinition->setDefinition([
            new InputOption('repository', 'r', InputOption::VALUE_REQUIRED, 'url to git repo')
        ]);

        return $InputDefinition;
    }

    public static function init(Event $event): void
    {
        $operation = $event->getOperation();
        
        $package = method_exists($event->getOperation(), 'getPackage')
            ? $operation->getPackage()
            : $operation->getInitialPackage();

        $Input = new StringInput(implode(' ', $event->getArguments()));
        $Input->bind(self::getInitDefinition());
        $Arguments = $Input->getOptions();

        static::executeCommand('sioweb:add:git ' . implode(' ', [
            $Arguments['repository'],
            $package->getName(),
            '--target-dir='.$package->getSourceUrl(),
            '--vendor-dir='.$event->getComposer()->getConfig()->get('vendor-dir'),
        ]), $event);
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

        echo "\nOUTPUT GIT COMMAND:\n".sprintf(
            '%s %s%s %s%s --env=%s',
            escapeshellarg($phpPath),
            escapeshellarg($event->getComposer()->getConfig()->get('vendor-dir') . '/bin/contao-console'),
            $event->getIO()->isDecorated() ? ' --ansi' : '',
            $cmd,
            self::getVerbosityFlag($event),
            getenv('SYMFONY_ENV') ?: 'prod'
        )."\n----------------\n";

        $process = new Process(
            sprintf(
                '%s %s%s %s%s --env=%s',
                escapeshellarg($phpPath),
                escapeshellarg($event->getComposer()->getConfig()->get('vendor-dir') . '/bin/contao-console'),
                $event->getIO()->isDecorated() ? ' --ansi' : '',
                $cmd,
                self::getVerbosityFlag($event),
                getenv('SYMFONY_ENV') ?: 'prod'
            )
        );

        // Increase the timeout according to terminal42/background-process (see #54)
        $process->setTimeout(800);

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
