<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Invalid\argumentGuessing;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Type
 */
#[GQL\Type]
final class InvalidArgumentGuessing
{
    /**
     * @GQL\Field(name="guessFailed")
     *
     * @param mixed $test
     */
    #[GQL\Field(name: "guessFailed")]
    public function guessFail($test): int
    {
        return 12;
    }
}
