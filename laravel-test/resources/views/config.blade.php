@extends("layouts.app")

@section('head')
    @parent
    {{Html::script("js/jquery-3.2.1.min.js")}}
    {{Html::script("js/jquery-ui.min.js")}}
    {{Html::script("js/tabulator.min.js")}}
    {{Html::style("css/tabulator.min.css")}}
    {{Html::style("css/tabulator_semantic-ui.min.css")}}
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