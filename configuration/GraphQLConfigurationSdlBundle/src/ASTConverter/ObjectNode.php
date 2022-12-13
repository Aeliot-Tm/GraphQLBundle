<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationSdlBundle\ASTConverter;

use GraphQL\Language\AST\Node;
use Overblog\GraphQLBundle\Configuration\InputConfiguration;
use Overblog\GraphQLBundle\Configuration\InterfaceConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;

class ObjectNode implements NodeInterface
{
    protected const TYPENAME = 'object';

    public static function toConfiguration(string $name, Node $node): TypeConfiguration
    {
        $fieldsType = Fields::TYPE_FIELDS;
        switch (static::TYPENAME) {
            case 'object':
                $configuration = ObjectConfiguration::create($name);
                break;
            case 'interface':
                $configuration = InterfaceConfiguration::create($name);
                break;
            case 'input-object':
                $configuration = InputConfiguration::create($name);
                $fieldsType = Fields::TYPE_INPUT_FIELDS;
                break;
        }

        $configuration->setDescription(Description::get($node));
        $configuration->addExtensions(Extensions::get($node));

        $configuration->addFields(Fields::get($node, $fieldsType));

        if (!empty($node->interfaces)) {
            $interfaces = [];
            foreach ($node->interfaces as $interface) {
                $interfaces[] = Type::astTypeNodeToString($interface);
            }
            if (count($interfaces) > 0) {
                $configuration->setInterfaces($interfaces);
            }
        }

        return $configuration;
    }
}
