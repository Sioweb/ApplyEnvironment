<?php

namespace Sioweb\ApplyEnvironment\Composer;

use Composer\Script\Event;
use Composer\Util\Filesystem;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

class ApplyEnvironment
{

    /**
     * Runs all Composer tasks to initialize a Contao Managed Edition.
     */
    public static function setup(Event $event): void
    {
        static::executeCommand('sioweb:environment', $event);
        $event->getIO()->write('<info>Environment is done!.</info>');
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

        $rootDir = dirname(\Composer\Factory::getComposerFile());
        if (file_exists($rootDir.'/.env')) {
            (new Dotenv())->load($rootDir.'/.env');
            $_environment = @getenv('APPLY_ENVIRONMENT', true);
        }

        $process = new Process(
            sprintf(
                '%s %s%s %s%s --env=%s',
                escapeshellarg($phpPath),
                $event->getComposer()->getConfig()->get('vendor-dir').'/contao/manager-bundle/bin/contao-console',
                $event->getIO()->isDecorated() ? ' --ansi' : '',
                $cmd,
                self::getVerbosityFlag($event),
                getenv('APPLY_ENVIRONMENT') ?: 'prod'
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
