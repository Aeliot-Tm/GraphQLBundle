<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Transformer;

use GraphQL\Type\Definition\EnumType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Overblog\GraphQLBundle\Definition\Type\PhpEnumType;
use Overblog\GraphQLConfigurationMetadataBundle\ClassesTypesMap;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function array_map;
use function count;
use function is_array;
use function is_object;
use function sprintf;
use function strlen;
use function substr;

final class ArgumentsTransformer
{
    private PropertyAccessor $accessor;
    private ?ValidatorInterface $validator;
    private ClassesTypesMap $classesTypesMap;

    /**
     * FIXME: parameters with default value MUST be after parameters without default values.
     *
     * @see https://www.php.net/manual/en/functions.arguments.php#functions.arguments.default Example #7 Incorrect usage of default function arguments
     */
    public function __construct(ValidatorInterface $validator = null, ClassesTypesMap $classesTypesMap)
    {
        $this->validator = $validator;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->classesTypesMap = $classesTypesMap;
    }

    /**
     * Get the PHP class for a given input or enum type.
     *
     * @return object|null
     */
    private function getTypeClassInstance(string $type)
    {
        $className = $this->classesTypesMap->resolveClass($type);

        return null !== $className ? new $className() : null;
    }

    /**
     * Extract given type from Resolve Info.
     */
    private function getType(string $type, ResolveInfo $info): ?Type
    {
        return $info->schema->getType($type);
    }

    /**
     * Populate an object based on type with given data.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    private function populateObject(Type $type, $data, bool $multiple, ResolveInfo $info)
    {
        if (null === $data) {
            return null;
        }

        if ($type instanceof NonNull) {
            $type = $type->getWrappedType();
        }

        if ($multiple) {
            return array_map(function ($data) use ($type, $info) {
                return $this->populateObject($type, $data, false, $info);
            }, $data);
        }

        if ($type instanceof EnumType) {
            /** Enum based on PHP Enum are already processed by PhpEnumType */
            if ($type instanceof PhpEnumType && $type->isEnumPhp()) { /** @phpstan-ignore-line */
                return $data;
            }
            $instance = $this->getTypeClassInstance($type->name);
            if ($instance) {
                $this->accessor->setValue($instance, 'value', $data);

                return $instance;
            }

            return $data;
        }

        if ($type instanceof InputObjectType) {
            $instance = $this->getTypeClassInstance($type->name);
            if (!$instance) {
                return $data;
            }

            $fields = $type->getFields();

            foreach ($fields as $name => $field) {
                $fieldData = $this->accessor->getValue($data, sprintf('[%s]', $name));
                $fieldType = $field->getType();

                if ($fieldType instanceof NonNull) {
                    $fieldType = $fieldType->getWrappedType();
                }

                if ($fieldType instanceof ListOfType) {
                    $fieldValue = $this->populateObject($fieldType->getWrappedType(), $fieldData, true, $info);
                } else {
                    $fieldValue = $this->populateObject($fieldType, $fieldData, false, $info);
                }

                $this->accessor->setValue($instance, $name, $fieldValue);
            }

            return $instance;
        }

        return $data;
    }

    /**
     * Given a GraphQL type and an array of data, populate corresponding object recursively
     * using annoted classes.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function getInstanceAndValidate(string $argType, $data, ResolveInfo $info, string $argName)
    {
        $isRequired = '!' === $argType[strlen($argType) - 1];
        $isMultiple = '[' === $argType[0];
        $isStrictMultiple = false;
        if ($isMultiple) {
            $isStrictMultiple = '!' === $argType[strpos($argType, ']') - 1];
        }

        $endIndex = ($isRequired ? 1 : 0) + ($isMultiple ? 1 : 0) + ($isStrictMultiple ? 1 : 0);
        $type = substr($argType, $isMultiple ? 1 : 0, $endIndex > 0 ? -$endIndex : strlen($argType));

        $result = $this->populateObject($this->getType($type, $info), $data, $isMultiple, $info);

        if (null !== $this->validator) {
            $errors = new ConstraintViolationList();
            if (is_object($result)) {
                $errors = $this->validator->validate($result);
            }
            if (is_array($result) && $isMultiple) {
                foreach ($result as $element) {
                    if (is_object($element)) {
                        $errors->addAll(
                            $this->validator->validate($element)
                        );
                    }
                }
            }

            if (count($errors) > 0) {
                throw new InvalidArgumentError($argName, $errors);
            }
        }

        return $result;
    }

    /**
     * Transform a list of arguments into their corresponding php class and validate them.
     *
     * @param mixed $data
     *
     * @return array
     */
    public function getArguments(array $mapping, $data, ResolveInfo $info)
    {
        $args = [];
        $exceptions = [];

        foreach ($mapping as $name => $type) {
            try {
                $value = $this->getInstanceAndValidate($type, $data[$name], $info, $name);
                $args[] = $value;
            } catch (InvalidArgumentError $exception) {
                $exceptions[] = $exception;
            }
        }

        if (!empty($exceptions)) {
            throw new InvalidArgumentsError($exceptions);
        }

        return $args;
    }
}
