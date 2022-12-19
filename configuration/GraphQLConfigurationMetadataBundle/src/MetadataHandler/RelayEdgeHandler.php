<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\MetadataHandler;

use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\ExtensionConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\Extension\Builder\BuilderExtension;
use Overblog\GraphQLBundle\Relay\Connection\EdgeInterface;
use Overblog\GraphQLConfigurationMetadataBundle\Metadata;
use Overblog\GraphQLConfigurationMetadataBundle\MetadataConfigurationException;
use ReflectionClass;

class RelayEdgeHandler extends ObjectHandler
{
    public function addConfiguration(Configuration $configuration, ReflectionClass $reflectionClass, Metadata\Metadata $typeMetadata): ?TypeConfiguration
    {
        if (!$reflectionClass->implementsInterface(EdgeInterface::class)) {
            throw new MetadataConfigurationException(sprintf('The metadata %s on class "%s" can only be used on class implementing the EdgeInterface.', $this->formatMetadata('Edge'), $reflectionClass->getName()));
        }

        $typeConfiguration = parent::addConfiguration($configuration, $reflectionClass, $typeMetadata);
        if (null !== $typeConfiguration) {
            /** @var ObjectConfiguration $typeConfiguration */
            $typeConfiguration->addExtension(ExtensionConfiguration::create(BuilderExtension::ALIAS, [
                'name' => 'relay-edge',
                'configuration' => ['nodeType' => $typeMetadata->node],
            ]));
        }

        return $typeConfiguration;
    }
}
