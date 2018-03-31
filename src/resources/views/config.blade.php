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
            <ul class="nav nav-tabs">
                <li class="active"><a href="#rules" data-toggle="tab">Rules</a></li>
                <li><a href="#interface" data-toggle="tab">Interface</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade in active" id="rules">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="panel panel-default">
                                <div class="panel-heading">Port Forwarding</div>
                                <div class="panel-body">
                                    <div id="ports-table"></div>
                                    <button id="add-rule" class="btn btn-primary">Add Rule</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade in" id="interface">
                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="panel panel-default">
                                <div class="panel-heading">Network Configuration</div>
                                <div class="panel-body">
                                    <form id="network-configuration" class="form-horizontal">
                                        <div class="form-group">
                                            <label for="ip" class="col-md-4 control-label">IP Address</label>
                                            <div class="col-md-6">
                                                <input id="ip" type="text" class="form-control" name="ip">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="netmask" class="col-md-4 control-label">Subnet Mask</label>
                                            <div class="col-md-6">
                                                <input id="netmask" type="text" class="form-control" name="netmask">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-6 col-md-offset-4">
                                                <button type="submit" class="btn btn-primary">
                                                    Save
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
