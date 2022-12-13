<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationYamlBundle;

use Overblog\GraphQLConfigurationYamlBundle\DependencyInjection\OverblogGraphQLConfigurationYamlExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GraphQLConfigurationYamlBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new OverblogGraphQLConfigurationYamlExtension();
    }
}
