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
            @if ($cannot_be_seen)
                <h4 class="text-danger name-info">
                    This folder may contain symlinks or other unsupported types. Its content cannot be accessed.
                </h4>
            @else
                <h4 class="text-primary name-info">
                    {{ $dirPath }}
                </h4>
            @endif
        </div>
    </div>
    <br/>
    <br/>
    <div class="container-fluid" style="width:50%;">
        <div class="tab-v1">
            <div class="row">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 text-center">
                <div class="card card-folder-root">
                    <i class="fa fa-folder fa-4x i-folder-root"></i>
                    <div class="card-body" style="padding:2em;">
                        <p class="card-text p-folder-root"
                            style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;display: inline-block;max-width: 100%;"
                        >
                            ..
                        </p>
                    </div>
                    <script>
                        $(function(){
                            $( ".card-folder-root" ).mouseover(function(){
                                $(".i-folder-root, .p-folder-root").addClass('text-primary')
                                $('.name-info').html("{{ count($quick_navigation) <= 1 ? '.' : $quick_navigation[count($quick_navigation) - 1]['path'] }}")
                            })
                            $( ".card-folder-root" ).mouseout(function(){
                                $(".i-folder-root, .p-folder-root").removeClass('text-primary')
                                $('.name-info').html("{{ $dirPath }}")
                            })
                            $( ".card-folder-root" ).click(function(){
                                $(".i-folder-root, .p-folder-root").removeClass('text-primary')
                                $( location ).attr("href", "/storage/" + "{{ count($quick_navigation) <= 1 ? '' : $quick_navigation[count($quick_navigation) - 1]['path'] }}");
                            })
                        })
                    </script>                        
                </div>
                </div>
                @foreach ($directories as $key)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 text-center">
                    <div class="card {{ 'card-folder-' . $loop->index }}">
                        <i class="fa fa-folder fa-4x {{ 'i-folder-' . $loop->index }}"></i>
                        <div class="card-body" style="padding:2em;">
                            <p class="card-text {{ 'p-folder-' . $loop->index }}"
                                style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;display: inline-block;max-width: 100%;"
                            >
                                {{ $key['name'] }}
                            </p>
                        </div>
                        <script>
                            $(function(){
                                $( ".{{ 'card-folder-' . $loop->index }}" ).mouseover(function(){
                                    $(".{{ 'i-folder-' . $loop->index }}, .{{ 'p-folder-' . $loop->index }}").addClass('text-primary')
                                    $('.name-info').html("{{ $dirPath . '/' . $key['name'] }}")
                                })
                                $( ".{{ 'card-folder-' . $loop->index }}" ).mouseout(function(){
                                    $(".{{ 'i-folder-' . $loop->index }}, .{{ 'p-folder-' . $loop->index }}").removeClass('text-primary')
                                    $('.name-info').html("{{ $dirPath }}")
                                })
                                $( ".{{ 'card-folder-' . $loop->index }}" ).click(function(){
                                    $(".{{ 'i-folder-' . $loop->index }}, .{{ 'p-folder-' . $loop->index }}").removeClass('text-primary')
                                    $( location ).attr("href", "/storage/" + "{{ $key['path'] }}");
                                })
                            })
                        </script>                        
                    </div>
                </div>
                @endforeach
                @foreach ($files as $key)
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 text-center">
                    <div class="card text-center {{ 'card-file-' . $loop->index }}" title="{{ $dirPath . '/' . $key['name'] }}">
                        <i class="fa fa-file fa-4x text-muted {{ 'i-file-' . $loop->index }}" style="color:darkgray;"></i>
                        <div class="card-body" style="padding:2em;">
                            <p  class="card-text text-muted {{ 'p-file-' . $loop->index }}"
                                style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;display: inline-block;max-width: 100%; color:darkgray;"
                            >                                
                                {{ $key['name'] }}
                            </p>
                        </div>
                        <script>
                            $(function(){
                                $( ".{{ 'card-file-' . $loop->index }}" ).mouseover(function(){
                                    $(".{{ 'i-file-' . $loop->index }}, .{{ 'p-file-' . $loop->index }}").css('color', 'lightblue')
                                    $('.name-info').html("{{ $dirPath . '/' . $key['name'] }}")
                                })
                                $( ".{{ 'card-file-' . $loop->index }}" ).mouseout(function(){
                                    $(".{{ 'i-file-' . $loop->index }}, .{{ 'p-file-' . $loop->index }}").css('color', 'darkgray')
                                    $('.name-info').html("{{ $dirPath }}")
                                })
                                $( ".{{ 'card-file-' . $loop->index }}" ).click(function(){

                                })
                            })
                        </script>                          
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    <script>
        $(function(){
            // $( '.card-folder-root' ).css('display', 'none')
            // $( '.card-folder-root' ).css('display', 'inline-block')
        })
    </script>
@endsection