<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\Transformer;

use Exception;
use Generator;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
// TODO check merge conflict solving
use Overblog\GraphQLBundle\Definition\Type\PhpEnumType;
use Overblog\GraphQLBundle\Tests\Functional\EnumPhp\EnumPhp;
use Overblog\GraphQLConfigurationMetadataBundle\ClassesTypesMap;
use Overblog\GraphQLConfigurationMetadataBundle\Transformer\ArgumentsTransformer;
use Overblog\GraphQLConfigurationMetadataBundle\Transformer\InvalidArgumentError;
use Overblog\GraphQLConfigurationMetadataBundle\Transformer\InvalidArgumentsError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use function class_exists;

final class ArgumentsTransformerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(Validation::class)) {
            $this->markTestSkipped('Symfony validator component is not installed');
        }
    }

    private function getTransformer(array $classesMap = [], ConstraintViolationList $validateReturn = null): ArgumentsTransformer
    {
        $validator = $this->createMock(RecursiveValidator::class);
        $validator->method('validate')->willReturn($validateReturn ?? new ConstraintViolationList());

        return new ArgumentsTransformer($validator, new ClassesTypesMap(null, $classesMap));
    }

    public function getResolveInfo(array $types): ResolveInfo
    {
        /** @var ResolveInfo $info */
        $info = $this->getMockBuilder(ResolveInfo::class)->disableOriginalConstructor()->getMock();
        $info->schema = new Schema(['types' => $types]);

        return $info;
    }

    public static function getTypes(): array
    {
        $t1 = new InputObjectType([
            'name' => 'InputType1',
            'fields' => [
                'field1' => Type::string(),
                'field2' => Type::int(),
                'field3' => Type::boolean(),
            ],
        ]);

        $t3 = new PhpEnumType([
            'name' => 'Enum1',
            'values' => ['op1' => 1, 'op2' => 2, 'op3' => 3],
        ]);

        $t2 = new InputObjectType([
            'name' => 'InputType2',
            'fields' => [
                'field1' => Type::listOf($t1),
                'field2' => $t3,
                'field3' => Type::nonNull($t3),
            ],
        ]);

        $t4 = new InputObjectType([
            'name' => 'InputType3',
            'fields' => [
                'field1' => Type::nonNull(Type::listOf($t1)),
            ],
        ]);

        $types = [$t1, $t2, $t3, $t4];

        if (PHP_VERSION_ID >= 80100) {
            $t5 = new PhpEnumType([
                'name' => 'EnumPhp',
                'enumClass' => EnumPhp::class,
                'values' => [
                    'VALUE1' => 'VALUE1',
                    'VALUE2' => 'VALUE2',
                    'VALUE3' => 'VALUE3',
                ],
            ]);
            $types[] = $t5;
        }

        return $types;
    }

    public function testPopulating(): void
    {
        $transformer = $this->getTransformer([
            'InputType1' => ['type' => 'input', 'class' => InputType1::class],
            'InputType2' => ['type' => 'input', 'class' => InputType2::class],
            'InputType3' => ['type' => 'input', 'class' => InputType3::class],
        ]);

        $info = $this->getResolveInfo(self::getTypes());

        $data = [
            'field1' => 'hello',
            'field2' => 12,
            'field3' => true,
        ];

        $res = $transformer->getInstanceAndValidate('InputType1', $data, $info, 'input1');

        $this->assertInstanceOf(InputType1::class, $res);
        $this->assertEquals($data['field1'], $res->field1);
        $this->assertEquals($data['field2'], $res->field2);
        $this->assertEquals($data['field3'], $res->field3);

        $data = [
            'field1' => [
                ['field1' => 'hello2', 'field2' => 2, 'field3' => false],
                ['field1' => 'world2'],
            ],
            'field2' => 3,
        ];

        $res = $transformer->getInstanceAndValidate('InputType2', $data, $info, 'input2');

        $this->assertInstanceOf(InputType2::class, $res);
        $this->assertIsArray($res->field1);
        $this->assertArrayHasKey(0, $res->field1);
        $this->assertArrayHasKey(1, $res->field1);
        $this->assertInstanceOf(InputType1::class, $res->field1[0]);
        $this->assertInstanceOf(InputType1::class, $res->field1[1]);

        // InputType3
        $data = [
            // [InputType1]!
            'field1' => [
                ['field1' => 'string 1', 'field2' => 1, 'field3' => true],
                ['field1' => 'string 2', 'field2' => 2, 'field3' => false],
            ],
        ];

        $res = $transformer->getInstanceAndValidate('InputType3', $data, $info, 'input');

        $this->assertInstanceOf(InputType3::class, $res);
        $this->assertArrayHasKey(0, $res->field1);
        $this->assertInstanceOf(InputType1::class, $res->field1[0]);
        $this->assertEquals($data['field1'][0]['field1'], $res->field1[0]->field1);
        $this->assertEquals($data['field1'][0]['field2'], $res->field1[0]->field2);
        $this->assertEquals($data['field1'][0]['field3'], $res->field1[0]->field3);
        $this->assertArrayHasKey(1, $res->field1);
        $this->assertInstanceOf(InputType1::class, $res->field1[1]);
        $this->assertEquals($data['field1'][1]['field1'], $res->field1[1]->field1);
        $this->assertEquals($data['field1'][1]['field2'], $res->field1[1]->field2);
        $this->assertEquals($data['field1'][1]['field3'], $res->field1[1]->field3);

        $res = $transformer->getInstanceAndValidate('Enum1', 2, $info, 'enum1');

        $this->assertEquals(2, $res);

        $transformer = $this->getTransformer([
            'InputType1' => ['type' => 'input', 'class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\Transformer\InputType1'],
            'InputType2' => ['type' => 'input', 'class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\Transformer\InputType2'],
            'Enum1' => ['type' => 'enum', 'class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\Transformer\Enum1'],
        ]);

        $res = $transformer->getInstanceAndValidate('Enum1', 2, $info, 'enum1');
        $this->assertInstanceOf(Enum1::class, $res);
        $this->assertEquals(2, $res->value);

        if (PHP_VERSION_ID >= 80100) {
            $res = $transformer->getInstanceAndValidate('EnumPhp', EnumPhp::VALUE2, $info, 'enumPhp');
            $this->assertInstanceOf(EnumPhp::class, $res);
            $this->assertEquals(EnumPhp::VALUE2, $res);
        }

        $mapping = ['input1' => 'InputType1', 'input2' => 'InputType2', 'enum1' => 'Enum1', 'int1' => 'Int!', 'string1' => 'String!'];
        $data = [
            'input1' => ['field1' => 'hello', 'field2' => 12, 'field3' => true],
            'input2' => ['field1' => [['field1' => 'hello1'], ['field1' => 'hello2']], 'field2' => 12],
            'enum1' => 2,
            'int1' => 14,
            'string1' => 'test_string',
        ];

        $res = $transformer->getArguments($mapping, $data, $info);
        $this->assertInstanceOf(InputType1::class, $res[0]);
        $this->assertInstanceOf(InputType2::class, $res[1]);
        $this->assertInstanceOf(Enum1::class, $res[2]);
        $this->assertCount(2, $res[1]->field1);
        $this->assertIsInt($res[3]);
        $this->assertEquals('test_string', $res[4]);

        $data = [];
        $res = $transformer->getInstanceAndValidate('InputType1', $data, $info, 'input1');
        $this->assertInstanceOf(InputType1::class, $res);

        $res = $transformer->getInstanceAndValidate('InputType2', ['field3' => 'enum1'], $info, 'input2');
        $this->assertInstanceOf(Enum1::class, $res->field3);
        $this->assertEquals('enum1', $res->field3->value);
    }

    public function testRaisedErrors(): void
    {
        $violation = new ConstraintViolation('validation_error', 'validation_error', [], 'invalid', 'field2', 'invalid');
        $builder = $this->getTransformer([
            'InputType1' => ['type' => 'input', 'class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\Transformer\InputType1'],
            'InputType2' => ['type' => 'input', 'class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\Transformer\InputType2'],
        ], new ConstraintViolationList([$violation]));

        $mapping = ['input1' => 'InputType1', 'input2' => 'InputType2'];
        $data = [
            'input1' => ['field1' => 'hello', 'field2' => 12, 'field3' => true],
            'input2' => ['field1' => [['field1' => 'hello1'], ['field1' => 'hello2']], 'field2' => 12],
        ];

        try {
            $builder->getArguments($mapping, $data, $this->getResolveInfo(self::getTypes()));
            $this->fail('When input data validation fail, it should raise an Overblog\GraphQLBundle\Error\InvalidArgumentsError exception');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidArgumentsError::class, $e);
            $first = $e->getErrors()[0];
            $this->assertInstanceOf(InvalidArgumentError::class, $first);
            $this->assertEquals($violation, $first->getErrors()->get(0));
            $this->assertEquals('input1', $first->getName());

            $expected = [
                'input1' => [[
                    'path' => 'field2',
                    'message' => 'validation_error',
                    'code' => null,
                ]],
                'input2' => [[
                    'path' => 'field2',
                    'message' => 'validation_error',
                    'code' => null,
                ]],
            ];

            $this->assertEquals($expected, $e->toState());
        }
    }

    /**
     * Validate array of input values annotated with Constraints, for example [InputTypeWithConstraints!].
     */
    public function testRaisedErrorsForMultipleInputs(): void
    {
        $violation1 = new ConstraintViolation(
            'validation_error1',
            'validation_error',
            [],
            'invalid',
            'field2',
            'invalid'
        );
        $violation2 = new ConstraintViolation(
            'validation_error2',
            'validation_error',
            [],
            'invalid',
            'field2',
            'invalid'
        );

        $validator = $this->createMock(RecursiveValidator::class);
        $validator->method('validate')->willReturnOnConsecutiveCalls(
            new ConstraintViolationList([$violation1]),
            new ConstraintViolationList([$violation2])
        );
        $builder = new ArgumentsTransformer($validator, new ClassesTypesMap(null, [
            'InputType1' => ['type' => 'input', 'class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\Transformer\InputType1'],
            'InputType2' => ['type' => 'input', 'class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\Transformer\InputType2'],
        ]));

        $mapping = ['input1' => '[InputType1]', 'input2' => '[InputType2]'];
        $data = [
            'input1' => [['field1' => 'hello', 'field2' => 12, 'field3' => true]],
            'input2' => [['field1' => [['field1' => 'hello1'], ['field1' => 'hello2']], 'field2' => 12]],
        ];

        try {
            $builder->getArguments($mapping, $data, $this->getResolveInfo(self::getTypes()));
            $this->fail("When input data validation fail, it should raise an Overblog\GraphQLConfigurationMetadataBundle\Transformer\InvalidArgumentsError exception");
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidArgumentsError::class, $e);
            /** @var InvalidArgumentsError $e */
            [$first, $second] = $e->getErrors();
            $this->assertInstanceOf(InvalidArgumentError::class, $first);
            $this->assertEquals($violation1, $first->getErrors()->get(0));
            $this->assertEquals('input1', $first->getName());
            $this->assertEquals($violation2, $second->getErrors()->get(0));
            $this->assertEquals('input2', $second->getName());

            $expected = [
                'input1' => [
                    [
                        'path' => 'field2',
                        'message' => 'validation_error1',
                        'code' => null,
                    ],
                ],
                'input2' => [
                    [
                        'path' => 'field2',
                        'message' => 'validation_error2',
                        'code' => null,
                    ],
                ],
            ];

            $this->assertEquals($e->toState(), $expected);
        }
    }

    public function getWrappedInputObject(): Generator
    {
        $inputObject = new InputObjectType([
            'name' => 'InputType1',
            'fields' => [
                'field1' => Type::string(),
                'field2' => Type::int(),
                'field3' => Type::boolean(),
            ],
        ]);
        yield [$inputObject, false];
        yield [new NonNull($inputObject), false];
    }

    /** @dataProvider getWrappedInputObject */
    public function testInputObjectWithWrappingType(Type $type): void
    {
        $transformer = $this->getTransformer(
            [
                'InputType1' => ['type' => 'input', 'class' => InputType1::class],
            ],
            new ConstraintViolationList()
        );
        $info = $this->getResolveInfo(self::getTypes());

        $data = ['field1' => 'hello', 'field2' => 12, 'field3' => true];

        $inputValue = $transformer->getInstanceAndValidate($type->toString(), $data, $info, 'input1');

        /** @var InputType1 $inputValue */
        $this->assertInstanceOf(InputType1::class, $inputValue);
        $this->assertEquals($data['field1'], $inputValue->field1);
        $this->assertEquals($data['field2'], $inputValue->field2);
        $this->assertEquals($data['field3'], $inputValue->field3);
    }

    public function getWrappedInputObjectList(): Generator
    {
        $inputObject = new InputObjectType([
            'name' => 'InputType1',
            'fields' => [
                'field1' => Type::string(),
                'field2' => Type::int(),
                'field3' => Type::boolean(),
            ],
        ]);
        yield [new ListOfType($inputObject)];
        yield [new ListOfType(new NonNull($inputObject))];
        yield [new NonNull(new ListOfType($inputObject))];
        yield [new NonNull(new ListOfType(new NonNull($inputObject)))];
    }

    /** @dataProvider getWrappedInputObjectList */
    public function testInputObjectWithWrappingTypeList(Type $type): void
    {
        $transformer = $this->getTransformer(
            ['InputType1' => ['type' => 'input', 'class' => InputType1::class]],
            new ConstraintViolationList()
        );
        $info = $this->getResolveInfo(self::getTypes());

        $data = ['field1' => 'hello', 'field2' => 12, 'field3' => true];

        $inputValue = $transformer->getInstanceAndValidate($type->toString(), [$data], $info, 'input1');
        $inputValue = reset($inputValue);

        /** @var InputType1 $inputValue */
        $this->assertInstanceOf(InputType1::class, $inputValue);
        $this->assertEquals($data['field1'], $inputValue->field1);
        $this->assertEquals($data['field2'], $inputValue->field2);
        $this->assertEquals($data['field3'], $inputValue->field3);
    }
}
