<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Deprecated;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Type
 * @GQL\FieldsBuilder(name="MyFieldsBuilder", configuration={"param1": "val1"})
 */
class DeprecatedBuilderAttributes
{
    /**
     * @GQL\Field(type="String!")
     */
    protected string $color;
}
