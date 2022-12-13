<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Type;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;
use Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Enum\Race;
use Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Union\Killable;

/**
 * @GQL\Type(interfaces={"Character"})
 * @GQL\Description("The Hero type")
 */
#[GQL\Type(interfaces: ['Character'])]
#[GQL\Description('The Hero type')]
final class Hero extends Character implements Killable
{
    /**
     * @GQL\Field(type="Race")
     */
    #[GQL\Field(type: 'Race')]
    protected Race $race;
}
