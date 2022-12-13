<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationSdlBundle;

use Overblog\GraphQLConfigurationSdlBundle\DependencyInjection\OverblogGraphQLConfigurationSdlExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class GraphQLConfigurationSdlBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new OverblogGraphQLConfigurationSdlExtension();
    }
}
