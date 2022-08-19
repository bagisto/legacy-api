@extends('mobikul::admin.layouts.content')

@section('page_title')
    {{ __('mobikul::app.mobikul.notification.edit-notification') }}
@stop

@section('content')
    <div class="content">
        @php
            $locale = request()->get('locale') ?: app()->getLocale();
            $channel = request()->get('channel') ?: core()->getDefaultChannelCode();

            $channelLocales = app('Webkul\Core\Repositories\ChannelRepository')->findOneByField('code', $channel)->locales;

            if (! $channelLocales->contains('code', $locale)) {
                $locale = config('app.fallback_locale');
            }
            
            $notificationTranslation = $notification->translations->where('channel', $channel)->where('locale', $locale)->first();
        @endphp
        <form method="POST" action="" @submit.prevent="onSubmit" enctype="multipart/form-data">

            <div class="page-header">
                <div class="page-title">
                    <h1>
                        <i class="icon angle-left-icon back-link" onclick="history.length > 1 ? history.go(-1) : window.location = '{{ url('/admin/notification') }}';"></i>
                        {{ __('mobikul::app.mobikul.notification.new-notification') }}
                    </h1>

                    <div class="control-group">
                        <select class="control" id="channel-switcher" name="channel">
                            @foreach (core()->getAllChannels() as $channelModel)

                                <option
                                    value="{{ $channelModel->code }}" {{ ($channelModel->code) == $channel ? 'selected' : '' }}>
                                    {{ core()->getChannelName($channelModel) }}
                                </option>

                            @endforeach
                        </select>
                    </div>

                    <div class="control-group">
                        <select class="control" id="locale-switcher" name="locale">
                            @foreach ($channelLocales as $localeModel)

                                <option
                                    value="{{ $localeModel->code }}" {{ ($localeModel->code) == $locale ? 'selected' : '' }}>
                                    {{ $localeModel->name }}
                                </option>

                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="page-action">
                     <a href="{{ route('mobikul.notification.send-notification',$notification['id']) }}"  class="btn btn-lg btn-primary">
                        {{ __('mobikul::app.mobikul.send-push-notification-btn-title') }}
                    </a> 

                    <button type="submit" class="btn btn-lg btn-primary">
                        {{ __('mobikul::app.mobikul.notification.create-btn-title') }}
                    </button>
                </div>
            </div>

            <div class="page-content">

                <div class="form-container">
                    @csrf()
                    <input name="_method" type="hidden" value="PUT">
                    <input type="hidden" value="{{ $notification['id'] }}" name="notification_id" />
                    
                    <div class="control-group" :class="[errors.has('title') ? 'has-error' : '']">
                        <label for="title" class="required">{{ __('mobikul::app.mobikul.notification.notification-title') }}</label>

                        <input type="text" v-validate="'required'" class="control" id="title" name="title" value="{{ old('title') ?? (isset($notificationTranslation['title']) ? $notificationTranslation['title']: '') }}" data-vv-as="&quot;{{ __('mobikul::app.mobikul.notification.notification-title') }}&quot;" v-slugify-target="'slug'"/>

                        <span class="control-error" v-if="errors.has('title')">@{{ errors.first('title') }}</span>
                    </div>
                    
                    <div class="control-group" :class="[errors.has('content') ? 'has-error' : '']">
                        <label for="content" class="required">{{ __('mobikul::app.mobikul.notification.notification-content') }}</label>
                        
                        <textarea class="control" name="content" v-validate="'required'" data-vv-as="&quot;{{ __('mobikul::app.mobikul.notification.notification-content') }}&quot;" cols="30" rows="10">{{ old('content') ?? (isset($notificationTranslation['content']) ? $notificationTranslation['content'] : '') }}
                        </textarea>
                        
                        <span class="control-error" v-if="errors.has('content')">@{{ errors.first('content') }}</span>
                    </div>

                    <div class="control-group" :class="[errors.has('image') ? 'has-error' : '']">
                        <label for="image" class="required">
                            {{ __('mobikul::app.mobikul.notification.notification-image') }}
                        </label>

                        <image-wrapper :button-label="'{{ __('mobikul::app.mobikul.notification.notification-image') }}'" input-name="image" :multiple="false" :images='"{{ url('storage/'.$notification->image) }}"'></image-wrapper>

                        <span class="control-error" v-if="errors.has('image')">@{{ errors.first('image') }}</span>
                    </div>

                    <option-wrapper></option-wrapper>

                    <div class="control-group" :class="[errors.has('channels[]') ? 'has-error' : '']" >
                        <label for="reseller" class="required">
                            {{ __('mobikul::app.mobikul.notification.store-view') }}
                        </label>

                        <select  v-validate="'required'" id="channels" class="control" name="channels[]" multiple="multiple" data-vv-as="&quot;{{ __('mobikul::app.mobikul.notification.store-view') }}&quot;">
                            @foreach ($channels as $channelDetail)
                                <option value="{{ $channelDetail->code }}"
                                    @if ( in_array($channelDetail->code, $notification->notificationChannelsArray())) selected @endif >
                                    {{ $channelDetail->name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="control-error" v-if="errors.has('channels[]')">@{{ errors.first('channels[]') }}
                        </span>
                    </div>

                    <div class="control-group" :class="[errors.has('status') ? 'has-error' : '']">
                        <label for="status">
                            {{ __('mobikul::app.mobikul.notification.notification-status') }}
                        </label>

                        <select class="control" name="status" data-vv-as="&quot;{{ __('mobikul::app.mobikul.notification.notification-status') }}&quot;">
                            <option value="1" {{ $notification->status == '1' ? 'selected' : '' }}>{{ __('mobikul::app.mobikul.notification.status.enabled') }}</option>
                            <option value="0" {{ $notification->status == '0' ? 'selected' : '' }}>{{ __('mobikul::app.mobikul.notification.status.disabled') }}</option>
                        </select>
                        <span class="control-error" v-if="errors.has('status')">@{{ errors.first('status') }}</span>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@push('scripts')
    <script type="text/x-template" id="options-template">
        <div>
            <div class="control-group" :class="[errors.has('type') ? 'has-error' : '']">
                <label for="type" class="required">
                    {{ __('mobikul::app.mobikul.notification.notification-type') }}
                </label>

                <select class="control" id="type" name="type" v-validate="'required'" data-vv-as="&quot;{{ __('mobikul::app.mobikul.notification.notification-type') }}&quot;" @change="showHideOptions($event)" v-model="notificationType">

                    <option value="">{{ __('mobikul::app.mobikul.notification.notification-type-option.select') }}</option>
                    <option value="product" {{ $notification->type == 'product' ? 'selected' : '' }}>{{ __('mobikul::app.mobikul.notification.notification-type-option.product') }}</option>
                    <option value="category" {{ $notification->type == 'category' ? 'selected' : '' }}>{{ __('mobikul::app.mobikul.notification.notification-type-option.category') }}</option>
                    <option value="others" {{ $notification->type == 'others' ? 'selected' : '' }}>{{ __('mobikul::app.mobikul.notification.notification-type-option.others') }}</option>
                    <option value="custom_collection" {{ $notification->type == 'custom_collection' ? 'selected' : '' }}>{{ __('mobikul::app.mobikul.notification.notification-type-option.custom-collection') }}</option>
                </select>
                <span class="control-error" v-if="errors.has('type')">@{{ errors.first('type') }}</span>
            </div>

            <div class="control-group" id="productCat" :class="[errors.has('product_category_id') ? 'has-error' : '']" v-if="showProductCategory">
                <label for="product_category_id" class="required">
                    {{ __('mobikul::app.mobikul.notification.product-cat-id') }}
                </label>

                <input type="text" id="product_category_id" class="control" name="product_category_id" v-validate="showProductCategory ? 'required' : ''" value="{{ old('product_category_id') ?? $notification->product_category_id }}" data-vv-as="&quot;{{ __('mobikul::app.mobikul.notification.product-cat-id') }}&quot;">

                <span class="control-error" v-if="errors.has('product_category_id')">@{{ errors.first('product_category_id') }}</span>
            </div>

            <div class="control-group" :class="[errors.has('custom_collection') ? 'has-error' : '']" v-if="(notificationType == 'custom_collection')">
                <label for="custom_collection" class="required">
                    {{ __('mobikul::app.mobikul.notification.collection-autocomplete') }}
                </label>
    
                <input type="text" class="control" autocomplete="off" v-model="search_term" placeholder="{{ __('mobikul::app.mobikul.notification.collection-search-hint') }}" v-on:keyup="searchCollection">
    
                <div class="linked-product-search-result">
                    <ul>
                        <li v-for='(collection_val, index) in collections' v-if='collections.length' @click="addCollection(collection_val)">
                            @{{ collection_val.name }}
                        </li>
    
                        <li v-if='! collections.length && search_term.length && ! is_searching'>
                            {{ __('mobikul::app.mobikul.notification.no-collection-found') }}
                        </li>
    
                        <li v-if="is_searching && search_term.length">
                            {{ __('admin::app.catalog.products.searching') }}
                        </li>
                    </ul>
                </div>
                
                <input type="hidden" name="custom_collection" v-if="addedCollection.id" :value="addedCollection.id" v-validate="'required'" id="custom_collection" data-vv-as="&quot;{{ __('mobikul::app.mobikul.notification.collection-autocomplete') }}&quot;" />
                
                <input type="hidden" name="custom_collection" v-if="!addedCollection.id" value="" v-validate="'required'" id="custom_collection" data-vv-as="&quot;{{ __('mobikul::app.mobikul.notification.collection-autocomplete') }}&quot;" />
        
                <span class="filter-tag" style="text-transform: capitalize; margin-top: 10px; margin-right: 0px; justify-content: flex-start" v-if="addedCollection.id">
                    <span class="wrapper" style="margin-left: 0px; margin-right: 10px;">
                        @{{ addedCollection.name }}
                    <span class="icon cross-icon" @click="removeCollection(addedCollection)"></span>
                    </span>
                </span>
                <span class="control-error" v-if="errors.has('custom_collection')">@{{ errors.first('custom_collection') }}</span>
            </div>

        </div>
    </script>

    <script>

        Vue.component('option-wrapper', {

            template: '#options-template',

            inject: ['$validator'],

            data: function(data) {
                return {
                    showProductCategory: '{{ ($notification['type'] == 'product' || $notification['type'] == 'category') ?? false }}',
                    notificationType : '{{ $notification['type'] }}',
                    collections: [],
                    search_term: '',
                    addedCollection: @json($customCollection),
                    is_searching: false,
                    brand:  {},
                }
            },

            methods: {
                showHideOptions: function (event) {
                    this_this = this;
                    this_this.notificationType = event.target.value;

                    this_this.showProductCategory = false;
                    if (event.target.value == 'product' || event.target.value == 'category' ) {
                        this_this.showProductCategory = true;
                    }
                },
                
                addCollection: function (collection) {
                    this.addedCollection = collection;
                    this.search_term = '';
                    this.collections = [];
                },

                removeCollection: function (collection) {
                    this.addedCollection = {};
                },

                searchCollection: function () {
                    this_this = this;

                    this.is_searching = true;

                    if (this.search_term.length >= 1) {
                        this.$http.get ("{{ route('mobikul.custom-collection.search') }}", {params: {query: this.search_term}})
                            .then (function(response) {

                                if ( this_this.addedCollection ) {
                                    for (var collectionId in response.data) {
                                        if (response.data[collectionId].id == this_this.addedCollection.id) {
                                            response.data.splice(collectionId, 1);
                                        }
                                    }
                                }

                                this_this.collections = response.data;

                                this_this.is_searching = false;
                            })

                            .catch (function (error) {
                                this_this.is_searching = false;
                            })
                    } else {
                        this_this.collections = [];
                        this_this.is_searching = false;
                    }
                }
            },
        });

    </script>

    <script>
        $(document).ready(function () {
            $('#channel-switcher, #locale-switcher').on('change', function (e) {
                $('#channel-switcher').val()
                var query = '?channel=' + $('#channel-switcher').val() + '&locale=' + $('#locale-switcher').val();

                window.location.href = "{{ route('mobikul.notification.edit', $notification->id)  }}" + query;
            })
        });
    </script>
@endpush
