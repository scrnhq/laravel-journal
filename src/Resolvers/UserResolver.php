<?php

namespace Scrn\Journal\Resolvers;

class UserResolver implements \Scrn\Journal\Contracts\UserResolver
{
    public static function resolve()
    {
        return auth()->user();
    }
}
