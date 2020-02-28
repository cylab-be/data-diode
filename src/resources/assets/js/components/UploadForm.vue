<template>
    <div class="box">
        <span v-if="folder_only">            
            <div 
                class="dropZoneElementFolder"
                v-on:dragover="dropZoneColorOn"                
                v-on:dragleave="dropZoneColorOff"
                v-on:dragstart="dropZoneColorOn"
                v-on:dragend="dropZoneColorOff"
                v-on:mouseover="dropZoneColorOn"
                v-on:mouseleave="dropZoneColorOff"
                :style="{backgroundColor: dropZoneBackgroundColor}"
            >
                <div class="textWrapper">                    
                    <p class="dropImageHereText">
                        Drop a folder<br/>
                        <i class="fa fa-angle-double-right fa-lg"></i>
                        <strong>here</strong>
                        <i class="fa fa-angle-double-left fa-lg"></i>
                    </p>
                </div>
            </div>
        </span>
        <span v-else>
            <div 
                class="dropZoneElementFiles"
                v-on:dragover="dropZoneColorOn"                
                v-on:dragleave="dropZoneColorOff"
                v-on:dragstart="dropZoneColorOn"
                v-on:dragend="dropZoneColorOff"
                v-on:mouseover="dropZoneColorOn"
                v-on:mouseleave="dropZoneColorOff"
                :style="{backgroundColor: dropZoneBackgroundColor}"
            >
                <div class="textWrapper">
                    <p class="dropImageHereText">
                        Drop files<br/>
                        <i class="fa fa-angle-double-right fa-lg"></i>                        
                        <strong>here</strong>                        
                        <i class="fa fa-angle-double-left fa-lg"></i>
                    </p>
                </div>
            </div>
        </span>        
        <!-- 
            To see the effect of each atribute see:
             https://docs.telerik.com/kendo-ui/api/javascript/ui/upload
        -->
        <div style="border-left: solid grey thin; border-right: solid grey thin; border-top:solid grey 1em;">
        <kendo-upload
            ref="upload"
            name="files"
            :async-save-url="upload_url"
            :async-remove-url="'custom-remove-url'"            
            :directory="folder_only"
            :directory-drop="folder_only"
            :async-batch="true"
            :validation-max-file-size="1024 * 1024 * 1024"
            :localization-invalid-max-file-size="'The file size cannot exceed 1GB'"
            :localization-invalid-files="'<br/><p style=\'font-size:1.5em;color:red;border-style:solid;border-width:thin;padding:1em;\'>Invalid file(s). Please check file upload requirements. Some files may be bigger than 1Gb.</p>'"
            :localization-select="folder_only ? 'Select a folder to upload...' : 'Select files to upload...'"
            :show-file-list="false"
            :drop-zone="folder_only ? '.dropZoneElementFolder' : '.dropZoneElementFiles'"
            @select="onSelect"
            @upload="onUpload"
            @error="onError"
            @success="onSuccess"
            :key="folder_only"
        >
        </kendo-upload>
        </div>
        <div style="width:100%;background-color:grey;">
            <div :style="{width: barProgress, backgroundColor: '#007bff', height: '1em'}">
                    
            </div>
        </div>  
        <div class="products">
            <div
                :style="productStyle"
                v-for="(file, index) in showedFiles" :key="index"
            >                
                <span style="padding-left:10%;">{{ file }}</span>
                <div :style="index == showedFiles.length - 1 ? {} : {borderBottom: 'solid grey thin'}"></div>
            </div>
        </div>
    </div>
</template>

<script>
import '@progress/kendo-ui';
import { Upload, UploadInstaller } from '@progress/kendo-upload-vue-wrapper';

Vue.use(UploadInstaller);

export default {
    props: {
        folder_only: Boolean,
        upload_url: String,
    },
    data() {
        return {
            dropZoneBackgroundColor: '#8f8f8f',
            showedFiles: [],
            barProgress: 0,
            productStyle: {
                //textAlign:'center',
                //margin:'auto',
                //width: '50%',
                //borderWidth: 'thin',
                //borderStyle: 'solid',
                //padding: '1%',                
                color: 'inherit',                
            },
            index: 0,
            nbFilesUploaded: 0,
        }
    },
    methods: {        
        dropZoneColorOn() {
            this.dropZoneBackgroundColor = '#666'
        },
        dropZoneColorOff() {
            this.dropZoneBackgroundColor = '#8f8f8f'
        },
        onUpload(e) {
            var upload = this.$refs.upload.kendoWidget();
            upload.disable();
            var me = this;
            me.showedFiles = []
            for (var i = 0; i < e.files.length; i++) {
                var file = e.files[i].rawFile;
                if (file) {                    
                    if (e.files.length <= 10) {
                        var filename = file.name.length > 20 ? file.name.slice(0, 14) + '...' + file.name.slice(file.name.length - 3, file.name.length) : file.name
                        me.showedFiles.push(filename)
                    } else {
                        if (i < 10) {
                            var filename = file.name.length > 20 ? file.name.slice(0, 14) + '...' + file.name.slice(file.name.length - 3, file.name.length) : file.name
                            me.showedFiles.push(filename)
                        }
                        if (i == 9) {
                            me.showedFiles[9] += ' + ' + (e.files.length - 10) + ' files...'
                        }
                    }                        
                }
            }
        },
        onSelect(e) {
            var me = this;
            me.productStyle.color = 'inherit'
            me.showedFiles = [];
            me.barProgress = 0 + '%';
            me.nbFilesUploaded = 0;
            /*if (e.files.length > 20) {
                toastr.error('You cannot upload more than 20 files at once.')
                var upload = this.$refs.upload.kendoWidget();
                upload.removeAllFiles();                
                upload.enable();
                e.preventDefault();
            }*/
        },
        onError(e) {
            toastr.error("Failed to upload. Note that the max file size is 1GB.");
            var upload = this.$refs.upload.kendoWidget();
            upload.removeAllFiles();
            upload.enable();
            e.preventDefault();
        },
        onSuccess(e) {
                                    
        }
    },
    mounted() {

        var me = this;

        var upload = this.$refs.upload.kendoWidget();

        upload._module.postFormData = function (url, data, fileEntry, xhr) {
            
            var module = this;
            var counter = 0;
            me.index = 0;
            const files = fileEntry.data("files")
            
            const firstPass = (self) => {
                return new Promise(function(resolve, reject) {                    
                    let formData = new FormData();
                    formData.append('input_file_full_path_' + 0, files[self.index].name);
                    formData.append("input_file_" + 0, files[self.index].rawFile);
                    self.index++;
                    axios.post( url,
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            },
                            onUploadProgress: function( progressEvent ) {
                                var donePerc = 100 * (self.index - 1) / files.length
                                var doingPerc = (progressEvent.loaded / progressEvent.total) * 95 * 1 / files.length
                                self.barProgress = parseInt(donePerc + doingPerc) + '%';
                            }.bind(this),
                        }
                    )
                    .then(function(response){
                        self.nbFilesUploaded += 1
                        self.barProgress = parseInt( 100 * self.index / files.length ) + '%';
                        var e = { target: { responseText: '{"uploaded":true}', statusText: "OK", status: 200 } };
                        if (self.nbFilesUploaded == files.length) {
                            module.onRequestSuccess.call(module, e, fileEntry);
                            self.productStyle.color = '#007bff';                 
                            upload.enable();
                            toastr.success('Successfully uploaded ' + files.length + ' file' + (files.length > 1 ? 's' : '') + '!');
                        }
                        resolve(self);
                    })
                    .catch(function(error){
                        toastr.error(error);
                        upload.enable();
                        reject(error);
                    });
                })
            }

            const secondPass = (self) => {
                return new Promise(function(resolve, reject) {
                    if (self.index != 9 || files.length != 10) {
                        resolve(self);
                        return;
                    }
                    let formData = new FormData();
                    formData.append('input_file_full_path_' + 0, files[self.index].name);
                    formData.append("input_file_" + 0, files[self.index].rawFile);
                    self.index++;
                    axios.post( url,
                        formData,
                        {
                        headers: {
                                'Content-Type': 'multipart/form-data'
                            },
                            onUploadProgress: function( progressEvent ) {
                                var donePerc = 100 * (self.index - 1) / files.length
                                var doingPerc = (progressEvent.loaded / progressEvent.total) * 95 * 1 / files.length
                                self.barProgress = parseInt(donePerc + doingPerc) + '%';
                            }.bind(this),
                        }
                    )
                    .then(function(response){
                        self.nbFilesUploaded += 1
                        self.barProgress = parseInt( 100 * self.index / files.length ) + '%';
                        var e = { target: { responseText: '{"uploaded":true}', statusText: "OK", status: 200 } };
                        module.onRequestSuccess.call(module, e, fileEntry);                  
                        self.productStyle.color = '#007bff'
                        upload.enable();
                        toastr.success('Successfully uploaded ' + files.length + ' file' + (files.length > 1 ? 's' : '') + '!');
                        resolve(self);
                    })
                    .catch(function(error){
                        toastr.error(error);
                        upload.enable();
                        reject(error);
                    });
                })
            }

            const thirdPass = (self) => {
                return new Promise(function(resolve, reject) {
                    if (files.length <= 10) {
                        resolve(self);
                        return;
                    }
                    var rest = files.length - self.index;
                    if (rest > 20) {
                        rest = 20
                    }
                    var limit = self.index + rest
                    let formData = new FormData();
                    let j = 0;
                    while (self.index < limit) {
                        formData.append('input_file_full_path_' + j, files[self.index].name);
                        formData.append("input_file_" + j, files[self.index].rawFile);
                        self.index++;
                        j++;
                    }
                    axios.post( url,
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            },
                            onUploadProgress: function( progressEvent ) {
                                var donePerc = 100 * (self.index - j) / files.length
                                var doingPerc = (progressEvent.loaded / progressEvent.total) * 95 * j / files.length
                                self.barProgress = parseInt(donePerc + doingPerc) + '%';
                            }.bind(this),
                        }
                    )
                    .then(function(response){
                        self.nbFilesUploaded += j
                        self.barProgress = parseInt( 100 * self.index / files.length ) + '%';
                        var e = { target: { responseText: '{"uploaded":true}', statusText: "OK", status: 200 } };
                        if (self.nbFilesUploaded == files.length) {
                            module.onRequestSuccess.call(module, e, fileEntry);
                            self.productStyle.color = '#007bff'
                            upload.enable();
                            toastr.success('Successfully uploaded ' + files.length + ' file' + (files.length > 1 ? 's' : '') + '!');
                        }
                        resolve(self);
                    })
                    .catch(function(error){
                        toastr.error(error);
                        upload.enable();
                        reject(error);
                    })
                })
            }

            function next(entity) {
                return firstPass(entity).then(self => {
                    if (self.index < 9 && self.index < files.length) {
                        return next(self);
                    } else {
                        return self;
                    }
                });
            }

            function next2(entity) {
                return secondPass(entity).then(self =>{
                    return self;
                })
            }

            function next3(entity) {
                return thirdPass(entity).then(self => {
                    if (self.index < files.length) {
                        return next3(self);
                    } else {
                        return self;
                    }
                });
            }
            
            next(me).then(self => {
                next2(self).then(self2 => {
                    next3(self2).then(self3 => {
                        console.log(self3)
                    })
                })
            }).catch(error => {
                console.log(error)
            })
        };

        upload._submitRemove = function (fileNames, eventArgs, onSuccess, onError) {
            onSuccess();            
        };
        upload._module.stopUploadRequest= function(fileEntry) {};
    },
}
</script>

  <style scoped>
   
    .dropZoneElementFolder {
        position: relative;
        display: inline-block;
        width: 100%;
        height: 10em;
        text-align: center;
        user-select: none;
        border-radius: 2em 2em 0em 0em;
    }

    .dropZoneElementFiles {
        position: relative;
        display: inline-block;
        width: 100%;
        height: 10em;
        text-align: center;
        user-select: none;
        border-radius: 2em 2em 0em 0em;
    }

    .textWrapper {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 100%;
        font-size: 4em;
        line-height: 0.7em;
        font-family: Arial,Helvetica,sans-serif;
        color: #000;
    }

    .dropImageHereText {
        color: #c7c7c7;
        text-transform: uppercase;
        font-size: 0.5em;
    }

    .products {
        border-left: solid grey thin;
        border-right: solid grey thin;
        border-bottom: solid grey thin;
        margin-bottom: 2em; 
        font-size: 1em;
    }

  </style>