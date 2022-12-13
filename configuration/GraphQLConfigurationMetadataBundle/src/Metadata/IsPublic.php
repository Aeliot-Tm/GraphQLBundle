<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Metadata;

use Attribute;
use Overblog\GraphQLBundle\Extension\IsPublic\IsPublicExtension;

/**
 * Annotation for GraphQL public on fields.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "METHOD", "PROPERTY"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
final class IsPublic extends Extension
{
    public function __construct(string $expression)
    {
        parent::__construct(IsPublicExtension::ALIAS, $expression);
    }
}
