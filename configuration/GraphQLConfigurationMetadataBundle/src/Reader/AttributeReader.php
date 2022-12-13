<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Reader;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

final class AttributeReader implements MetadataReaderInterface
{
    const METADATA_FORMAT = '#[%s]';

    public function formatMetadata(string $metadataType): string
    {
        return sprintf(self::METADATA_FORMAT, $metadataType);
    }

    public function getMetadatas(Reflector $reflector): array
    {
        $attributes = [];

        switch (true) {
            case $reflector instanceof ReflectionClass:
            case $reflector instanceof ReflectionMethod:
            case $reflector instanceof ReflectionProperty:
            case $reflector instanceof ReflectionClassConstant:
                if (is_callable([$reflector, 'getAttributes'])) {
                    $attributes = $reflector->getAttributes();
                }
        }

        // @phpstan-ignore-next-line
        return array_map(fn (ReflectionAttribute $attribute) => $attribute->newInstance(), $attributes);
    }
}
