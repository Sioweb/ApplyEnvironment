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

use Contao\CoreBundle\Analyzer\HtaccessAnalyzer;
use Contao\CoreBundle\Util\SymlinkUtil;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Contao\CoreBundle\Command\AbstractLockedCommand;
/**
 * Symlinks the public resources into the web directory.
 */
class AddGitCommand extends AbstractLockedCommand
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
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('sioweb:add:git')
            ->setDefinition([
                new InputArgument('repository', InputArgument::REQUIRED, 'The url to the git repository'),
                new InputArgument('package', InputArgument::REQUIRED, 'The package name'),
                new InputOption('target-dir', 'td', InputOption::VALUE_OPTIONAL, 'The composer target dir'),
                new InputOption('vendor-dir', 'vd', InputOption::VALUE_OPTIONAL, 'The composer vendor dir'),
            ])
            ->setDescription('Initialize the git repository, in the package for better development in vendor (Only recomended on localhost!).')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeLocked(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->rootDir = $this->getContainer()->getParameter('kernel.project_dir');
        $this->gitUrl = rtrim($input->getArgument('url'), '/');
        $this->packageName = rtrim($input->getArgument('package'), '/');
        // die("cd \"".$this->rootDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.str_replace('/',DIRECTORY_SEPARATOR,$this->packageName)."\" && git init && git remote add origin ".$this->gitUrl." && git fetch --all && git reset --hard origin/master 2>&1");
        exec("cd \"".$this->rootDir.'/vendor/'.$this->packageName."\" && git init && git remote add origin ".$this->gitUrl." && git fetch --all && git reset --hard origin/master 2>&1", $output);
        // print_r($output);
        // die();

        // if (!empty($this->rows)) {
        //     $this->io->newLine();
        //     $this->io->table(['', 'GIT', 'Url / Error'], $this->rows);
        // }

        return $this->statusCode;
    }
}
