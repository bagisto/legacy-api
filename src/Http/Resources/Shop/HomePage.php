<?php

namespace Webkul\API\Http\Resources\Shop;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Webkul\Checkout\Facades\Cart;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Facades\ProductImage as ProductImageFacade;
use Illuminate\Http\Resources\Json\JsonResource;

class HomePage extends JsonResource
{
    /**
     * Contains current channel
     *
     * @var string
     */
    protected $channel;

    /**
     * Contains current currency
     *
     * @var string
     */
    protected $currencyCode;

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
        $this->channelRepository = app('Webkul\Core\Repositories\ChannelRepository');

        $this->sliderRepository = app('Webkul\Core\Repositories\SliderRepository');

        $this->velocityHelper = app('Webkul\Velocity\Helpers\Helper');

        $this->categoryRepository = app('Webkul\Category\Repositories\CategoryRepository');

        $this->productRepository = app('Webkul\Product\Repositories\ProductRepository');

        $this->cmsRepository = app('Webkul\CMS\Repositories\CmsRepository');

        $this->wishlistHelper = app('Webkul\Customer\Helpers\Wishlist');
        
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
        $this->channel = request()->input('channel_id');
        $this->currencyCode = request()->input('currency');
        $this->localeCode = request()->input('locale');

        $channel = $this->channelRepository->find($this->channel);
        
        $defaultCurrency = $channel->currencies()->where('id', $channel->base_currency_id)->first();

        return [
            'success'           => true,
            'defaultChannelId'  => core()->getDefaultChannel()->id,
            'allowedCurrencies' => $channel->currencies()->get()->toArray(),
            'defaultCurrency'   => $defaultCurrency ? $defaultCurrency->code : $this->currencyCode,
            'channelData'       => $this->getAllowedChannelWithLocales(),
            'customer'          => $this['customer'] ? $this['customer']->toArray() : null,
            'cartCount'         => Cart::getCart() ? count(Cart::getCart()->items) : 0,
            'categories'        => $this->categoryRepository->getVisibleCategoryTree($channel->root_category_id)->toArray(),
            'sliders'           => $this->getSlider($channel),
            'homeContent'       => $this->getThemeHomePageContent($channel),
        ];
    }

    /**
     * Get the slider array based on channel.
     *
     * @param  \Webkul\Core\Contracts\Channel   $channel
     * @return array
     */
    public function getSlider($channel = null)
    {
        $sliderData = [];
        $channel = $channel ?? core()->getCurrentChannel();

        $sliders = $this->sliderRepository->where('channel_id', $channel->id)
            ->whereRaw("find_in_set(?, locale)", [$this->localeCode])
            ->where(function ($query) {
                $query->where('expired_at', '>=', Carbon::now()->format('Y-m-d'))
                    ->orWhereNull('expired_at');
            })
            ->orderBy('sort_order', 'ASC')
            ->get();

        foreach ($sliders as $slider) {
            $path = $slider->slider_path;
            $slug_type = '';
            $redirect_id = null;

            if ( $path ) {
                $getSlug = explode("/", $path);
                if ( count($getSlug) > 1 )
                    $path = $getSlug[1];

                if ( $category = $this->categoryRepository->whereTranslation('slug', $path)->first() ) {
                    $slug_type = 'category';
                    $redirect_id = $category->id;
                } elseif ( $product = $this->productRepository->findOneByField('sku', $path) ) {
                    $slug_type = 'product';
                    $redirect_id = $product->id;
                } elseif ( $cms = $this->cmsRepository->whereTranslation('url_key', $path)->first() ) {
                    $slug_type = 'cms';
                    $redirect_id = $cms->id;
                }
            }

            $sliderData[] = [
                'id'            => $slider->id,
                'title'         => $slider->title,
                'content'       => $slider->content,
                'image_url'     => $slider->image_url,
                'path'          => $path,
                'type'          => $slug_type,
                'redirect_id'   => $redirect_id,
            ];
        }
        
        return $sliderData;
    }

    /**
     * Get the channel with allowed locales.
     *
     * @return void
     */
    public function getAllowedChannelWithLocales()
    {
        $channelList = [];
        foreach (core()->getAllChannels() as $channel) {
            $locales = [];

            if ( $channel ) {
                foreach ($channel->locales as $locale) {
                
                    if ( $locale ) {
                        $locales[] = [
                            'id'        => $channel->id,
                            'locale_id' => $locale->id,
                            'code'      => $locale->code ?? '',
                            'name'      => $locale->name ?? '',
                        ];
                    }
                }

                $channelList[] = [
                    'id'        => $channel->id,
                    'name'      => $channel->name ?? '',
                    'code'      => $channel->code ?? '',
                    'channels'  => $locales,
                ];
            }
        }

        return $channelList;
    }

    /**
     * Get HomePageContent of the channel's theme.
     *
     * @param  \Webkul\Core\Contracts\Channel   $channel
     * @return void
     */
    public function getThemeHomePageContent($channel = null)
    {
        $homePageContent = [];

        $themeHomePageContent = $channel->home_page_content;

        $velocity = $this->velocityHelper->getVelocityMetaData();
        if ( $channel->theme == 'velocity' && $velocity->home_page_content ) {
            $themeHomePageContent = $velocity->home_page_content;
        }
        
        $home_page_content = explode("@include", strip_tags($themeHomePageContent));
        foreach (array_filter($home_page_content) as $content) {

            if ( $arrayContent = $this->getTemplateContent(rtrim(ltrim(str_replace(" ", "", $content), "("), ")"), $velocity) ) {
                $homePageContent[] = $arrayContent;
            }
        }
        
        return $homePageContent;
    }

    /**
     * Get HomePageContent of the channel's theme.
     *
     * @return void
     */
    public function getTemplateContent($trimedString = null, $theme = null)
    {
        $homeContent = null;
        $advertisement = isset($theme->advertisement) ? json_decode($theme->advertisement, true) : null;

        if ( Str::contains($trimedString, "'shop::home.category'") ) {
            $trimedContent = $this->formatBlade($trimedString, "'shop::home.category',", "['category'=>");
            if ( $trimedContent ) {
                $slug = str_replace("'", "", $trimedContent[0]);
                
                $category = $this->categoryRepository->whereTranslation('slug', $slug)->first();
                if ( $category ) {
                    $homeContent = [
                        'type'      => 'category',
                        'slug'      => $slug,
                        'label'     => $category->name,
                        'image_url' => $category->image_url,
                        'id'        => $category->id
                    ];
                }
            }
        } else if ( Str::contains($trimedString, "'shop::home.advertisements.advertisement-four'") ) {
            $advertisementFour = isset($advertisement[4]) ? array_values(array_filter($advertisement[4])) : [];
            
            $homeContent = [
                'type'          => 'advertisement',
                'slug'          => 'four',
                'id'            => null,
                'custom_data'   => [
                    [
                        'image_url'     => asset('/themes/velocity/assets/images/big-sale-banner.webp'),
                        'label'         => '',
                        'slug'          => '',
                        'id'            => null
                    ],  [
                        'image_url'     => asset('/themes/velocity/assets/images/seasons.webp'),
                        'label'         => '',
                        'slug'          => '',
                        'id'            => null
                    ],  [
                        'image_url'     => asset('/themes/velocity/assets/images/deals.webp'),
                        'label'         => '',
                        'slug'          => '',
                        'id'            => null
                    ],  [
                        'image_url'     => asset('/themes/velocity/assets/images/kids.webp'),
                        'label'         => '',
                        'slug'          => '',
                        'id'            => null
                    ]
                ]
            ];

            foreach($advertisementFour as $key => $advertisement) {
                $homeContent['custom_data'][$key]['image_url'] = $advertisement[$key];
            }
            
            $trimedContent = $this->formatBlade($trimedString, "'shop::home.advertisements.advertisement-four',");
            if ( $trimedContent ) {
                $slugs = explode(",", $trimedContent);
                
                foreach ($slugs as $slug) {
                    $categorySlug = explode("=>", str_replace("'", "", $slug));
                    
                    $category = $this->categoryRepository->whereTranslation('slug', $categorySlug[1])->first();

                    if ( $category ) {
                        if ( $categorySlug[0] == 'one')
                            $homeContent['custom_data'][0] = array_merge($homeContent['custom_data'][0], [
                                'label'     => $category->name,
                                'slug'      => $categorySlug[1],
                                'id'        => $category->id
                            ]);
                        else if ( $categorySlug[0] == 'two')
                            $homeContent['custom_data'][1] = array_merge($homeContent['custom_data'][1], [
                                'label'     => $category->name,
                                'slug'      => $categorySlug[1],
                                'id'        => $category->id
                            ]);
                        else if ( $categorySlug[0] == 'three')
                            $homeContent['custom_data'][2] = array_merge($homeContent['custom_data'][2], [
                                'label'     => $category->name,
                                'slug'      => $categorySlug[1],
                                'id'        => $category->id
                            ]);
                        else if ( $categorySlug[0] == 'four')
                            $homeContent['custom_data'][3] = array_merge($homeContent['custom_data'][3], [
                                'label'     => $category->name,
                                'slug'      => $categorySlug[1],
                                'id'        => $category->id
                            ]);
                    }
                }
            }
        } else if ( Str::contains($trimedString, "'shop::home.advertisements.advertisement-three'") ) {
            $advertisementThree = isset($advertisement[3]) ? array_values(array_filter($advertisement[3])) : [];
            
            $homeContent = [
                'type'          => 'advertisement',
                'slug'          => 'three',
                'id'            => null,
                'custom_data'   => [
                    [
                        'image_url'     => asset('/themes/velocity/assets/images/headphones.webp'),
                        'label'         => '',
                        'slug'          => '',
                        'id'            => null
                    ],  [
                        'image_url'     => asset('/themes/velocity/assets/images/watch.webp'),
                        'label'         => '',
                        'slug'          => '',
                        'id'            => null
                    ],  [
                        'image_url'     => asset('/themes/velocity/assets/images/kids-2.webp'),
                        'label'         => '',
                        'slug'          => '',
                        'id'            => null
                    ]
                ]
            ];

            foreach($advertisementThree as $key => $advertisement) {
                $homeContent['custom_data'][$key]['image_url'] = $advertisement[$key];
            }
            
            $trimedContent = $this->formatBlade($trimedString, "'shop::home.advertisements.advertisement-three',");
            if ( $trimedContent ) {
                $slugs = explode(",", $trimedContent);
                
                foreach ($slugs as $slug) {
                    $categorySlug = explode("=>", str_replace("'", "", $slug));
                    
                    $category = $this->categoryRepository->whereTranslation('slug', $categorySlug[1])->first();

                    if ( $category ) {
                        if ( $categorySlug[0] == 'one')
                            $homeContent['custom_data'][0] = array_merge($homeContent['custom_data'][0], [
                                'label'     => $category->name,
                                'slug'      => $categorySlug[1],
                                'id'        => $category->id
                            ]);
                        else if ( $categorySlug[0] == 'two')
                            $homeContent['custom_data'][1] = array_merge($homeContent['custom_data'][1], [
                                'label'     => $category->name,
                                'slug'      => $categorySlug[1],
                                'id'        => $category->id
                            ]);
                        else if ( $categorySlug[0] == 'three')
                            $homeContent['custom_data'][2] = array_merge($homeContent['custom_data'][2], [
                                'label'     => $category->name,
                                'slug'      => $categorySlug[1],
                                'id'        => $category->id
                            ]);
                    }
                }
            }
        } else if ( Str::contains($trimedString, "'shop::home.advertisements.advertisement-two'") ) {
            $advertisementTwo = isset($advertisement[2]) ? array_values(array_filter($advertisement[2])) : [];
            
            $homeContent = [
                'type'          => 'advertisement',
                'slug'          => 'two',
                'id'            => null,
                'custom_data'   => [
                    [
                        'image_url'     => asset('/themes/velocity/assets/images/toster.webp'),
                        'label'         => '',
                        'slug'          => '',
                        'id'            => null
                    ],  [
                        'image_url'     => asset('/themes/velocity/assets/images/trimmer.webp'),
                        'label'         => '',
                        'slug'          => '',
                        'id'            => null
                    ]
                ]
            ];

            foreach($advertisementTwo as $key => $advertisement) {
                $homeContent['custom_data'][$key]['image_url'] = $advertisement[$key];
            }
            
            $trimedContent = $this->formatBlade($trimedString, "'shop::home.advertisements.advertisement-two',");
            if ( $trimedContent ) {
                $slugs = explode(",", $trimedContent);
                
                foreach ($slugs as $slug) {
                    $categorySlug = explode("=>", str_replace("'", "", $slug));
                    
                    $category = $this->categoryRepository->whereTranslation('slug', $categorySlug[1])->first();

                    if ( $category ) {
                        if ( $categorySlug[0] == 'one')
                            $homeContent['custom_data'][0] = array_merge($homeContent['custom_data'][0], [
                                'label'     => $category->name,
                                'slug'      => $categorySlug[1],
                                'id'        => $category->id
                            ]);
                        else if ( $categorySlug[0] == 'two')
                            $homeContent['custom_data'][1] = array_merge($homeContent['custom_data'][1], [
                                'label'     => $category->name,
                                'slug'      => $categorySlug[1],
                                'id'        => $category->id
                            ]);
                    }
                }
            }
        } else if ( Str::contains($trimedString, "'shop::home.category-with-custom-option'") ) {
            $homeContent = [
                'type'  => 'category-with-custom-option',
                'slug'  => 'category',
                'id'    => null
            ];

            $trimedContent = $this->formatBlade($trimedString, "'shop::home.category-with-custom-option',", "['category'=>[");
            if ( $trimedContent ) {
                $slugs = explode(",", str_replace("'", "", $trimedContent[0]));
                
                foreach ($slugs as $slug) {
                    $category = $this->categoryRepository->whereTranslation('slug', $slug)->first();
                    if ( $category ) {
                        $children = $this->categoryRepository->findByField('parent_id', $category->id);

                        $homeContent['custom_data'][] = [
                            'image_url' => $category->image_url,
                            'label'     => $category->name,
                            'slug'      => $slug,
                            'id'        => $category->id,
                            'children'  => $children ? $children->toArray() : [],
                        ];
                    }
                }
            }
        } else if ( Str::contains($trimedString, "'shop::home.hot-categories'") ) {
            $homeContent = [
                'label' => trans('velocity::app.home.hot-categories'),
                'type'  => 'hot-categories',
                'slug'  => 'category',
                'id'    => null
            ];

            $trimedContent = $this->formatBlade($trimedString, "'shop::home.hot-categories',", "['category'=>[");
            if ( $trimedContent ) {
                $slugs = explode(",", str_replace("'", "", $trimedContent[0]));
                foreach ($slugs as $slug) {
                    $category = $this->categoryRepository->whereTranslation('slug', $slug)->first();
                    if ( $category ) {
                        $children = $this->categoryRepository->findByField('parent_id', $category->id);

                        $homeContent['custom_data'][] = [
                            'icon_url'  => $category->category_icon_path ? Storage::url($category->category_icon_path) : null,
                            'label'     => $category->name,
                            'slug'      => $slug,
                            'id'        => $category->id,
                            'children'  => $children ? $children->toArray() : []
                        ];
                    }
                }
            }
        } else if ( Str::contains($trimedString, "shop::home.featured-products") ) {
            $homeContent = [
                'label' => trans('shop::app.home.featured-products'),
                'type'  => 'featured-products',
                'slug'  => 'product',
                'id'    => null
            ];
            
            $featuredProducts = $this->productRepository->getFeaturedProducts();
            foreach ($featuredProducts as $productFlat) {
                $baseImage = ProductImageFacade::getProductBaseImage($productFlat->product);
                
                /* get type instance */
                $productTypeInstance = $productFlat->product->getTypeInstance();
                
                $homeContent['custom_data'][] = [
                    'image_url'     => $baseImage['medium_image_url'],
                    'label'         => $productFlat->name,
                    'type'          => $productFlat->type,
                    'slug'          => $productFlat->url_key,
                    'is_wishlisted' => $this->wishlistHelper->getWishlistProduct($productFlat) ? true : false,
                    'price'         => $productTypeInstance->getMinimalPrice(),
                    'formated_price'=> core()->currency($productTypeInstance->getMinimalPrice()),
                    'id'        => $productFlat->product_id
                ];
            }
        } else if ( Str::contains($trimedString, "shop::home.new-products") ) {
            $homeContent = [
                'label' => trans('shop::app.home.new-products'),
                'type'  => 'new-products',
                'slug'  => 'product',
                'id'    => null
            ];

            $newProducts = $this->productRepository->getNewProducts();
            
            foreach ($newProducts as $productFlat) {
                $baseImage = ProductImageFacade::getProductBaseImage($productFlat->product);
                
                /* get type instance */
                $productTypeInstance = $productFlat->product->getTypeInstance();
                
                $homeContent['custom_data'][] = [
                    'image_url'     => $baseImage['medium_image_url'],
                    'label'         => $productFlat->name,
                    'type'          => $productFlat->type,
                    'slug'          => $productFlat->url_key,
                    'is_wishlisted' => $this->wishlistHelper->getWishlistProduct($productFlat) ? true : false,
                    'price'         => $productTypeInstance->getMinimalPrice(),
                    'formated_price'=> core()->currency($productTypeInstance->getMinimalPrice()),
                    'id'            => $productFlat->product_id
                ];
            }
        } else if ( Str::contains($trimedString, "shop::home.product-policy") ) {
            $product_policy = array_values(array_filter(explode("\r\n", strip_tags($theme->product_policy))));

            $homeContent = [
                'type'          => 'product-policy',
                'slug'          => 'policy',
                'id'            => null,
                'custom_data'   => []
            ];

            foreach ($product_policy as $key => $policy) {
                $homeContent['custom_data'][$key] = [
                    'icon_url'  => null,
                    'label'     => $policy,
                    'id'        => null
                ];
            }
        } else if ( Str::contains($trimedString, "shop::home.customer-reviews") ) {
            $homeContent = [
                'label'         => trans('velocity::app.home.customer-reviews'),
                'type'          => 'customer-reviews',
                'slug'          => 'review',
                'id'            => null,
                'custom_data'   => $this->velocityHelper->getShopRecentReviews(4)->toArray()
            ];
        } else if ( Str::contains($trimedString, "'shop::home.popular-categories'") ) {
            $homeContent = [
                'label' => trans('velocity::app.home.popular-categories'),
                'type'  => 'popular-categories',
                'slug'  => 'category',
                'id'    => null
            ];

            $trimedContent = $this->formatBlade($trimedString, "'shop::home.popular-categories',", "['category'=>[");
            if ( $trimedContent ) {
                $slugs = explode(",", str_replace("'", "", $trimedContent[0]));
                foreach ($slugs as $slug) {
                    $category = $this->categoryRepository->whereTranslation('slug', $slug)->first();
                    if ( $category ) {
                        $children = $this->categoryRepository->findByField('parent_id', $category->id);
                        $homeContent['custom_data'][] = [
                            'image_url'  => $category->image_url,
                            'label'     => $category->name,
                            'slug'      => $slug,
                            'id'        => $category->id,
                            'children'  => $children ? $children->toArray() : []
                        ];
                    }
                }
            }
        }
        
        return $homeContent;
    }

    /**
     * format the blade with their data
     *
     * @return array
     */
    public function formatBlade($trimedString = null, $findString = '', $explodingString = '')
    {
        $trimedContent = [];
        $matchTemplate = explode($findString, $trimedString);
        $data = array_values(array_filter($matchTemplate));
        
        if ( is_array($data) && count($matchTemplate) > 1 ) {
            if ( $explodingString ) {
                $trimedContent = array_values(array_filter(explode($explodingString, rtrim($data[0], "]"))));
            } else {
                $trimedContent = ltrim(rtrim($data[0], "]"), "[");
            }
        }
        
        return $trimedContent;
    }
}
