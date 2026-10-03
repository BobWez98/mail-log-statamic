<?php

declare(strict_types=1);

namespace BobWez98\MailLogStatamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\User;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeMailLog
{
    /** @param Closure(Request): Response $next */
    public function handle(Request $request, Closure $next): Response
    {
        $user = User::current();

        abort_unless(
            $user && ($user->isSuper() || $user->hasPermission('view mail log')),
            403,
        );

        return $next($request);
    }
}
