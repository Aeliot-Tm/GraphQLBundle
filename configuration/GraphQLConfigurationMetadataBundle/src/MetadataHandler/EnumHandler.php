<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\MetadataHandler;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata;
use Overblog\GraphQLConfigurationMetadataBundle\Metadata\Enum;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\EnumConfiguration;
use Overblog\GraphQLBundle\Configuration\EnumValueConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use ReflectionClass;
use ReflectionClassConstant;

/**
 * TODO: check if there is necessary to transfer handling of PHP 8 enum
 *       from the removed \Overblog\GraphQLBundle\Config\Parser\MetadataParser\MetadataParser
 */
class EnumHandler extends MetadataHandler
{
    const TYPE = TypeConfiguration::TYPE_ENUM;

    public function setClassesMap(ReflectionClass $reflectionClass, Metadata\Metadata $enumMetadata): void
    {
        $gqlName = $this->getEnumName($reflectionClass, $enumMetadata);
        $this->classesTypesMap->addClassType($gqlName, $reflectionClass->getName(), self::TYPE);
    }

    /**
     * Add a GraphQL Union configuration from given union metadata.
     */
    public function addConfiguration(Configuration $configuration, ReflectionClass $reflectionClass, Metadata\Metadata $enumMetadata): ?TypeConfiguration
    {
        if (!$enumMetadata instanceof Enum) {
            throw new \InvalidArgumentException(sprintf('Metadata arguments MUST be an instance of %s', Enum::class));
        }

        $gqlName = $this->getEnumName($reflectionClass, $enumMetadata);
        $metadatas = $this->getMetadatas($reflectionClass);

        $enumConfiguration = EnumConfiguration::create($gqlName)
            ->setDescription($this->getDescription($metadatas))
            ->addExtensions($this->getExtensions($metadatas))
            ->setOrigin($this->getOrigin($reflectionClass));

        // Annotation @EnumValue handling
        /** @var Metadata\EnumValue[] $enumValues */
        $enumValues = $this->getMetadataMatching($metadatas, Metadata\EnumValue::class);

        foreach ($reflectionClass->getConstants() as $name => $value) {
            $reflectionConstant = new ReflectionClassConstant($reflectionClass->getName(), $name);
            $valueMetadatas = $this->getMetadatas($reflectionConstant);

            $enumValueConfig = EnumValueConfiguration::create($name, $value)
                ->setDescription($this->getDescription($valueMetadatas))
                ->setDeprecationReason($this->getDeprecation($valueMetadatas))
                ->addExtensions($this->getExtensions($valueMetadatas))
                ->setOrigin($this->getOrigin($reflectionConstant));

            // Search matching @EnumValue handling
            $enumValueAnnotation = current(array_filter($enumValues, static fn (Metadata\EnumValue $enumValueAnnotation): bool => $enumValueAnnotation->name === $name));

            if (false !== $enumValueAnnotation) {
                if (isset($enumValueAnnotation->description)) {
                    $enumValueConfig->setDescription($enumValueAnnotation->description);
                }

                if (isset($enumValueAnnotation->deprecationReason)) {
                    $enumValueConfig->setDeprecationReason($enumValueAnnotation->deprecationReason);
                }
            }

            $enumConfiguration->addValue($enumValueConfig);
        }

        $configuration->addType($enumConfiguration);

        return $enumConfiguration;
    }

    protected function getEnumName(ReflectionClass $reflectionClass, Metadata\Metadata $enumMetadata): string
    {
        return $enumMetadata->name ?? $reflectionClass->getShortName();
    }
}
