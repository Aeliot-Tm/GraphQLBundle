<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Union;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Union(name="ResultSearch", types={"Hero", "Droid", "Sith"}, typeResolver="value.getType()")
 * @GQL\Description("A search result")
 */
#[GQL\Union("ResultSearch", types: ["Hero", "Droid", "Sith"], typeResolver: "value.getType()")]
#[GQL\Description("A search result")]
final class SearchResult
{
}
