<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Relay\Mutation;

use Overblog\GraphQLBundle\Definition\Builder\MappingInterface;
use Overblog\GraphQLBundle\Enum\TypeEnum;
use function array_merge;
use function is_array;
use function preg_replace;

final class InputDefinition implements MappingInterface
{
    public function toMappingDefinition(array $config): array
    {
        $alias = preg_replace('/(.*)?Type$/', '$1', $config['class_name']);
        $name = $config['name'];
        $name = preg_replace('/(.*)?Input$/', '$1', $name).'Input';

        $inputFields = empty($config['fields']) || !is_array($config['fields']) ? [] : $config['fields'];

        return [
            $alias => [
                'type' => TypeEnum::INPUT_OBJECT,
                'config' => [
                    'name' => $name,
                    'fields' => array_merge(
                        $inputFields,
                        [
                            'clientMutationId' => ['type' => 'String'],
                        ]
                    ),
                ],
            ],
        ];
    }
}
