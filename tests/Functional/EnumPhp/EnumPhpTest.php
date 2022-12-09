<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Tests\Functional\EnumPhp;

use Overblog\GraphQLBundle\Tests\Functional\TestCase;

/**
 * @requires PHP 8.1
 */
final class EnumPhpTest extends TestCase
{
    protected function setUp(): void
    {
        static::bootKernel(['test_case' => 'enumPhp']);
    }

    public static function resolveQueryEnum(): EnumPhp
    {
        return EnumPhp::VALUE2;
    }

    public static function resolveQueryEnumBacked(): EnumPhpBacked
    {
        return EnumPhpBacked::VALUE3;
    }

    public function testEnumSerializedToName(): void
    {
        $query = 'query { enum }';
        $result = $this->executeGraphQLRequest($query);

        self::assertIsArray($result);
        self::assertArrayNotHasKey('errors', $result, json_encode($result, JSON_THROW_ON_ERROR));
        self::assertArrayHasKey('data', $result);
        self::assertArrayHasKey('enum', $result['data']);
        self::assertEquals(EnumPhp::VALUE2->name, $result['data']['enum']);
    }

    public function testEnumBackedSerializedToName(): void
    {
        $query = 'query { enumBacked }';
        $result = $this->executeGraphQLRequest($query);

        self::assertIsArray($result);
        self::assertArrayNotHasKey('errors', $result, json_encode($result, JSON_THROW_ON_ERROR));
        self::assertArrayHasKey('data', $result);
        self::assertArrayHasKey('enumBacked', $result['data']);
        self::assertEquals(EnumPhpBacked::VALUE3->name, $result['data']['enumBacked']);
    }

    public static function resolveQueryEnumAsInput(mixed $enumParam = null, mixed $enumParam2 = null): string
    {
        return (EnumPhp::VALUE2 === $enumParam && EnumPhpBacked::VALUE3 === $enumParam2) ? 'OK' : 'KO';
    }

    public function testEnumLiteralParsedAsPhpEnum(): void
    {
        $query = 'query { enumParser(enum: VALUE2, enumBacked: VALUE3) }';

        $result = $this->executeGraphQLRequest($query);
        self::assertIsArray($result);
        self::assertArrayNotHasKey('errors', $result, json_encode($result, JSON_THROW_ON_ERROR));
        self::assertArrayHasKey('data', $result);
        self::assertArrayHasKey('enumParser', $result['data']);
        self::assertEquals('OK', $result['data']['enumParser']);
    }

    public function testEnumVariableParsedAsPhpEnum(): void
    {
        $query = 'query($enum: EnumPhp!, $enumBacked: EnumPhpBacked!) { enumParser(enum: $enum, enumBacked: $enumBacked) }';
        $result = $this->executeGraphQLRequest($query, [], null, ['enum' => EnumPhp::VALUE2->name, 'enumBacked' => EnumPhpBacked::VALUE3->name]);

        self::assertIsArray($result);
        self::assertArrayNotHasKey('errors', $result, json_encode($result, JSON_THROW_ON_ERROR));
        self::assertArrayHasKey('data', $result);
        self::assertArrayHasKey('enumParser', $result['data']);
        self::assertEquals('OK', $result['data']['enumParser']);
    }

    public function testEnumIntrospection(): void
    {
        $query = <<<'EOF'
            query {
              __schema {
                types {
                  name
                  enumValues {
                    name
                    description
                  }
                }
              }
            }
            EOF;
        $result = $this->executeGraphQLRequest($query);
        $types = $result['data']['__schema']['types'];
        $this->assertEquals($types[1]['name'], 'EnumPhp');
        $this->assertEquals($types[1]['enumValues'][0]['name'], 'VALUE1');
        $this->assertEquals($types[1]['enumValues'][1]['name'], 'VALUE2');
        $this->assertEquals($types[1]['enumValues'][2]['name'], 'VALUE3');
        $this->assertEquals($types[1]['enumValues'][2]['description'], 'The value 3');

        $this->assertEquals($types[2]['name'], 'EnumPhpBacked');
        $this->assertEquals($types[2]['enumValues'][0]['name'], 'VALUE1');
        $this->assertEquals($types[2]['enumValues'][1]['name'], 'VALUE2');
        $this->assertEquals($types[2]['enumValues'][2]['name'], 'VALUE3');
    }
}
