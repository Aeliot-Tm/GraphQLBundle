<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Reader;

use Reflector;

interface MetadataReaderInterface
{
    public function formatMetadata(string $metadataType): string;

    public function getMetadatas(Reflector $reflector): array;
}
