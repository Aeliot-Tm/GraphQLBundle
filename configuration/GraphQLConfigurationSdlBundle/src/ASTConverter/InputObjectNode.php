<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationSdlBundle\ASTConverter;

final class InputObjectNode extends ObjectNode
{
    protected const TYPENAME = 'input-object';
}
