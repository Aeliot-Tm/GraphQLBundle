<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Relay;

use Overblog\GraphQLBundle\Relay\Connection\Output\Edge;
use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Relay\Edge(node="Character")
 */
#[GQL\Relay\Edge(node: 'Character')]
final class FriendsConnectionEdge extends Edge
{
}
