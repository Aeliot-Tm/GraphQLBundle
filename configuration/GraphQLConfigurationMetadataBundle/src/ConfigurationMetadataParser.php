<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle;

use Doctrine\Common\Annotations\Reader;
use Overblog\GraphQLConfigurationMetadataBundle\MetadataHandler\MetadataHandler;
use Overblog\GraphQLConfigurationMetadataBundle\Reader\MetadataReaderInterface;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\ConfigurationProvider\ConfigurationFilesParser;
use ReflectionClass;
use Reflector;
use RuntimeException;
use SplFileInfo;
use function sprintf;

class ConfigurationMetadataParser extends ConfigurationFilesParser
{
    protected ?Reader $annotationReader = null;
    protected MetadataReaderInterface $metadataReader;
    protected ClassesTypesMap $classesTypesMap;

    protected Configuration $configuration;

    protected array $providers = [];
    protected array $resolvers = [];

    public function __construct(MetadataReaderInterface $metadataReader, ClassesTypesMap $classesTypesMap, iterable $resolvers, array $directories = [])
    {
        parent::__construct($directories);
        $this->configuration = new Configuration();
        $this->metadataReader = $metadataReader;
        $this->classesTypesMap = $classesTypesMap;

        $this->resolvers = $resolvers instanceof \Traversable ? iterator_to_array($resolvers) : $resolvers;
    }

    public function getSupportedExtensions(): array
    {
        return ['php'];
    }

    public function getConfiguration(): Configuration
    {
        $files = $this->getFiles();

        foreach ($files as $file) {
            $this->parseFileClassMap($file);
        }

        foreach ($files as $file) {
            $this->parseFile($file);
        }

        $this->classesTypesMap->cache();

        return $this->configuration;
    }

    protected function parseFileClassMap(SplFileInfo $file): void
    {
        $this->processFile($file, true);
    }

    protected function parseFile(SplFileInfo $file): void
    {
        $this->processFile($file);
    }

    protected function processFile(SplFileInfo $file, bool $initializeClassesTypesMap = false): void
    {
        try {
            $reflectionClass = $this->getFileClassReflection($file);

            foreach ($this->getMetadatas($reflectionClass) as $classMetadata) {
                if ($classMetadata instanceof Metadata\Metadata) {
                    $resolver = $this->getResolver($classMetadata);
                    if ($resolver) {
                        if ($initializeClassesTypesMap) {
                            $resolver->setClassesMap($reflectionClass, $classMetadata);
                        } else {
                            $resolver->addConfiguration($this->configuration, $reflectionClass, $classMetadata);
                        }
                    }
                }
            }
        } catch (RuntimeException $e) {
            throw new MetadataConfigurationException(sprintf('Failed to parse GraphQL metadata from file "%s".', $file), $e->getCode(), $e);
        }
    }

    protected function getResolver(Metadata\Metadata $classMetadata): ?MetadataHandler
    {
        foreach ($this->resolvers as $metadataClass => $resolver) {
            if ($classMetadata instanceof $metadataClass) {
                return $resolver;
            }
        }

        return null;
    }

    protected function getFileClassReflection(SplFileInfo $file): ReflectionClass
    {
        try {
            /** @var class-string $className */
            $className = $file->getBasename('.php');
            $contents = file_get_contents($file->getRealPath());
            if (false === $contents) {
                throw new RuntimeException(sprintf('File %s is cannot be read', $file->getRealPath()));
            }
            if (preg_match('#namespace (.+);#', $contents, $matches)) {
                /** @var class-string $className */
                $className = trim($matches[1]).'\\'.$className;
            }

            return new ReflectionClass($className);
        } catch (RuntimeException $e) {
            throw new MetadataConfigurationException(sprintf('Failed to parse GraphQL metadata from file "%s".', $file), $e->getCode(), $e);
        }
    }

    /**
     * @return object[]
     */
    protected function getMetadatas(Reflector $reflector): array
    {
        return $this->metadataReader->getMetadatas($reflector);
    }
}
