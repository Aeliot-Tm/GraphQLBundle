<?php

declare(strict_types=1);

namespace Overblog\GraphQL\Bundle\ConfigurationYamlBundle\Definition;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

final class CustomScalarTypeDefinition extends TypeDefinition
{
    public function getDefinition(): ArrayNodeDefinition
    {
        /** @var ArrayNodeDefinition $node */
        $node = self::createNode('_custom_scalar_config');

        /** @phpstan-ignore-next-line */
        $node
            ->children()
                ->append($this->nameSection())
                ->append($this->descriptionSection())
                ->append($this->extensionsSection())
                ->variableNode('scalarType')->end()
                ->variableNode('serialize')->end()
                ->variableNode('parseValue')->end()
                ->variableNode('parseLiteral')->end()
            ->end();

        return $node;
    }
}
