<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationYamlBundle;

use Overblog\GraphQLBundle\Configuration\ArgumentConfiguration;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\EnumConfiguration;
use Overblog\GraphQLBundle\Configuration\EnumValueConfiguration;
use Overblog\GraphQLBundle\Configuration\ExtensionConfiguration;
use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Overblog\GraphQLBundle\Configuration\InputConfiguration;
use Overblog\GraphQLBundle\Configuration\InputFieldConfiguration;
use Overblog\GraphQLBundle\Configuration\InterfaceConfiguration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use Overblog\GraphQLBundle\Configuration\RootTypeConfiguration;
use Overblog\GraphQLBundle\Configuration\ScalarConfiguration;
use Overblog\GraphQLBundle\Configuration\TypeConfiguration;
use Overblog\GraphQLBundle\Configuration\UnionConfiguration;
use Overblog\GraphQLBundle\ConfigurationProvider\ConfigurationFilesParser;
use Overblog\GraphQLBundle\Extension\Access\AccessExtension;
use Overblog\GraphQLBundle\Extension\Builder\BuilderExtension;
use Overblog\GraphQLBundle\Extension\IsPublic\IsPublicExtension;
use Overblog\GraphQLBundle\Extension\Validation\ValidationExtension;
use Overblog\GraphQLBundle\Extension\Validation\ValidationGroupsExtension;
use Symfony\Component\Config\Definition\Processor;

abstract class ConfigurationParser extends ConfigurationFilesParser
{
    protected Configuration $configuration;

    public const EXTENSIONS = [
        'access' => AccessExtension::class,
        'public' => IsPublicExtension::class,
        'validation' => ValidationExtension::class,
        'validationGroups' => ValidationGroupsExtension::class,
    ];

    public function __construct(array $directories = [])
    {
        parent::__construct($directories);
        $this->configuration = new Configuration();
    }

    public function getConfiguration(): Configuration
    {
        $config = $this->getConfigurationArray();

        // Turns the array into a proper configuration object
        $configuration = new Configuration();
        foreach ($config as $name => $typeConfig) {
            $typeConfiguration = $this->configArrayToTypeConfiguration($name, $typeConfig);
            if (null !== $typeConfiguration) {
                $configuration->addType($typeConfiguration);
            }
        }

        return $configuration;
    }

    public function getConfigurationArray(): array
    {
        $files = $this->getFiles();
        $config = [];
        foreach ($files as $file) {
            $config[] = $this->parseFile($file);
        }

        $config = [array_merge(...$config)]; // TODO: handle duplicates

        return (new Processor())->processConfiguration(new TypesConfiguration(), $config);
    }

    /**
     * Transform a type array config into a proper TypeConfiguration
     *
     * @param array<string,mixed> $typeConfig
     */
    protected function configArrayToTypeConfiguration(string $name, array $typeConfig): ?RootTypeConfiguration
    {
        $config = $typeConfig['config'];
        $configType = $typeConfig['type'];

        switch ($configType) {
            case TypesConfiguration::TYPE_INTERFACE:
                $typeConfiguration = new InterfaceConfiguration($name);
                if (isset($config['resolveType'])) {
                    $typeConfiguration->setResolveType((string) $config['resolveType']);
                }
                // no break
            case TypesConfiguration::TYPE_OBJECT:
                $typeConfiguration = $typeConfiguration ?? new ObjectConfiguration($name);

                if (count($config['interfaces']) > 0) {
                    $typeConfiguration->setInterfaces($config['interfaces']);
                }

                if (isset($config['resolveField'])) {
                    $typeConfiguration->setResolveField($config['resolveField']);
                }

                if (isset($config['builders'])) {
                    foreach ($config['builders'] as $fieldsBuilder) {
                        $typeConfiguration->addExtension(new ExtensionConfiguration(BuilderExtension::ALIAS, [
                            'name' => $fieldsBuilder['builder'],
                            'configuration' => $fieldsBuilder['builderConfig'] ?? null,
                        ]));
                    }
                }

                foreach ($config['fields'] as $fieldName => $fieldConfig) {
                    $fieldConfiguration = new FieldConfiguration($fieldName, $fieldConfig['type'] ?? null);
                    $this->setCommonProperties($fieldConfiguration, $fieldConfig);
                    if (isset($fieldConfig['resolve'])) {
                        $fieldConfiguration->setResolve($fieldConfig['resolve']);
                    }
                    if (isset($fieldConfig['complexity'])) {
                        $fieldConfiguration->setComplexity((string) $fieldConfig['complexity']);
                    }
                    if (isset($fieldConfig['args'])) {
                        foreach ($fieldConfig['args'] as $argName => $argConfig) {
                            $argumentConfiguration = new ArgumentConfiguration($argName, $argConfig['type']);
                            $this->setCommonProperties($argumentConfiguration, $argConfig);
                            if (isset($argConfig['validation'])) {
                                $argumentConfiguration->addExtension(new ExtensionConfiguration(ValidationExtension::ALIAS, $argConfig['validation']));
                            }
                            if (array_key_exists('defaultValue', $argConfig)) {
                                $argumentConfiguration->setDefaultValue($argConfig['defaultValue']);
                            }
                            $this->handleDefaultExtensions($argumentConfiguration, $argConfig);
                            $fieldConfiguration->addArgument($argumentConfiguration);
                        }
                    }
                    if (isset($fieldConfig['builder'])) {
                        $fieldConfiguration->addExtension(new ExtensionConfiguration(BuilderExtension::ALIAS, [
                            'name' => $fieldConfig['builder'],
                            'configuration' => $fieldConfig['builderConfig'] ?? null,
                        ]));
                    }
                    if (isset($fieldConfig['argsBuilder'])) {
                        $builderName = $fieldConfig['argsBuilder']['builder'];
                        $configuration = $fieldConfig['argsBuilder']['config'] ?? null;
                        $fieldConfiguration->addExtension(new ExtensionConfiguration(BuilderExtension::ALIAS, [
                            'name' => $builderName,
                            'configuration' => $configuration,
                        ]));
                    }
                    $this->handleDefaultExtensions($fieldConfiguration, $fieldConfig);
                    $typeConfiguration->addField($fieldConfiguration);
                }
                break;
            case TypesConfiguration::TYPE_INPUT:
                $typeConfiguration = new InputConfiguration($name);
                foreach ($config['fields'] as $fieldName => $fieldConfig) {
                    $fieldConfiguration = new InputFieldConfiguration($fieldName, $fieldConfig['type']);
                    $this->setCommonProperties($fieldConfiguration, $fieldConfig);
                    if (array_key_exists('defaultValue', $fieldConfig)) {
                        $fieldConfiguration->setDefaultValue($fieldConfig['defaultValue']);
                    }
                    $this->handleDefaultExtensions($fieldConfiguration, $fieldConfig);
                    $typeConfiguration->addField($fieldConfiguration);
                }
                break;
            case TypesConfiguration::TYPE_SCALAR:
                $typeConfiguration = new ScalarConfiguration($name);
                if (isset($config['scalarType'])) {
                    $typeConfiguration->setScalarType($config['scalarType']);
                }
                if (isset($config['serialize'])) {
                    $typeConfiguration->setSerialize($config['serialize']);
                }
                if (isset($config['parseValue'])) {
                    $typeConfiguration->setParseValue($config['parseValue']);
                }
                if (isset($config['parseLiteral'])) {
                    $typeConfiguration->setParseLiteral($config['parseLiteral']);
                }
                break;
            case TypesConfiguration::TYPE_ENUM:
                $typeConfiguration = new EnumConfiguration($name);
                if (isset($config['enumClass'])) {
                    $typeConfiguration->setEnumClass($config['enumClass']);
                }
                foreach ($config['values'] as $valueName => $valueConfig) {
                    $valueConfiguration = new EnumValueConfiguration($valueName, $valueConfig['value']);
                    $this->setCommonProperties($valueConfiguration, $valueConfig);
                    $typeConfiguration->addValue($valueConfiguration);
                }
                break;
            case TypesConfiguration::TYPE_UNION:
                $typeConfiguration = new UnionConfiguration($name);
                $typeConfiguration->setTypes($config['types']);
                $typeConfiguration->setResolveType($config['resolveType']);
                break;
            default:
                return null;
        }

        $this->handleDefaultExtensions($typeConfiguration, $config);
        $this->setCommonProperties($typeConfiguration, $config);

        return $typeConfiguration;
    }

    protected function setCommonProperties(TypeConfiguration $typeConfiguration, array $config): void
    {
        if ($typeConfiguration instanceof RootTypeConfiguration && isset($config['name'])) {
            $typeConfiguration->setPublicName($config['name']);
        }

        if (isset($config['description'])) {
            $typeConfiguration->setDescription($config['description']);
        }

        if (isset($config['deprecationReason'])) {
            $typeConfiguration->setDeprecationReason($config['deprecationReason']);
        }

        foreach ($config['extensions'] as $extension) {
            $typeConfiguration->addExtension(new ExtensionConfiguration($extension['name'], $extension['configuration']));
        }
    }

    protected function handleDefaultExtensions(TypeConfiguration $typeConfiguration, array $config): void
    {
        foreach (self::EXTENSIONS as $extensionKey => $extensionClass) {
            $alias = constant(sprintf('%s::ALIAS', $extensionClass));
            $supports = constant(sprintf('%s::SUPPORTS', $extensionClass));

            if (isset($config[$extensionKey]) && in_array($typeConfiguration->getGraphQLType(), $supports)) {
                $typeConfiguration->addExtension(new ExtensionConfiguration($alias, $config[$extensionKey]));
            }
        }
    }
}
