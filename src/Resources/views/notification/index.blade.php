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
                <div class="export-import" @click="showModal('downloadDataGrid')">
                    <i class="export-icon"></i>

                    <span>
                        {{ __('admin::app.export.export') }}
                    </span>
                </div>

                <a href="{{ route('api.notification.create') }}" class="btn btn-lg btn-primary">
                    {{ __('api::app.notification.add-title') }}
                </a>
            </div>
        </div>

        <div class="page-content">
            <datagrid-plus src="{{ route('api.notification.index') }}"></datagrid-plus>
        </div>
    </div>

    <modal id="downloadDataGrid" :is-open="modalIds.downloadDataGrid">
        <h3 slot="header">{{ __('admin::app.export.download') }}</h3>

        <div slot="body">
            <export-form></export-form>
        </div>
    </modal>
@stop

@push('scripts')
    @include('admin::export.export', ['gridName' => app('Webkul\API\DataGrids\PushNotificationDataGrid')])

    <script>
        function reloadPage(getVar, getVal) {
            let url = new URL(window.location.href);
            url.searchParams.set(getVar, getVal);

            window.location.href = url.href;
        }
        
    </script>
@endpush
