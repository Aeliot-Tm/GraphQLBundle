<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Metadata;

use Attribute;

/**
 * Annotation for GraphQL input field.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"PROPERTY"})
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class InputField extends Metadata
{
    /**
     * The input field name.
     */
    public ?string $name;

    /**
     * Input Field Type.
     */
    public ?string $type;

    /**
     * Default value
     *
     * @var mixed
     */
    public $defaultValue;

    public bool $isDefaultSet = false;

    /**
     * @param string|null $name         The GraphQL name of the field
     * @param string|null $type         The GraphQL type of the field
     * @param mixed       $defaultValue The input field default value
     */
    public function __construct(
        string $name = null,
        string $type = null,
        $defaultValue = null
    ) {
        $this->name = $name;
        $this->type = $type;
        if (func_num_args() > 2) {
            $this->defaultValue = $defaultValue;
            $this->isDefaultSet = true;
        }
    }
}
