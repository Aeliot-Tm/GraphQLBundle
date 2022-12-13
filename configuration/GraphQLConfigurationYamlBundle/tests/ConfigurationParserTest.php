<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationYamlBundle\Tests;

use Overblog\GraphQLConfigurationYamlBundle\ConfigurationYamlParser;
use Overblog\GraphQLBundle\Configuration\Configuration;
use Overblog\GraphQLBundle\Configuration\ObjectConfiguration;
use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Finder\Finder;

class ConfigurationParserTest extends WebTestCase
{
    protected array $excludeDirectories = ['broken', 'constant'];
    protected Configuration $configuration;

    public function setUp(): void
    {
        parent::setup();
        $this->configuration = unserialize(serialize($this->getConfiguration()));
    }

    protected function getConfiguration(array $includeDirectories = [])
    {
        $finder = Finder::create()
            ->in(__DIR__.'/fixtures')
            ->directories();
        foreach ($this->excludeDirectories as $exclude) {
            $finder = $finder->exclude($exclude);
        }
        $directories = array_values(array_map(fn (SplFileInfo $dir) => $dir->getPathname(), iterator_to_array($finder->getIterator())));
        $directories = [...$directories, ...$includeDirectories];

        $generator = new ConfigurationYamlParser($directories);

        return $generator->getConfiguration();
    }

    protected function getType(string $name, string $configurationClass = null)
    {
        $type = $this->configuration->getType($name);
        if (!$type) {
            $this->fail(sprintf('Unable to retrieve type "%s" from configuration', $name));
        }
        $this->assertNotNull($type);
        if ($configurationClass) {
            $this->assertInstanceOf($configurationClass, $type);
        }

        return $type;
    }

    public function testBrokenYaml(): void
    {
        $dirname = __DIR__.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR.'broken'.DIRECTORY_SEPARATOR.'yaml';
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('#The file "(.*)'.preg_quote(DIRECTORY_SEPARATOR, '/').'broken.types.yml" does not contain valid YAML\.#');

        $parser = new ConfigurationYamlParser([$dirname]);
        $parser->getConfiguration();
    }

    public function testQuery(): void
    {
        $object = $this->getType('Query', ObjectConfiguration::class);
        $this->assertEquals([
            'name' => 'Query',
            'fields' => [
                [
                    'name' => 'node',
                    'extensions' => [
                        [
                            'alias' => 'builder',
                            'configuration' => [
                                'name' => 'Relay::Node',
                                'configuration' => [
                                    'nodeInterfaceType' => 'NodeInterface',
                                    'idFetcher' => '@=service("overblog_graphql.test.resolver.global").idFetcher(value)',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'allObjects',
                    'type' => '[NodeInterface]',
                    'resolve' => '@=service("overblog_graphql.test.resolver.global").resolveAllObjects()',
                ],
            ],
        ], $object->toArray());
    }
}
