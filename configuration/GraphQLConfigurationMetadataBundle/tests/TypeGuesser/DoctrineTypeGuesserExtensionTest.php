<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationMetadataBundle\Tests\TypeGuesser;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Exception;
use Overblog\GraphQLConfigurationMetadataBundle\ClassesTypesMap;
use Overblog\GraphQLConfigurationMetadataBundle\Reader\AnnotationReader;
use Overblog\GraphQLConfigurationMetadataBundle\Reader\AttributeReader;
use Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Guesser\Doctrine\LightsaberAnnotations;
use Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Guesser\Doctrine\LightsaberAttributes;
use Overblog\GraphQLConfigurationMetadataBundle\TypeGuesser\Extension\DoctrineTypeGuesserExtension;
use Overblog\GraphQLConfigurationMetadataBundle\TypeGuesser\TypeGuessingException;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DoctrineTypeGuesserExtensionTest extends WebTestCase
{
    // @phpstan-ignore-next-line
    protected $property;

    public function testGuessError(): void
    {
        $refClass = new ReflectionClass(__CLASS__);
        $doctrineGuesser = new DoctrineTypeGuesserExtension(
            new ClassesTypesMap(),
            new AnnotationReader(new DoctrineAnnotationReader()),
            new AttributeReader(),
        );

        try {
            // @phpstan-ignore-next-line
            $doctrineGuesser->guessType($refClass, $refClass);
        } catch (Exception $e) {
            $this->assertInstanceOf(TypeGuessingException::class, $e);
            $this->assertStringContainsString('Doctrine type guesser only apply to properties.', $e->getMessage());
        }

        try {
            $doctrineGuesser->guessType($refClass, $refClass->getProperty('property'));
        } catch (Exception $e) {
            $this->assertInstanceOf(TypeGuessingException::class, $e);
            $this->assertStringContainsString('No Doctrine ORM annotation found.', $e->getMessage());
        }
    }

    /**
     * @param class-string $className
     */
    protected function testGuessClass(string $className): void
    {
        $classesMap = [
            'Hero' => ['class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Guesser\Doctrine\Hero', 'type' => 'object'],
            'Crystal' => ['class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Guesser\Doctrine\Crystal', 'type' => 'object'],
            'Battle' => ['class' => 'Overblog\GraphQLConfigurationMetadataBundle\Tests\fixtures\Guesser\Doctrine\Battle', 'type' => 'object'],
        ];

        $doctrineMapping = [
            'text[]' => ['[String]', '[String]!'],
        ];

        $doctrineGuesser = new DoctrineTypeGuesserExtension(
            new ClassesTypesMap(null, $classesMap),
            new AnnotationReader(new DoctrineAnnotationReader()),
            new AttributeReader(),
            $doctrineMapping,
        );
        $refClass = new ReflectionClass($className);

        $result = [];
        foreach ($refClass->getProperties() as $refProperty) {
            $result[$refProperty->getName()] = $doctrineGuesser->guessType($refClass, $refProperty);
        }

        $expected = [
            'color' => 'String!',
            'text' => 'String!',
            'string' => 'String!',
            'size' => 'Int',
            'holders' => '[Hero!]',
            'creator' => 'Hero',
            'crystal' => 'Crystal',
            'battles' => '[Battle!]',
            'currentHolder' => 'Hero!',
            'tags' => '[String]!',
            'float' => 'Float!',
            'decimal' => 'Float!',
            'bool' => 'Boolean!',
            'boolean' => 'Boolean!',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGuessFromAnnotations(): void
    {
        $this->testGuessClass(LightsaberAnnotations::class);
    }

    public function testGuessFromAttributes(): void
    {
        $this->testGuessClass(LightsaberAttributes::class);
    }
}
