<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Union;

use GraphQL\Type\Definition\Type;
use Overblog\GraphQLBundle\Resolver\TypeResolver;
use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Union(types={"Hero", "Droid", "Sith"})
 */
#[GQL\Union(types: ['Hero', 'Droid', 'Sith'])]
final class SearchResult2
{
    public static function resolveType(TypeResolver $typeResolver, bool $value): ?Type
    {
        return $typeResolver->resolve('Hero');
    }
}
