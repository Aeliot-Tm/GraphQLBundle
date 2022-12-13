<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Invalid\provider2;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Provider
 */
#[GQL\Provider]
class InvalidProvider
{
    /**
     * @GQL\Mutation(type="Int", targetTypes="RootQuery2")
     */
    #[GQL\Mutation(type: "Int", targetTypes: "RootQuery2")]
    public function noMutationOnQuery(): array
    {
        return [];
    }
}
