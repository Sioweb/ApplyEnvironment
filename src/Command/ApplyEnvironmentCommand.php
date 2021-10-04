<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Sioweb\ApplyEnvironment\Command;

use Contao\System;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Dotenv\Dotenv;
use Composer\Script\Event;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
/**
 * Symlinks the public resources into the web directory.
 */
class ApplyEnvironmentCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var array
     */
    private $rows = [];

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var string
     */
    private $webDir;

    /**
     * @var int
     */
    private $statusCode = 0;

    /**
     * Object instance (Singleton)
     * @var ApplyEnvironment
     */
    protected static $objInstance;


    /**
     * Instantiate the object (Factory)
     *
     * @return Files The files object
     */
    public static function getInstance()
    {
        if (self::$objInstance === null) {
            self::$objInstance = new static();
        }

        return self::$objInstance;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('sioweb:environment')
            // ->setDefinition([
            //     new InputArgument('repository', InputArgument::REQUIRED, 'The url to the git repository'),
            //     new InputArgument('package', InputArgument::REQUIRED, 'The package name'),
            //     new InputOption('target-dir', 'td', InputOption::VALUE_OPTIONAL, 'The composer target dir'),
            //     new InputOption('vendor-dir', 'vd', InputOption::VALUE_OPTIONAL, 'The composer vendor dir'),
            // ])
            ->setDescription('Apply the environment out of the /app/environments/$ENVIRONMENT.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output): int
    {
        $environment = $this->getContainer()->getParameter('kernel.environment');
        $rootDir = $this->getContainer()->getParameter('kernel.project_dir');
        if (file_exists($rootDir.'/.env')) {
            (new Dotenv())->load($rootDir.'/.env');
            $_environment = @getenv('APPLY_ENVIRONMENT', true);
            if(!empty($_environment)) {
                $environment = $_environment;
            }
        }

        $ApplyEnvironment = System::importStatic('Sioweb\ApplyEnvironment\Contao\ApplyEnvironment');
        $ApplyEnvironment->applyEnvironment($environment);
        return $this->statusCode;
    }
    /**
     * Runs all Composer tasks to initialize a Contao Managed Edition.
     */
    public static function initializeApplication(Event $event): void
    {
        static::executeCommand('sioweb:environment', $event);
        $event->getIO()->write('<info>Environment is installed.</info>');
    }

    /**
     * @throws \RuntimeException
     */
    public static function executeCommand(string $cmd, Event $event): void
    {
        $phpFinder = new PhpExecutableFinder();

        if (false === ($phpPath = $phpFinder->find())) {
            throw new \RuntimeException('The php executable could not be found.');
        }

        $process = new Process(
            sprintf(
                '%s %s%s %s%s --env=%s',
                escapeshellarg($phpPath),
                escapeshellarg(__DIR__.'/../../bin/contao-console'),
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
}
