<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests;

use Overblog\GraphQLConfigurationMetadataBundle\Reader\AnnotationReader;
use Overblog\GraphQLConfigurationMetadataBundle\Reader\MetadataReaderInterface;

class ConfigurationAnnotationTest extends ConfigurationMetadataTest
{
    public function getMetadataReader(): MetadataReaderInterface
    {
        return new AnnotationReader(null, false);
    }
}
