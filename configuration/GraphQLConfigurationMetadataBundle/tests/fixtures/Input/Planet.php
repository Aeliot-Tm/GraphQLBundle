<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Input;

use Doctrine\ORM\Mapping as ORM;
use Overblog\GraphQLConfigurationMetadataBundle\Metadata as GQL;

/**
 * @GQL\Input
 * @GQL\Description("Planet Input type description")
 */
#[GQL\Input]
#[GQL\Description("Planet Input type description")]
final class Planet
{
    /**
     * @GQL\InputField(type="String!", defaultValue="Sun")
     */
    #[GQL\InputField(type: "String!")]
    public string $name = 'Sun';

    /**
     * @GQL\InputField(type="Int!")
     */
    #[GQL\InputField(type: "Int!")]
    public string $population;

    /**
     * @GQL\InputField
     */
    #[GQL\InputField]
    public string $description;

    /**
     * @GQL\InputField
     * @ORM\Column(type="integer", nullable=true)
     */
    #[GQL\InputField]
    // @phpstan-ignore-next-line
    public ?int $diameter;

    /**
     * @GQL\InputField
     * @ORM\Column(type="boolean")
     */
    #[GQL\InputField]
    public int $variable;

    // @phpstan-ignore-next-line
    public $dummy;

    /**
     * @GQL\InputField(defaultValue={})
     * @ORM\Column(type="text[]")
     */
    #[GQL\InputField]
    #[ORM\Column(type: "text[]")]
    public array $tags = [];
}
