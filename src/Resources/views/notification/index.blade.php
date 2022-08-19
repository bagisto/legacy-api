@extends('admin::layouts.content')

@section('page_title')
    {{ __('api::app.notification.title') }}
@stop

@section('content')
    <div class="content">
        <?php $locale = request()->get('locale') ?: null; ?>
        <?php $channel = request()->get('channel') ?: null; ?>

        <div class="page-header">
            <div class="page-title">
                <h1>{{ __('api::app.notification.title') }}</h1>
            </div>
            
            <div class="page-action">
                <a href="{{ route('api.notification.create') }}" class="btn btn-lg btn-primary">
                    {{ __('api::app.notification.add-title') }}
                </a>
            </div>
        </div>

        <div class="page-content">
            <datagrid-plus src="{{ route('api.notification.index') }}"></datagrid-plus>
        </div>
    </div>
@stop

@push('scripts')
    <script>
        function reloadPage(getVar, getVal) {
            let url = new URL(window.location.href);
            url.searchParams.set(getVar, getVal);

            window.location.href = url.href;
        }
        
    </script>
@endpush
