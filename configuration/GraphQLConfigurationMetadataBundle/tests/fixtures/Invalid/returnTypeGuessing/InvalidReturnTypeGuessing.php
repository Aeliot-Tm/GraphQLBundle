<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Invalid\returnTypeGuessing;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Type
 */
#[GQL\Type]
final class InvalidReturnTypeGuessing
{
    /**
     * @GQL\Field(name="guessFailed")
     *
     * @phpstan-ignore-next-line
     */
    #[GQL\Field(name: 'guessFailed')]
    // @phpstan-ignore-next-line
    public function guessFail(int $test)
    {
        return 12;
    }
}
