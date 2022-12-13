<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Type;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Type
 * @GQL\FieldsBuilder(name="MyFieldsBuilder", configuration={"param1": "val1"})
 */
#[GQL\Type]
#[GQL\FieldsBuilder(name: 'MyFieldsBuilder', configuration: ['param1' => 'val1'])]
final class Crystal
{
    /**
     * @GQL\Field(type="String!")
     */
    #[GQL\Field(type: 'String!')]
    public string $color;
}
