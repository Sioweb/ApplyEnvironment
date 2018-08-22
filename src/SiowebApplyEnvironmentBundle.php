<?php

namespace Sioweb\ApplyEnvironment;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Sioweb\ApplyEnvironment\DependencyInjection\ApplyEnvironmentExtension;

/**
 * @author Sascha Weidner <http://www.sioweb.de>
 */
class SiowebApplyEnvironmentBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new ApplyEnvironmentExtension();
    }
}