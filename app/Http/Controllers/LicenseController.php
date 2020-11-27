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

namespace App\Http\Controllers;

use App\Models\Account;
use App\Utils\CurlUtils;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use stdClass;

class LicenseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Claim a white label license.
     *
     * @return Response
     *
     * @OA\Get(
     *      path="/api/v1/claim_license",
     *      operationId="getClaimLicense",
     *      tags={"claim_license"},
     *      summary="Attempts to claim a white label license",
     *      description="Attempts to claim a white label license",
     *      @OA\Parameter(ref="#/components/parameters/X-Api-Secret"),
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(
     *          name="license_key",
     *          in="query",
     *          description="The license hash",
     *          example="d87sh-s755s-s7d76-sdsd8",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="product_id",
     *          in="query",
     *          description="The ID of the product purchased.",
     *          example="1",
     *          required=true,
     *          @OA\Schema(
     *              type="string",
     *              format="string",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success!",
     *          @OA\Header(header="X-MINIMUM-CLIENT-VERSION", ref="#/components/headers/X-MINIMUM-CLIENT-VERSION"),
     *          @OA\Header(header="X-RateLimit-Remaining", ref="#/components/headers/X-RateLimit-Remaining"),
     *          @OA\Header(header="X-RateLimit-Limit", ref="#/components/headers/X-RateLimit-Limit"),
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationError"),

     *       ),
     *       @OA\Response(
     *           response="default",
     *           description="Unexpected Error",
     *           @OA\JsonContent(ref="#/components/schemas/Error"),
     *       ),
     *     )
     */
    public function index()
    {

        /* Catch claim license requests */
        if (config('ninja.environment') == 'selfhost' && request()->has('license_key')) {
            $license_key = request()->input('license_key');
            $product_id = 3;

            $url = config('ninja.license_url')."/claim_license?license_key={$license_key}&product_id={$product_id}&get_date=true";
            $data = trim(CurlUtils::get($url));

            if ($data == Account::RESULT_FAILURE) {
                $error = [
                    'message' => trans('texts.invalid_white_label_license'),
                    'errors' => new stdClass,
                ];

                return response()->json($error, 400);
            } elseif ($data) {
                $date = date_create($data)->modify('+1 year');

                if ($date < date_create()) {
                    $error = [
                        'message' => trans('texts.invalid_white_label_license'),
                        'errors' => new stdClass,
                    ];

                    return response()->json($error, 400);
                } else {
                    $account = auth()->user()->company()->account;

                    $account->plan_term = Account::PLAN_TERM_YEARLY;
                    $account->plan_paid = $data;
                    $account->plan_expires = $date->format('Y-m-d');
                    $account->plan = Account::PLAN_WHITE_LABEL;
                    $account->save();

                    $error = [
                        'message' => trans('texts.bought_white_label'),
                        'errors' => new stdClass,
                    ];

                    return response()->json($error, 200);
                }
            } else {
                $error = [
                        'message' => trans('texts.white_label_license_error'),
                        'errors' => new stdClass,
                    ];

                return response()->json($error, 400);
            }
        }

        $error = [
            'message' => 'Invalid license, or invalid environment '.config('ninja.environment'),
            'errors' => new stdClass,
        ];

        return response()->json($error, 400);
    }
}
