<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests;

use Overblog\GraphQLConfigurationMetadataBundle\Reader\AttributeReader;
use Overblog\GraphQLConfigurationMetadataBundle\Reader\MetadataReaderInterface;

/**
 * @requires PHP 8.
 */
class ConfigurationAttributeTest extends ConfigurationMetadataTest
{
    public function getMetadataReader(): MetadataReaderInterface
    {
        return new AttributeReader();
    }
}
