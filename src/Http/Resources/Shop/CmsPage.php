<?php

namespace Webkul\API\Http\Resources\Shop;

use Illuminate\Http\Resources\Json\JsonResource;

class CmsPage extends JsonResource
{
    /**
     * Contains current channel
     *
     * @var string
     */
    protected $channel;

    /**
     * Contains current locale
     *
     * @var string
     */
    protected $localeCode;
    
    /**
     * Create a new resource instance.
     *
     * @return void
     */
    public function __construct($resource)
    {
        $this->cmsRepository = app('Webkul\CMS\Repositories\CmsRepository');
        
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $cms_content = [];
        
        foreach ($this->getAll() as $cms) {
            $content = [
                'id'        => $cms->cms_page_id,
                'url_key'   => $cms->url_key,
                'title'     => $cms->page_title,
            ];
            
            if ( request()->input('full_content') ) {
                $content['meta_title']          = $cms->meta_title;
                $content['meta_keywords']       = $cms->meta_keywords;
                $content['meta_description']    = $cms->meta_description;
                $content['content']             = $cms->html_content;
                $content['created_at']          = $cms->created_at;
                $content['updated_at']          = $cms->updated_at;
            }
            
            $cms_content[] = $content;
        }
        
        return [
            'success'   => count($cms_content) ? true : false,
            'pages'     => $cms_content
        ];
    }

    /**
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAll()
    {
        $params = request()->all();

        $results = $this->cmsRepository->scopeQuery(function ($query) use ($params) {
            $channel = $params['channel_id'];

            $locale = $params['locale'];

            $qb = $query->distinct()
                ->addSelect('cms_pages.*')
                ->addSelect('cms_page_translations.*')
                ->addSelect('cms_page_channels.*')
                ->leftJoin('cms_page_translations', 'cms_page_translations.cms_page_id', '=', 'cms_pages.id')
                ->leftJoin('cms_page_channels', 'cms_page_channels.cms_page_id', '=', 'cms_pages.id')
                ->where('cms_page_channels.channel_id', $channel)
                ->where('cms_page_translations.locale', $locale);

            if (isset($params['id']) && $params['id']) {
                $qb->where('cms_pages.id', $params['id']);
            }

            return $qb;
        });

        if (isset($params['id']) && $params['id']) {
            return $results->get();
        } else {
            return $results->paginate(isset($params['limit']) ? $params['limit'] : 10);
        }
    }
}
