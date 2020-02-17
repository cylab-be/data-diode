<template>
    <div class="container">
        <div class="large-12 medium-12 small-12 cell">
            <label>File
                <input type="file" id="file" ref="file" v-on:change="handleFileUpload()"/>
            </label>
            <br>
            <progress max="100" :value.prop="uploadPercentage"></progress>
            <br>
            <button v-on:click="submitFile()">Submit</button>
        </div>
    </div>
</template>

<script>
    export default {
        /*
        Defines the data used by the component
        */
        data(){
            return {
                file: '',
                uploadPercentage: 0,
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        },
        methods: {
            /*
            Handles a change on the file upload
            */
            handleFileUpload(){
                this.file = this.$refs.file.files[0];
            },
            /*
            Submits the file to the server
            */
            submitFile(){
                /*
                Initialize the form data
                */
                let formData = new FormData();

                /*
                Add the form data we need to submit
                */
                formData.append('input_file', this.file);

                /*
                Make the request to the POST /single-file URL
                */
                axios.post( '/upload',
                    formData,
                    {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        },
                        onUploadProgress: function( progressEvent ) {
                            this.uploadPercentage = parseInt( Math.round( ( progressEvent.loaded / progressEvent.total ) * 100 ))
                        }.bind(this),
                    }
                ).then(function(response){
                    alert('SUCCESS');
                })
                .catch(function(error){
                    console.log('FAILURE!!');
                    console.log(error);
                });
            },
        }
    }
</script>