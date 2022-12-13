<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Type;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Type(isTypeOf="@=isTypeOf('App\Entity\Droid')")
 * @GQL\Description("The Droid type")
 */
#[GQL\Type(isTypeOf: "@=isTypeOf('App\Entity\Droid')")]
#[GQL\Description("The Droid type")]
final class Droid extends Character
{
    /**
     * @GQL\Field
     */
    #[GQL\Field]
    protected int $memory;
}
