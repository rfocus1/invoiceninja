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


namespace App\Http\Requests\Setup;

use App\Http\Requests\Request;

class CheckMailRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; /* Return something that will check if setup has been completed, like Ninja::hasCompletedSetup() */
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        info($this->driver);

        return [
            'driver' => ['required', 'in:smtp,mail,sendmail,log'],
            'from_name' => ['required_unless:driver,log'],
            'from_address' => ['required_unless:driver,log'],
            'username' => ['required_unless:driver,log'],
            'host' => ['required_unless:driver,log'],
            'port' => ['required_unless:driver,log'],
            'encryption' => ['required_unless:driver,log'],
            'password' => ['required_unless:driver,log'],
        ];
    }
}
