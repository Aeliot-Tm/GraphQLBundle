<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\MetadataHandler;

use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\ExtensionConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\Extension\Builder\BuilderExtension;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLConfigurationMetadataBundle\Metadata;
use Overblog\GraphQLConfigurationMetadataBundle\MetadataConfigurationException;
use ReflectionClass;

class RelayConnectionHandler extends ObjectHandler
{
    public function addConfiguration(Configuration $configuration, ReflectionClass $reflectionClass, Metadata\Metadata $typeMetadata): ?TypeConfiguration
    {
        if (!$reflectionClass->implementsInterface(ConnectionInterface::class)) {
            throw new MetadataConfigurationException(sprintf('The metadata %s on class "%s" can only be used on class implementing the ConnectionInterface.', $this->formatMetadata('Connection'), $reflectionClass->getName()));
        }

        if (!(isset($typeMetadata->edge) xor isset($typeMetadata->node))) {
            throw new MetadataConfigurationException(sprintf('The metadata %s on class "%s" is invalid. You must define either the "edge" OR the "node" attribute, but not both.', $this->formatMetadata('Connection'), $reflectionClass->getName()));
        }

        $typeConfiguration = parent::addConfiguration($configuration, $reflectionClass, $typeMetadata);
        if (null !== $typeConfiguration) {
            /** @var ObjectConfiguration $typeConfiguration */
            $edgeType = $typeMetadata->edge ?? false;
            if (!$edgeType) {
                $edgeType = $typeConfiguration->getName().'Edge';
                $objectConfiguration = ObjectConfiguration::create($edgeType)
                    ->addExtension(ExtensionConfiguration::create(BuilderExtension::ALIAS, [
                        'name' => 'relay-edge',
                        'configuration' => ['nodeType' => $typeMetadata->node],
                    ]));
                $configuration->addType($objectConfiguration);
            }

            $typeConfiguration->addExtension(ExtensionConfiguration::create(BuilderExtension::ALIAS, [
                'name' => 'relay-connection',
                'configuration' => ['edgeType' => $edgeType],
            ]));
        }

        return $typeConfiguration;
    }
}
