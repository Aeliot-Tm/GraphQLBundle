<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Union;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Union(typeResolver="value.getType()")
 */
#[GQL\Union(typeResolver: "value.getType()")]
interface Killable
{
}
