<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Scalar;

use GraphQL\Language\AST\Node;
use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;
use function explode;
use function implode;

/**
 * @GQL\Scalar
 * @GQL\Description("The galaxy coordinates scalar")
 */
#[GQL\Scalar]
#[GQL\Description('The galaxy coordinates scalar')]
final class GalaxyCoordinates
{
    /**
     * @param int[] $coordinates
     */
    public static function serialize(array $coordinates): string
    {
        return implode(',', $coordinates);
    }

    /**
     * @return string[]
     */
    public static function parseValue(?string $value): array
    {
        return $value ? explode(',', $value) : [];
    }

    /**
     * @return string[]
     */
    public static function parseLiteral(Node $valueNode): array
    {
        return self::parseValue($valueNode->value);
    }
}
