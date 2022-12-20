<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

/**
 * @template-covariant T of object
 */
interface FieldsOwner
{
    /**
     * @return array<int, object>
     *
     * @phpstan-return T[]
     */
    public function getFields(bool $indexedByName = false): array;

    /**
     * @param object[] $fields
     *
     * @phpstan-param T[] $fields
     */
    public function addFields(array $fields);
}
