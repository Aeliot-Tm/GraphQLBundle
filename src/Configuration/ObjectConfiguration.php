<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

use Overblog\GraphQLBundle\Configuration\Traits\FieldsTrait;

/**
 * @implements FieldsOwner<FieldConfiguration>
 */
class ObjectConfiguration extends RootTypeConfiguration implements FieldsOwner, Interfaced
{
    use FieldsTrait;

    /**
     * TODO: check if the list of interfaces have only unique values.
     *
     * @var string[]
     */
    protected array $interfaces = [];

    protected ?string $isTypeOf = null;
    protected ?string $resolveField = null;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function create(string $name): ObjectConfiguration
    {
        return new static($name);
    }

    public function getGraphQLType(): string
    {
        return self::TYPE_OBJECT;
    }

    /** @return string[] */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    public function setInterfaces(array $interfaces = []): self
    {
        $this->interfaces = $interfaces;

        return $this;
    }

    public function addInterface(string $interface): void
    {
        if (!in_array($interface, $this->interfaces)) {
            $this->interfaces[] = $interface;
        }
    }

    public function getIsTypeOf(): ?string
    {
        return $this->isTypeOf;
    }

    public function setIsTypeOf(string $isTypeOf): self
    {
        $this->isTypeOf = $isTypeOf;

        return $this;
    }

    public function getResolveField(): ?string
    {
        return $this->resolveField;
    }

    public function setResolveField(string $resolveField): self
    {
        $this->resolveField = $resolveField;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'interfaces' => $this->interfaces,
            'isTypeOf' => $this->isTypeOf,
            'resolveField' => $this->resolveField,
            'fields' => array_map(fn (FieldConfiguration $field) => $field->toArray(), $this->fields),
            'extensions' => $this->getExtensionsArray(),
        ]);
    }
}
