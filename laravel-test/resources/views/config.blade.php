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
    {{Html::script("js/configuration.js")}}
@endsection

@section("content")
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Interfaces</div>
                    <div class="panel-body">
                        Interface configuration
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Port Forwarding</div>
                    <div class="panel-body">
                        <div id="ports-table"></div>
                        <button id="add-rule">Add Rule</button>
                    </div>
                </div>
            </div>
        </div>
    </div>      
    <script>
        /*$(function(){
            $.ajax({
                type: "POST",
                url: "/rule",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    input_port: 123,
                    output_port: 456
                }
            });
            $.ajax({
                type: "POST",
                url: "/rule",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    input_port: 500,
                    output_port: 5555
                }
            });
        // });*/
    </script>
@endsection