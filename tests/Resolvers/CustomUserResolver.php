<?php

namespace Scrn\Journal\Tests\Resolvers;

use Scrn\Journal\Contracts\UserResolver;

class CustomUserResolver implements UserResolver
{
    /**
     * Resolve the User.
     *
     * @return mixed|null
     */
    public static function resolve()
    {
        return null;
    }
}
