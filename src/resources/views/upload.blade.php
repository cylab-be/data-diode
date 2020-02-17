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
            <?php
                echo Form::open(array('url' => '/upload','files'=>'true'));
                echo Form::file('image');
                echo Form::submit('Upload File');
                echo Form::close();
            ?>
            <form action="/upload" enctype="multipart/form-data" method="post">
                <input id="uploadfolder" name="files[]" type="file" value="Input" multiple directory webkitdirectory moxdirectory>
                <input type="submit" value="Upload folder">
            </form>
        </div>
    </div>
@endsection
