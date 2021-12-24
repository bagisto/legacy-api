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
        $channelId = request()->input('channel_id');

        if (! $this->validateConfigHeader())
        {
            return response()->json([
                'success'   => false,
                'message'   => trans('admin::app.api.auth.invalid-auth'),
            ], 401);
        }

        $request['token'] = $token ?: 0;
        
        // Validate the header request storeId
        if ( $channelId ) {
            $channel = $this->channelRepository->find($channelId);
            if (! $channel ) {
                return response()->json([
                    'success'   => false,
                    'message'   => trans('admin::app.api.auth.invalid-store'),
                ], 200);
            }
            
            $request['channel_id'] = $channelId;
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
