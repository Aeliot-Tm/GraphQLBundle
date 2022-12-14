<?php

declare(strict_types=1);


namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\Stub;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;

final class TestKernel extends SymfonyKernel
{
    use MicroKernelTrait;

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return \dirname($this->getProjectDir()) . '/var/cache/' . $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return \dirname($this->getProjectDir()) . '/var/log';
    }

    protected function getContainerClass(): string
    {
        return parent::getContainerClass() . 'Tmp';
    }
}
