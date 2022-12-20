<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationSdlBundle\ASTConverter;

use Overblog\GraphQLBundle\Configuration\InterfaceConfiguration;
use Overblog\GraphQLBundle\Configuration\RootTypeConfiguration;

final class InterfaceNode extends ObjectNode
{
    protected static function createConfiguration(string $name): RootTypeConfiguration
    {
        return InterfaceConfiguration::create($name);
    }

    protected static function getFieldsType(): string
    {
        return Fields::TYPE_INPUT_FIELDS;
    }
}
