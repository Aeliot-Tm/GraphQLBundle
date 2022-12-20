<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @implements FieldsOwner<InputFieldConfiguration>
 */
class InputConfiguration extends RootTypeConfiguration implements FieldsOwner
{
    /**
     * @Assert\Valid
     *
     * @var InputFieldConfiguration[]
     */
    protected array $fields = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $name): InputConfiguration
    {
        return new static($name);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_INPUT;
    }

    /**
     * @return InputFieldConfiguration[]
     */
    public function getFields(bool $indexedByName = false): array
    {
        if (!$indexedByName) {
            return $this->fields;
        }

        $fields = [];
        foreach ($this->fields as $field) {
            $fields[$field->getName()] = $field;
        }

        return $fields;
    }

    /**
     * Get the latest defined input field with given name
     */
    public function getField(string $name): ?InputFieldConfiguration
    {
        foreach (array_reverse($this->fields) as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        return null;
    }

    public function addField(InputFieldConfiguration $fieldConfiguration): self
    {
        if (in_array($fieldConfiguration, $this->fields, true)) {
            return $this;
        }
        $fieldConfiguration->setParent($this);
        $this->fields[] = $fieldConfiguration;

        return $this;
    }

    /** @param InputFieldConfiguration[] $fields */
    public function addFields(array $fields): self
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    /**
     * @return InputFieldConfiguration[]
     */
    public function getChildren(): array
    {
        return $this->getFields();
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'fields' => array_map(fn (InputFieldConfiguration $field) => $field->toArray(), $this->fields),
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
