@extends('edutalk-core::admin._master')

@section('css')

@endsection

@section('js')

@endsection

@section('js-init')
    <script type="text/javascript">
        $(document).ready(function () {
            Edutalk.wysiwyg($('.js-wysiwyg'));
        });
    </script>
@endsection

@section('content')
    {!! Form::open(['class' => 'js-validate-form']) !!}
    <div class="layout-2columns sidebar-right">
        <div class="column main">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('edutalk-core::base.form.basic_info') }}</h3>
                    <div class="box-tools">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label class="control-label">
                            <b>{{ trans('edutalk-core::base.form.title') }}</b>

                        </label>
                        <input required type="text" name="static_block[title]"
                               class="form-control"
                               value="{{ $object->title }}"
                               autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            <b>{{ trans('edutalk-core::base.form.slug') }}</b>

                        </label>
                        <input type="text" name="static_block[slug]"
                               class="form-control"
                               value="{{ $object->slug }}" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            <b>{{ trans('edutalk-core::base.form.content') }}</b>
                        </label>
                        <textarea name="static_block[content]"
                                  data-height="600px"
                                  class="form-control js-wysiwyg">{!! $object->content !!}</textarea>
                    </div>
                </div>
            </div>
            @php do_action(BASE_ACTION_META_BOXES, 'main', EDUTALK_STATIC_BLOCKS, $object) @endphp
        </div>
        <div class="column right">
            @include('edutalk-core::admin._components.form-actions')
            @php do_action(BASE_ACTION_META_BOXES, 'top-sidebar', EDUTALK_STATIC_BLOCKS, $object) @endphp
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ trans('edutalk-core::base.form.status') }}</h3>
                    <div class="box-tools">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    {!! form()->select('static_block[status]', [
                       'activated' => trans('edutalk-core::base.status.activated'),
                        'disabled' => trans('edutalk-core::base.status.disabled'),
                    ], $object->status, ['class' => 'form-control']) !!}
                </div>
            </div>
            @php do_action(BASE_ACTION_META_BOXES, 'bottom-sidebar', EDUTALK_STATIC_BLOCKS, $object) @endphp
        </div>
    </div>
    {!! Form::close() !!}
@endsection
