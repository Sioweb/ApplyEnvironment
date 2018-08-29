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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
                new InputArgument('url', InputArgument::REQUIRED, 'The url to the git repository', 'https://www.github.com/'),
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

        $this->loadComposerJson();
        $this->initializeGitForPackage();

        if (!empty($this->rows)) {
            $this->io->newLine();
            $this->io->table(['', 'GIT', 'Url / Error'], $this->rows);
        }

        return $this->statusCode;
    }

    private function loadComposerJson() {
        
    }

    private function initializeGitForPackage() {
        
    }

    /**
     * Generates the symlinks in the web directory.
     */
    private function generateSymlinks(): void
    {
        $fs = new Filesystem();
        $uploadPath = $this->getContainer()->getParameter('contao.upload_path');

        // Remove the base folders in the document root
        $fs->remove($this->rootDir.'/'.$this->webDir.'/'.$uploadPath);
        $fs->remove($this->rootDir.'/'.$this->webDir.'/system/modules');
        $fs->remove($this->rootDir.'/'.$this->webDir.'/vendor');

        $this->symlinkFiles($uploadPath);
        $this->symlinkModules();
        $this->symlinkThemes();

        // Symlink the assets and themes directory
        $this->symlink('assets', $this->webDir.'/assets');
        $this->symlink('system/themes', $this->webDir.'/system/themes');

        // Symlinks the logs directory
        $this->symlink($this->getRelativePath($this->getContainer()->getParameter('kernel.logs_dir')), 'system/logs');

        // Symlink the TCPDF config file
        $this->symlink('vendor/contao/core-bundle/src/Resources/contao/config/tcpdf.php', 'system/config/tcpdf.php');
    }
}
