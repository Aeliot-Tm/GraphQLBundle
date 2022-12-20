<?php

declare(strict_types=1);

namespace Overblog\GraphQLBundle\Configuration;

interface Interfaced
{
    public function addInterface(string $interface): void;

    /**
     * @return string[]
     */
    public function getInterfaces(): array;

    /**
     * @param string[] $interfaces
     */
    public function setInterfaces(array $interfaces = []);
}
