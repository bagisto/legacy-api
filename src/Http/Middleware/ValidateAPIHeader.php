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
     * Controller instance
     * 
     * @param \Webkul\Core\Repositories\ChannelRepository $channelRepository
     * @param \Webkul\Core\Repositories\CurrencyRepository $currencyRepository
     * @param \Webkul\Core\Repositories\LocaleRepository $localeRepository
     */
    public function __construct(
        protected ChannelRepository $channelRepository,
        protected CurrencyRepository $currencyRepository,
        protected LocaleRepository $localeRepository
    )
    { }

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
}
