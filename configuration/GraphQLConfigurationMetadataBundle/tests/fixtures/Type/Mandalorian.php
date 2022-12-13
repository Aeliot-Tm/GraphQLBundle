<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Type;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;
use Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Union\Killable;

/**
 * @GQL\Type
 */
#[GQL\Type]
final class Mandalorian extends Character implements Killable, Armored
{
}
