<?php

declare(strict_types=1);

namespace Overblog\GraphQLConfigurationSdlBundle\ASTConverter;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\Node;
use GraphQL\Utils\AST;
use Overblog\GraphQLBundle\Configuration\ExtensionConfiguration;

class Extensions
{
    public static function get(Node $node): array
    {
        $extensions = [];
        foreach ($node->directives ?? [] as /** @var DirectiveNode $directiveDef */ $directiveDef) {
            if ('ext' === $directiveDef->name->value) {
                $name = $directiveDef->arguments[0]->value->value;
                $configuration = AST::valueFromASTUntyped($directiveDef->arguments[1]->value);
                $extensions[] = new ExtensionConfiguration($name, $configuration);
            }
        }

        return $extensions;
    }
}
