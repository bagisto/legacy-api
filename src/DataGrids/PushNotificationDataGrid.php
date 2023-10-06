<?php

namespace Webkul\API\DataGrids;

use Webkul\Ui\DataGrid\DataGrid;
use Webkul\Core\Models\Locale;
use Webkul\Core\Models\Channel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PushNotificationDataGrid extends DataGrid
{
    /**
     * Default sort order of datagrid.
     *
     * @var string
     */
    protected $sortOrder = 'desc';

    /**
     * Set index columns, ex: id.
     *
     * @var string
     */
    protected $index = 'notification_id';

    /**
     * If paginated then value of pagination.
     *
     * @var int
     */
    protected $itemsPerPage = 10;

    /**
     * Locale.
     *
     * @var string
     */
    protected $locale = 'all';

    /**
     * Channel.
     *
     * @var string
     */
    protected $channel = 'all';

    /**
     * Contains the keys for which extra filters to show.
     *
     * @var string[]
     */
    protected $extraFilters = [
        'channels',
        'locales',
    ];

    /**
     * Create datagrid instance.
     *
     * @return void
     */
    public function __construct()
    {
        /* locale */
        $this->locale = core()->getRequestedLocaleCode();

        /* channel */
        $this->channel = core()->getRequestedChannelCode();

        /* parent constructor */
        parent::__construct();

        /* finding channel code */
        if ($this->channel !== 'all') {
            $this->channel = Channel::query()->where('code', $this->channel)->first();
            $this->channel = $this->channel ? $this->channel->code : 'all';
        }
    }

    /**
     * Prepare query builder.
     *
     * @return void
     */
    public function prepareQueryBuilder()
    {
        if ($this->channel === 'all') {
            $whereInChannels = Channel::query()->pluck('code')->toArray();
        } else {
            $whereInChannels = [$this->channel];
        }

        if ($this->locale === 'all') {
            $whereInLocales = Locale::query()->pluck('code')->toArray();
        } else {
            $whereInLocales = [$this->locale];
        }
        $queryBuilder = DB::table('push_notification_translations as pn_trans')
                            ->leftJoin('api_notifications as pn', 'pn_trans.push_notification_id', '=', 'pn.id')
                            ->leftJoin('channels as ch', 'pn_trans.channel', '=', 'ch.code')
                            ->leftJoin('channel_translations as ch_t', 'ch.id', '=', 'ch_t.channel_id')
                            ->addSelect(
                                'pn_trans.push_notification_id as notification_id',
                                'pn.image as image',
                                'pn_trans.title as title',
                                'pn_trans.content as content',
                                'pn_trans.channel as channel',
                                'pn_trans.locale as locale',
                                'pn.type as type',
                                'pn.product_category_id as ',
                                'pn.status as status',
                                'pn.created_at as created_at',
                                'pn.updated_at as updated_at',
                                'ch_t.name as channel_name'
                            );
        
            $queryBuilder->groupBy('pn_trans.push_notification_id', 'pn_trans.channel', 'pn_trans.locale');

            $queryBuilder->whereIn('pn_trans.locale', $whereInLocales);
            $queryBuilder->whereIn('pn_trans.channel', $whereInChannels);

        $this->addFilter('notification_id', 'pn_trans.push_notification_id');
        $this->addFilter('title', 'pn_trans.title');
        $this->addFilter('content', 'pn_trans.content');
        $this->addFilter('channel_name', 'ch_t.name');
        $this->addFilter('status', 'pn.status');
        $this->addFilter('type', 'pn.type');

        $this->setQueryBuilder($queryBuilder);
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function addColumns()
    {
        $this->addColumn([
            'index'         => 'notification_id',
            'label'         => trans('api::app.notification.id'),
            'type'          => 'number',
            'searchable'    => true,
            'sortable'      => true,
            'filterable'    => true,
        ]);

        $this->addColumn([
            'index'         => 'image',
            'label'         => trans('api::app.notification.image'),
            'type'          => 'html',
            'searchable'    => false,
            'sortable'      => false,
            'closure'       => true,
            'wrapper'       => function($row) {
                if ( $row->image )
                    return '<img src=' . Storage::url($row->image) . ' class="img-thumbnail" width="100px" height="70px" />';

            }
        ]);

        $this->addColumn([
            'index'         => 'title',
            'label'         => trans('api::app.notification.text-title'),
            'type'          => 'string',
            'searchable'    => true,
            'sortable'      => true,
            'filterable'    => true,
        ]);

        $this->addColumn([
            'index'         => 'content',
            'label'         => trans('api::app.notification.notification-content'),
            'type'          => 'string',
            'searchable'    => true,
            'sortable'      => true,
            'filterable'    => true
        ]);

        $this->addColumn([
            'index'         => 'type',
            'label'         => trans('api::app.notification.notification-type'),
            'type'          => 'string',
            'searchable'    => true,
            'sortable'      => true,
            'filterable'    => true,
            'closure'       => true,
            'wrapper'       => function($row) {
                return ucwords(strtolower(str_replace("_", " ", $row->type)));
            }
        ]);

        $this->addColumn([
            'index'         => 'channel_name',
            'label'         =>  trans('api::app.notification.store-view'),
            'type'          => 'string',
            'searchable'    => false,
            'sortable'      => false,
            'filterable'    => false,
            'closure'       => true,
            'wrapper'       => function($row) {
                $channelNames = '';
                $notificationTranslations = app('Webkul\API\Repositories\NotificationTranslationRepository')->where(['push_notification_id' => $row->notification_id])->groupBy('push_notification_id', 'channel')->pluck('channel')->toArray();

                if ($notificationTranslations) {
                    $channels = app('Webkul\Core\Repositories\ChannelRepository')->whereIn('code', $notificationTranslations)->get();
                    
                    foreach ($channels as $key => $channel) {
                        if ( $channel ) {
                            $channelNames .= $channel->name . '</br>' . PHP_EOL;
                        }   
                    } 
                }

                return $channelNames;
            }
        ]);

        $this->addColumn([
            'index'         => 'status',
            'label'         => trans('api::app.notification.notification-status'),
            'type'          => 'boolean',
            'searchable'    => true,
            'sortable'      => true,
            'filterable'    => true,
            'closure'       => true,
            'wrapper'       => function($row) {
                if ( $row->status == 1 )
                    return '<span class="badge badge-md badge-success">' . trans('api::app.notification.status.enabled') . '</span>';
                else
                    return '<span class="badge badge-md badge-danger">' . trans('api::app.notification.status.disabled') . '</span>';
            }
        ]);

        $this->addColumn([
            'index'         => 'created_at',
            'label'         =>  trans('api::app.notification.created'),
            'type'          => 'datetime',
            'searchable'    => true,
            'sortable'      => true,
            'filterable'    => true
        ]);

        $this->addColumn([
            'index'         => 'updated_at',
            'label'         => trans('api::app.notification.modified'),
            'type'          => 'datetime',
            'searchable'    => true,
            'sortable'      => true,
            'filterable'    => true
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'title'     => trans('admin::app.datagrid.edit'),
            'method'    => 'GET', //use post only for redirects only
            'route'     => 'api.notification.edit',
            'icon'      => 'icon pencil-lg-icon',
            'condition' => function () {
                return true;
            },
        ]);

        $this->addAction([
            'title'     => trans('admin::app.datagrid.delete'),
            'method'    => 'POST', // use GET request only for redirect purposes
            'route'     => 'api.notification.delete',
            'icon'      => 'icon trash-icon',
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        $this->addMassAction([
            'type'      => 'delete',
            'label'     => trans('admin::app.datagrid.delete'),
            'action'    => route('api.notification.mass-delete'),
            'method'    => 'POST',
        ]);

        $this->addMassAction([
            'type'      => 'update',
            'label'     => trans('admin::app.datagrid.update-status'),
            'action'    => route('api.notification.mass-update'),
            'method'    => 'POST',
            'options'   => [
                trans('admin::app.datagrid.active')     => 1,
                trans('admin::app.datagrid.inactive')   => 0
            ]
        ]);
    }
}
