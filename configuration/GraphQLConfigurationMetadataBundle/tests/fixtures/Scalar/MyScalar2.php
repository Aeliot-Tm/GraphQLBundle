<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Scalar;

use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Scalar(name="MyScalar", scalarType="newObject('App\Type\EmailType')")
 * @GQL\Scalar(name="MyScalar2", scalarType="newObject('App\Type\EmailType')")
 */
#[GQL\Scalar(name: "MyScalar", scalarType: "newObject('App\Type\EmailType')")]
#[GQL\Scalar(name: "MyScalar2", scalarType: "newObject('App\Type\EmailType')")]
final class MyScalar2
{
}
