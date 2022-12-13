<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\TypeGuesser;

use ReflectionClass;
use Reflector;

interface TypeGuesserInterface
{
    public function guessType(ReflectionClass $reflectionClass, Reflector $reflector, array $filterGraphQLTypes = []): ?string;
}
