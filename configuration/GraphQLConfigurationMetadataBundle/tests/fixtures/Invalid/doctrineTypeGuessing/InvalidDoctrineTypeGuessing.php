<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Invalid\doctrineTypeGuessing;

use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Type
 */
#[GQL\Type]
final class InvalidDoctrineTypeGuessing
{
    /**
     * @ORM\Column(type="invalidType")
     * @GQL\Field
     *
     * @var mixed
     */
    #[GQL\Field]
    public $myRelation;
}
