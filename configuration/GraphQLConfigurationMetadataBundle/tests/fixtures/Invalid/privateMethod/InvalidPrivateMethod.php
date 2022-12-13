<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Invalid\privateMethod;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Type
 */
#[GQL\Type]
final class InvalidPrivateMethod
{
    /**
     * @GQL\Field
     */
    #[GQL\Field]
    private function gql(): string
    {
        return 'invalid';
    }
}
