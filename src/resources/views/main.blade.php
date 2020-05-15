@extends("layouts.app")

@section('head')
    @parent
    {{Html::script("lib/jquery/jquery-3.2.1.min.js")}}
    {{Html::script("lib/jquery-ui/jquery-ui.min.js")}}
    {{Html::script("lib/tabulator/js/tabulator.min.js")}}
    {{Html::style("lib/tabulator/css/tabulator.min.css")}}
    {{Html::style("lib/tabulator/css/semantic-ui/tabulator_semantic-ui.min.css")}}
    {{Html::style("lib/jquery-ui/jquery-ui.min.css")}}
    {{Html::script("lib/toastr/toastr.min.js")}}
    {{Html::script("lib/sweetalert/sweetalert2.all.min.js")}}
    {{Html::style("lib/toastr/toastr.min.css")}}
    {{Html::script("lib/jquery-validation/jquery.validate.min.js")}}
    {{Html::script("js/common.js")}}
    @if (env("DIODE_IN", true))
      {{Html::script("js/diode-in.js")}}
    @else
      {{Html::script("js/diode-out.js")}}
    @endif
@endsection

@section("content")
    <div class="container">
        <div class="tab-v1">
            <!-- The following info about upload can come from PHP (ex: from .env) -->            
            @if (env("DIODE_IN", true))
            <main-modules
              :interval=10000
              :max-upload-size="{{1024 * 1024 * 1024 * env('MAX_UPLOAD_SIZE_GB', 1)}}"
              diodein
              max-upload-size-error-message="{{'Your upload cannot contain a file bigger than ' . env('MAX_UPLOAD_SIZE_GB', 1) . 'GB!'}}"
              ip-addr="{{env('INTERNAL_IP')}}"
            ></main-modules>
            @else
            <main-modules
              :interval=10000
              :max-upload-size="{{1024 * 1024 * 1024 * env('MAX_UPLOAD_SIZE_GB', 1)}}"
              max-upload-size-error-message="{{'Your upload cannot contain a file bigger than ' . env('MAX_UPLOAD_SIZE_GB', 1) . 'GB!'}}"
              ip-addr="{{env('INTERNAL_IP')}}"
            ></main-modules>
            @endif
        </div>
    </div>
@endsection