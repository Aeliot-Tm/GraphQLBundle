<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration\Traits;

use Overblog\GraphQLBundle\Configuration\FieldConfiguration;
use Symfony\Component\Validator\Constraints as Assert;

trait FieldsTrait
{
    /**
     * TODO: check if all fields have unique names.
     *
     * @Assert\Valid
     *
     * @var FieldConfiguration[]
     */
    protected array $fields = [];

    /**
     * @return FieldConfiguration[]
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
     * Get the latest defined field with given name
     */
    public function getField(string $name): ?FieldConfiguration
    {
        foreach (array_reverse($this->fields) as $field) {
            if ($field->getName() === $name) {
                return $field;
            }
        }

        return null;
    }

    public function addField(FieldConfiguration $fieldConfiguration): self
    {
        if (in_array($fieldConfiguration, $this->fields, true)) {
            return $this;
        }
        $fieldConfiguration->setParent($this);
        $this->fields[] = $fieldConfiguration;

        return $this;
    }

    /**
     * @param FieldConfiguration[] $fields
     */
    public function addFields(array $fields): self
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }

        return $this;
    }

    /**
     * @return FieldConfiguration[]
     */
    public function getChildren(): array
    {
        return $this->getFields();
    }
}
