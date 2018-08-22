<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Sioweb\ApplyEnvironment\EventListener;

use Symfony\Component\Filesystem\Filesystem;

class InitializeApplicationListener
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * Adds the initialize.php file.
     */
    public function onInitializeApplication(): void
    {
    }
}