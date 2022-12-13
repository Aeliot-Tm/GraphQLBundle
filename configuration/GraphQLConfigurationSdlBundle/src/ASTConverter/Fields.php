<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationSdlBundle\ASTConverter;

use GraphQL\Language\AST\Node;
use GraphQL\Utils\AST;
use Overblog\GraphQLBundle\Configuration\ArgumentConfiguration;
use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\InputFieldConfiguration;

class Fields
{
    const TYPE_FIELDS = 'fields';
    const TYPE_ARGUMENTS = 'arguments';
    const TYPE_INPUT_FIELDS = 'input-fields';

    const TYPES = [
        self::TYPE_FIELDS => ['property' => 'fields', 'class' => FieldConfiguration::class],
        self::TYPE_ARGUMENTS => ['property' => 'arguments', 'class' => ArgumentConfiguration::class],
        self::TYPE_INPUT_FIELDS => ['property' => 'fields', 'class' => InputFieldConfiguration::class],
    ];

    public static function get(Node $node, string $type = self::TYPE_FIELDS): array
    {
        $list = [];
        $parameters = self::TYPES[$type];
        $property = $parameters['property'];
        /** @var ArgumentConfiguration|FieldConfiguration|InputFieldConfiguration $class */
        $class = $parameters['class'];

        if (!empty($node->$property)) {
            foreach ($node->$property as $definition) {
                $configuration = $class::create($definition->name->value, Type::get($definition))
                    ->setDescription(Description::get($definition))
                    ->addExtensions(Extensions::get($definition));

                if (self::TYPE_FIELDS === $type) {
                    $configuration->setDeprecationReason(Deprecated::get($definition));
                    if (!empty($definition->arguments)) {
                        foreach (self::get($definition, self::TYPE_ARGUMENTS) as $argumentConfiguration) {
                            $configuration->addArgument($argumentConfiguration);
                        }
                    }
                } else {
                    if (!empty($definition->defaultValue)) {
                        $value = AST::valueFromASTUntyped($definition->defaultValue);
                        if (null !== $value) {
                            $configuration->setDefaultValue($value);
                        }
                    }
                }

                $list[] = $configuration;
            }
        }

        return $list;
    }
}
