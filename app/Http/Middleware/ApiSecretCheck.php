<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use stdClass;

class ApiSecretCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! config('ninja.api_secret')) {
            return $next($request);
        }

        if ($request->header('X-API-SECRET') && ($request->header('X-API-SECRET') == config('ninja.api_secret'))) {
            return $next($request);
        } else {
            $error = [
                'message' => 'Invalid secret',
                'errors' => new stdClass,
            ];

            return response()
            ->json($error, 403)
            ->header('X-App-Version', config('ninja.app_version'))
            ->header('X-Minimum-Client-Version', config('ninja.minimum_client_version'));
        }
    }
}
