<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationSdlBundle\ASTConverter;

use GraphQL\Language\AST\Node;
use Overblog\GraphQLBundle\Configuration\FieldsOwner;
use Overblog\GraphQLBundle\Configuration\Interfaced;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\Configuration\RootTypeConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;

class ObjectNode implements NodeInterface
{
    public static function toConfiguration(string $name, Node $node): TypeConfiguration
    {
        $configuration = static::createConfiguration($name);
        $configuration->setDescription(Description::get($node));
        $configuration->addExtensions(Extensions::get($node));

        if ($configuration instanceof FieldsOwner) {
            $configuration->addFields(Fields::get($node, static::getFieldsType()));
        }

        if ($configuration instanceof Interfaced) {
            $configuration->setInterfaces(static::getInterfaces($node));
        }

        return $configuration;
    }

    protected static function createConfiguration(string $name): RootTypeConfiguration
    {
        return ObjectConfiguration::create($name);
    }

    protected static function getFieldsType(): string
    {
        return Fields::TYPE_FIELDS;
    }

    protected static function getInterfaces(Node $node): array
    {
        $interfaces = [];
        foreach ($node->interfaces ?? [] as $interface) {
            $interfaces[] = Type::astTypeNodeToString($interface);
        }

        return $interfaces;
    }
}
