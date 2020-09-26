<?php
namespace App\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Exceptions\DefinitionException;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use \Tymon\JWTAuth\Facades\JWTAuth;


class CanAccessDirective extends BaseDirective implements FieldMiddleware, DefinedDirective
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<GRAPHQL
"""
Limit field access to users of a certain role.
"""
directive @canAccess(
  """
  The name of the role authorized users need to have.
  """
  requiredRole: String!
) on FIELD_DEFINITION
GRAPHQL;
    }

    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $originalResolver = $fieldValue->getResolver();

        return $next(
            $fieldValue->setResolver(
                function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($originalResolver) {
                    $requiredRole = $this->directiveArgValue('requiredRole');
                    $token=$context->request()->header('Authorization');
                    $token= str_replace('Bearer ', "" , $token);
                    JWTAuth::setToken($token);
                    $roles=JWTAuth::getPayload()->get("roles");
                    $roles=explode(",",$roles);


                    // Throw in case of an invalid schema definition to remind the developer
                    if ($requiredRole === null) {
                        throw new DefinitionException("Missing argument 'requiredRole' for directive '@canAccess'.");
                    }

                    if (
                        // Unauthenticated users don't get to see anything
                        ! $roles
                        // The user's role has to match have the required role
                        || !in_array($requiredRole,$roles) 
                    ) {
                        return null;
                    }

                    return $originalResolver($root, $args, $context, $resolveInfo);
                }
            )
        );
    }
}