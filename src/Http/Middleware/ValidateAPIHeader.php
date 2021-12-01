<?php

namespace Webkul\API\Http\Middleware;

use Closure;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Core\Repositories\CurrencyRepository;
use Webkul\Core\Repositories\LocaleRepository;

class ValidateAPIHeader
{
    /**
     * Contains current guard
     *
     * @var array
     */
    protected $guard;

    /**
     * @var \Webkul\Core\Repositories\ChannelRepository
     */
    protected $channelRepository;

    /**
     * @var \Webkul\Core\Repositories\CurrencyRepository
     */
    protected $currencyRepository;

    /**
     * @var \Webkul\Core\Repositories\LocaleRepository
     */
    protected $localeRepository;

    /**
     * Controller instance
     * 
     * @param \Webkul\Core\Repositories\ChannelRepository $channelRepository
     * @param \Webkul\Core\Repositories\CurrencyRepository $currencyRepository
     * @param \Webkul\Core\Repositories\LocaleRepository $localeRepository
     */
    public function __construct(
        ChannelRepository $channelRepository,
        CurrencyRepository $currencyRepository,
        LocaleRepository $localeRepository
        )
    {
        $this->channelRepository = $channelRepository;

        $this->currencyRepository = $currencyRepository;

        $this->localeRepository = $localeRepository;
    }

    /**
    * Handle an incoming request.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \Closure  $next
    * @return mixed
    */
    public function handle($request, Closure $next)
    {
        $token = request()->input('token');
        $channelId = request()->input('channel_id') ?? core()->getDefaultChannel()->id;
        $currencyCode = request()->input('currency') ?? session()->get('currency');
        $localeCode = request()->input('locale') ?? session()->get('locale');

        if (! $this->validateConfigHeader())
        {
            return response()->json([
                'success'   => false,
                'message'   => trans('admin::app.api.auth.invalid-auth'),
            ], 401);
        }

        $request['token'] = $token ?: 0;
        
        // Validate the header request storeId
        $channel = $this->channelRepository->find($channelId);
        if (! $channel ) {
            return response()->json([
                'success'   => false,
                'message'   => trans('admin::app.api.auth.invalid-store'),
            ], 200);
        }
        $request['channel_id'] = $channelId;
        
        // Check for the Channel's currency and validate request currency
        $channelCurrencyCodes = $channel->currencies->pluck('code')->toArray();
        if (! $currencyCode || !in_array($currencyCode, $channelCurrencyCodes)) {

            if ($currency = $this->currencyRepository->find($channel->base_currency_id)) {
                session()->put('currency', $currency->code);

                $request['currency'] = $currency->code;
            }
        } else {
            session()->put('currency', $currencyCode);
            $request['currency'] = $currencyCode;
        }

        // Check for the Channel's locale and validate request locale
        $channelLocaleCodes = $channel->locales->pluck('code')->toArray();
        if (! $localeCode || !in_array($localeCode, $channelLocaleCodes)) {

            if ($locale = $this->localeRepository->find($channel->default_locale_id)) {
                session()->put('locale', $locale->code);

                $request['locale'] = $locale->code;
            }
        } else {
            session()->put('locale', $localeCode);
            $request['locale'] = $localeCode;
        }
        
        return $next($request);
    }

    /**
     * Validate the config values with API header value
     *
     * @return boolean
     */
    public function validateConfigHeader()
    {
        $api_token = request()->header('api_token');
        $config_username = core()->getConfigData('general.api.settings.username');
        $config_password = core()->getConfigData('general.api.settings.password');

        if (! $api_token || 
            ! $config_username || 
            ! $config_password ||  
            ($api_token != md5($config_username . ':' . $config_password)) ) {

            return false;
        } else {

            return true;
        }
    }
}
