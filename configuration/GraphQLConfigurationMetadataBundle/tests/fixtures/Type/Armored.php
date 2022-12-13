<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Type;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\TypeInterface(name="WithArmor", typeResolver="@=query('character_type', value)")
 * @GQL\Description("The armored interface")
 * @GQL\Extension("CustomExtension", {"config1"=12})
 */
#[GQL\TypeInterface("WithArmor", typeResolver: "@=query('character_type', value)")]
#[GQL\Description("The armored interface")]
#[GQL\Extension('CustomExtension', ['config1' => 12])]
interface Armored
{
}
