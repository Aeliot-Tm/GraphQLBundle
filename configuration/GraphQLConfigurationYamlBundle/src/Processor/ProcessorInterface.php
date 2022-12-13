<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationYamlBundle\Processor;

interface ProcessorInterface
{
    public static function process(array $configs): array;
}
