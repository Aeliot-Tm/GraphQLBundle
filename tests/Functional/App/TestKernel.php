<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Functional\App;

use Overblog\GraphQLConfigurationMetadataBundle\GraphQLConfigurationMetadataBundle;
use Overblog\GraphQL\Bundle\ConfigurationSdlBundle\GraphQLConfigurationSdlBundle;
use Overblog\GraphQLConfigurationYamlBundle\GraphQLConfigurationYamlBundle;
use Overblog\GraphQLBundle\OverblogGraphQLBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

use function sprintf;
use function sys_get_temp_dir;

final class TestKernel extends Kernel implements CompilerPassInterface
{
    use MicroKernelTrait {
        registerContainerConfiguration as registerContainerConfigurationTrait;
    }

    private ?string $testCase;

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new SecurityBundle();
        yield new MonologBundle();
        yield new OverblogGraphQLBundle();
        yield new GraphQLConfigurationYamlBundle();
        yield new GraphQLConfigurationSdlBundle();
        yield new GraphQLConfigurationMetadataBundle();
    }

    public function __construct(string $environment, bool $debug, string $testCase = null)
    {
        $this->testCase = $testCase;
        parent::__construct($environment, $debug);
    }

    public function getCacheDir(): string
    {
        return $this->basePath().'cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->basePath().'logs';
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getRootDir(): string
    {
        return __DIR__;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $this->registerContainerConfigurationTrait($loader);

        $loader->load(function (ContainerBuilder $container): void {
            $container->addCompilerPass($this);
        });
    }

    private function configureContainer(ContainerConfigurator $container, LoaderInterface $loader, ContainerBuilder $builder): void
    {
        $configDir = $this->getConfigDir();

        $loader->load($configDir.'/config.yml');

        if (is_file($configDir.'/services.yml')) {
            $container->import($configDir.'/services.yml');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        // disabled http_exception_listener because it flatten exception to html response
        if ($container->has('http_exception_listener')) {
            $container->removeDefinition('http_exception_listener');
        }
    }

    private function basePath(): string
    {
        return sys_get_temp_dir().'/OverblogGraphQLBundle/'.Kernel::VERSION.'/'.($this->testCase ? $this->testCase.'/' : '');
    }

    /**
     * Gets the path to the configuration directory.
     */
    private function getConfigDir(): string
    {
        $configDir = $this->getProjectDir() . '/config';
        if (null !== $this->testCase) {
            $configDir .= sprintf('/%s', $this->testCase);
        }

        return $configDir;
    }
}
