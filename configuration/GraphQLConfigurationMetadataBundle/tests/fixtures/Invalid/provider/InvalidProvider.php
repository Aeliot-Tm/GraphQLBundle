<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Invalid\provider;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Provider
 */
#[GQL\Provider]
final class InvalidProvider
{
    /**
     * @GQL\Query(type="Int", targetType="RootMutation2")
     */
    #[GQL\Query(type: 'Int', targetTypes: 'RootMutation2')]
    public function noQueryOnMutation(): array
    {
        return [];
    }

    /**
     * @GQL\Mutation(type="Int", targetTypes="RootQuery2")
     */
    #[GQL\Mutation(type: 'Int', targetTypes: 'RootQuery2')]
    public function noMutationOnQuery(): array
    {
        return [];
    }
}
