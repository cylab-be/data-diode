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
        <div class="tab-v1" style="text-align:center;position:absolute;top:12em;">
            <h1>WELCOME TO THE HOME PAGE</h1>
            <br/>
            <br/>
            <br/>
            <div 
                class="container pt90"
                style="padding-bottom:10em"
            >
                <div
                    class="row pb60"
                    style="width:100%;text-align:center;margin:auto;"
                >
                    @foreach($routes as $route)
                    <div
                        class="col-md-6 col-lg-4 col-xl-3"
                        style="margin:auto;"
                    >
                        <growing-button
                            style="margin:auto"
                            icon="{{ $route['icon'] }}"
                            route="{{ $route['url'] }}"
                        >
                            <h4>{{ $route['name'] }}</h4>
                        </growing-button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <script>
        document.body.style.backgroundColor = '#222';
    </script>
@endsection
