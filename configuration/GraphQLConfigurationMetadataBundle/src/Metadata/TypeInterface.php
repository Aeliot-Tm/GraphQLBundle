<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Metadata;

use Attribute;
use InvalidArgumentException;

/**
 * Annotation for GraphQL interface.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("CLASS")
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class TypeInterface extends Metadata
{
    /**
     * Interface name.
     */
    public ?string $name;

    /**
     * Resolver type for interface.
     */
    public string $typeResolver;

    /**
     * FIXME: parameters with default value MUST be after parameters without default values.
     *
     * @see https://www.php.net/manual/en/functions.arguments.php#functions.arguments.default Example #7 Incorrect usage of default function arguments
     *
     * @param string|null $name         The GraphQL name of the interface
     * @param string|null $typeResolver The express resolve type
     */
    public function __construct(string $name = null, string $typeResolver = null)
    {
        // NOTE: workaround arguments order keeping
        // TODO: refactor
        if (!$typeResolver) {
            throw new InvalidArgumentException('Type resolver must not be empty');
        }
        $this->name = $name;
        $this->typeResolver = $typeResolver;
    }
}
