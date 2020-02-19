<template>
    <div id="dirformdiv">
        <h4 v-if="folder_only">
            <i class="fa fa-folder fa-lg"></i> Drop a folder below
        </h4>
        <h4 v-else>
            <i class="fa fa-file fa-lg"></i> Drop files below
        </h4>
        <kendo-upload ref="upload"
                    name="files"
                    :async-save-url="'custom-save-url'"
                    :async-remove-url="'custom-remove-url'"
                    :async-chunk-size="11000"
                    :directory="folder_only"
                    :directory-drop="folder_only">
        </kendo-upload>
    </div>
</template>

<script>
import '@progress/kendo-ui';
import { Upload, UploadInstaller } from '@progress/kendo-upload-vue-wrapper';

Vue.use(UploadInstaller);

export default {
    props: {
        folder_only: Boolean
    },
    mounted: function () {

        var upload = this.$refs.upload.kendoWidget();

        upload._module.postFormData = function (url, data, fileEntry, xhr) {
            var module = this;
            var counter = 0;
            var uid = fileEntry.data("files")[0].uid;
            let formData = new FormData();
            formData.append('input_file_full_path', fileEntry.data("files")[0].name);
            formData.append("input_file", fileEntry.data("files")[0].rawFile);
            // POST
            axios.post( '/upload/file',
                formData,
                {
                    headers: {
                        //'Content-Type': 'multipart/form-data'
                    },
                    onUploadProgress: function( progressEvent ) {
                        counter = parseInt( Math.round( ( progressEvent.loaded / progressEvent.total ) * 95 ))
                        module.upload._onFileProgress({ target: $(fileEntry, module.upload.wrapper) }, counter);
                    }.bind(this),
                }
            ).then(function(response){
                // console.log(response)
                counter = 100;
                module.upload._onFileProgress({ target: $(fileEntry, module.upload.wrapper) }, counter);
                var e = { target: { responseText: '{"uploaded":true,"fileUid":"' + uid + '"}', statusText: "OK", status: 200 } };
                /*setTimeout(function() {                    
                    $(fileEntry).find('.k-button').click();
                }, 2000);*/
                module.onRequestSuccess.call(module, e, fileEntry);
            })
            .catch(function(error){
                // console.log(error);
            });
        };

        upload._submitRemove = function (fileNames, eventArgs, onSuccess, onError) {
            onSuccess();            
        };
        upload._module.stopUploadRequest= function(fileEntry) {};
    },
}
</script>

  <style>
    #dirformdiv {padding: 10px;}
    .example-wrapper { min-height: 280px; align-content: flex-start; }
    .example-wrapper p, .example-col p { margin: 0 0 10px; }
    .example-wrapper p:first-child, .example-col p:first-child { margin-top: 0; }
    .example-col { display: inline-block; vertical-align: top; padding-right: 20px; padding-bottom: 20px; }
    .example-config { margin: 0 0 20px; padding: 20px; background-color: rgba(0,0,0,.03); border: 1px solid rgba(0,0,0,.08); }
    .event-log { margin: 0; padding: 0; max-height: 100px; overflow-y: auto; list-style-type: none; border: 1px solid rgba(0,0,0,.08); background-color: #fff; }
    .event-log li {margin: 0; padding: .3em; line-height: 1.2em; border-bottom: 1px solid rgba(0,0,0,.08); }
    .event-log li:last-child { margin-bottom: -1px;}
  </style>