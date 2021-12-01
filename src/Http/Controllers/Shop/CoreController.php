<?php

namespace Webkul\API\Http\Controllers\Shop;

use Illuminate\Http\Request;

class CoreController extends Controller
{
    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getConfig()
    {
        $configValues = [];

        foreach (explode(',', request()->input('_config')) as $config) {
            $configValues[$config] = core()->getConfigData($config);
        }
        
        return response()->json([
            'data' => $configValues,
        ]);
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCountryStateGroup()
    {
        $countyStates = [];
        foreach (core()->groupedStatesByCountries() as $country_code => $states) {
            $country = app('Webkul\Core\Repositories\CountryRepository')->findOneByField('code', $country_code);
            
            $countyStates[] = [
                'country_id'        => $country['id'],
                'name'              => $country['name'],
                'country_code'      => $country['code'],
                'isStateRequired'   => true,
                'isZipOptional'     => false,
                'states'            => $states,
            ];
        }

        return response()->json([
            'data' => $countyStates,
        ]);
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function switchCurrency()
    {
        return response()->json([]);
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function switchLocale()
    {
        return response()->json([]);
    }
}
