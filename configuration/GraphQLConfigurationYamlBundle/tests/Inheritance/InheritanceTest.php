<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationYamlBundle\Tests\Inheritance;

use Overblog\GraphQLConfigurationYamlBundle\ConfigurationYamlParser;
use Overblog\GraphQLConfigurationYamlBundle\Processor\InheritanceProcessor;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class InheritanceTest extends WebTestCase
{
    /**
     * @return array<string,mixed>
     */
    protected function getInheritanceConfiguration(): array
    {
        $parser = new ConfigurationYamlParser([__DIR__.DIRECTORY_SEPARATOR.'../fixtures/inheritance']);

        return $parser->getConfigurationArray();
    }

    public function testObjectInheritance(): void
    {
        $config = $this->getInheritanceConfiguration();

        $this->assertArrayHasKey('QueryMain', $config);
        // TODO(mcg-web): understand why travis fields order diffed from local test
        $this->assertEquals(
            [
                'type' => 'object',
                InheritanceProcessor::INHERITS_KEY => ['QueryFoo', 'QueryBar', 'QueryHelloWord'],
                'decorator' => false,
                'config' => [
                    'fields' => [
                        'sayHello' => [
                            'type' => 'String',
                            'extensions' => [],
                        ],
                        'period' => [
                            'type' => 'Period',
                            'extensions' => [],
                        ],
                        'bar' => [
                            'type' => 'String',
                            'extensions' => [],
                        ],
                    ],
                    'interfaces' => ['QueryHelloWord'],
                    'builders' => [],
                    'extensions' => [],
                ],
            ],
            $config['QueryMain']
        );
    }

    public function testEnumInheritance(): void
    {
        $config = $this->getInheritanceConfiguration();
        $this->assertArrayHasKey('Period', $config);
        $this->assertSame(
            [
                'type' => 'enum',
                InheritanceProcessor::INHERITS_KEY => ['Day', 'Month', 'Year'],
                'decorator' => false,
                'config' => [
                    'values' => [
                        'DAY' => ['value' => 1, 'extensions' => []],
                        'MONTH' => ['value' => 2, 'extensions' => []],
                        'YEAR' => ['value' => 3, 'extensions' => []],
                    ],
                    'extensions' => [],
                ],
            ],
            $config['Period']
        );
    }

    public function testRelayInheritance(): void
    {
        $config = $this->getInheritanceConfiguration();
        $this->assertArrayHasKey('ChangeEventInput', $config);
        $this->assertSame(
            [
                'type' => 'input-object',
                InheritanceProcessor::INHERITS_KEY => ['AddEventInput'],
                'decorator' => false,
                'config' => [
                    'name' => 'ChangeEventInput',
                    'fields' => [
                        'title' => ['type' => 'String!', 'extensions' => []],
                        'clientMutationId' => ['type' => 'String', 'extensions' => []],
                        'id' => ['type' => 'ID!', 'extensions' => []],
                    ],
                    'extensions' => [],
                ],
            ],
            $config['ChangeEventInput']
        );
    }

    public function testDecoratorTypeShouldRemovedFromFinalConfig(): void
    {
        $config = $this->getInheritanceConfiguration();
        $this->assertArrayNotHasKey('QueryBarDecorator', $config);
        $this->assertArrayNotHasKey('QueryFooDecorator', $config);
    }

    public function testDecoratorInterfacesShouldMerge(): void
    {
        $config = $this->getInheritanceConfiguration();
        $this->assertArrayHasKey('ABCDE', $config);
        $this->assertSame(
            [
                'type' => 'object',
                InheritanceProcessor::INHERITS_KEY => ['DecoratorA', 'DecoratorB', 'DecoratorD'],
                'decorator' => false,
                'config' => [
                    'interfaces' => ['A', 'AA', 'B', 'C', 'D', 'E'],
                    'fields' => [
                        'a' => [
                            'type' => 'String',
                            'extensions' => [],
                        ],
                        'aa' => [
                            'type' => 'String',
                            'extensions' => [],
                        ],
                        'b' => [
                            'type' => 'String',
                            'extensions' => [],
                        ],
                        'c' => [
                            'type' => 'String',
                            'extensions' => [],
                        ],
                        'd' => [
                            'type' => 'String',
                            'extensions' => [],
                        ],
                        'e' => [
                            'type' => 'String',
                            'extensions' => [],
                        ],
                    ],
                    'builders' => [],
                    'extensions' => [],
                ],
            ],
            $config['ABCDE']
        );
    }
}
