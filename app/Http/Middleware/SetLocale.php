<?php

namespace App\Http\Middleware;

use App\Locale;
use Closure;

class SetLocale
{
    /**
     * @var Locale
     */
    protected $locale;

    /**
     * Create a new middleware instance.
     *
     * @param Locale $locale
     */
    public function __construct(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get a translator instance from the service container
        $translator = app('translator');
        // Try to get the locale from the query string if possible
        $locale = $request->query('locale');
        // Otherwise try to get the locale from the user's settings
        if (is_null($locale)) {
            /* @var \App\User $user */
            $user = $request->user();
            if (!is_null($user)) {
                $locale = $user->locale->id;
            }
        }
        // Finally try to get the locale from the 'Accept-Language' header
        if (is_null($locale)) {
            $locale = $request->header('Accept-Language');
        }
        // Overwrite the default only if a locale was found and if it is among
        // the supported locales
        if (!is_null($locale) && $this->locale->find($locale)) {
            $translator->setLocale($locale);
        }
        // Pass the request to the next handler
        /* @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);
        // Set the 'Content-Language' header
        $response->headers->set('Content-Language', $translator->locale());
        // Return the response to the previous handler
        return $response;
    }
}
