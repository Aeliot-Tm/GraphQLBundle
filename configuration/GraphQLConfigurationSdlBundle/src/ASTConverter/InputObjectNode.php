<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationSdlBundle\ASTConverter;

use Overblog\GraphQLBundle\Configuration\InputConfiguration;
use Overblog\GraphQLBundle\Configuration\RootTypeConfiguration;

final class InputObjectNode extends ObjectNode
{
    protected static function createConfiguration(string $name): RootTypeConfiguration
    {
        return InputConfiguration::create($name);
    }
}
