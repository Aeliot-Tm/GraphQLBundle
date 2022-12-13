<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Relay;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;

/**
 * @GQL\Relay\Connection(node="Character")
 */
#[GQL\Relay\Connection(node: 'Character')]
final class EnemiesConnection extends Connection
{
}
