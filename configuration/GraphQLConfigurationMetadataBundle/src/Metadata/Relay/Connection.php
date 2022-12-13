<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Metadata\Relay;

use Attribute;
use Overblog\GraphQLConfigurationMetadataBundle\Metadata\Type;

/**
 * Annotation for GraphQL relay connection.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("CLASS")
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Connection extends Type
{
    /**
     * Connection Edge type.
     */
    public ?string $edge;

    /**
     * Connection Node type.
     */
    public ?string $node;

    public function __construct(string $edge = null, string $node = null)
    {
        parent::__construct();

        $this->edge = $edge;
        $this->node = $node;
    }
}
