<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Relay;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;

/**
 * @GQL\Relay\Connection(edge="FriendsConnectionEdge")
 */
#[GQL\Relay\Connection(edge: 'FriendsConnectionEdge')]
final class FriendsConnection extends Connection
{
}
