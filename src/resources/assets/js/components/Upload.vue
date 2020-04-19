<template>
    <div>
        <div class="row">
            <upload-toggler :style="{marginBottom: '0.5em'}" v-on:toggled="toggle"></upload-toggler>
        </div>
        <input 
            type="file"
            ref="fileToUpload"
            multiple
            :directory="!isFile"
            :webkitdirectory="!isFile"
            :moxdirectory="!isFile"
            :style="{
                width: '0.1px',
                height: '0.1px',
                opacity: '0',
                overflow: 'hidden',
                position: 'absolute',
                zIndex: -1,
            }"
            v-on:change="changeUploadText"
        >
        <button 
            :disabled="uploading"
            :style="selectButtonStyle"
            v-on:click="inputClick"
            v-on:mouseenter="enterSelect"
            v-on:mouseleave="selectButtonStyle.opacity = 0.8"
        >
            <div 
                :style="selectTextButtonstyle"
            >
                {{ uploadText }}
            </div>
        </button><!-- This comment 
        avoids spaces --><button 
            :style="uploadButtonStyle"
            v-on:click="uploadOrStop"
            v-on:mouseenter="uploadButtonStyle.backgroundColor = '#aaa'; uploadIconButtonStyle.opacity = 1"
            v-on:mouseleave="uploadButtonStyle.backgroundColor = '#bdc3c7'; uploadIconButtonStyle.opacity = 0.6"
        >
            <i 
                class="fas"
                :class="uploadIcon"
                :style="uploadIconButtonStyle"
            ></i>
        </button>
        <br/>
        <div :style="{width: '25em', padding: 0, marginLeft: '2.5em', marginTop: '1em'}">
            <p :style="{float: 'left'}">{{ currentName }}</p>
            <p :style="{float: 'right'}">{{ currentNb }} / {{ totalNb }}</p>
        </div>
        <br/>
        <div :style="{overflow: 'hidden', width: '26em', height: '1em', padding: 0, marginLeft: '2em', border: '1px solid #666', borderRadius: '0.5em'}">
            <div :style="{width: barProgress, backgroundColor: '#007bff', height: '1em'}"></div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        item: Object,
    },
    data() {
        return {
            barProgress: 0,
            currentNb: 0,
            totalNb: 0,
            currentName: '...',
            uploading: false,
            stopUpload: false,
            uploadIcon: 'fa-arrow-up',
            isFile: true,
            uploadText: 'Select files...',
            selectButtonStyle: {
                width: '16em',
                height: '3em',
                backgroundColor: '#007bff',
                fontSize: '1.33em',
                border: 'none',
                margin: 0,                        
                padding: 0,
                borderRadius: '1em 0 0 1em',
                position: 'relative',
                overflow: 'hidden',
                opacity: 0.8,
            },
            selectTextButtonstyle: {
                position: 'absolute',
                margin: 0,
                padding: 0,
                width: '14em',
                height: '3em',                            
                textAlign: 'center',
                marginTop: '-1.5em',
                marginLeft: '1em',
                verticalAlign: 'middle',
                lineHeight: '3em',
                color: '#bbb',
            },
            uploadButtonStyle: {
                width: '3em',
                height: '3em',
                fontSize: '1.33em',
                backgroundColor: '#bdc3c7',
                color: '#007bff',
                border: 'none',
                margin: 0,
                padding: 0,
                borderRadius: '0 1em 1em 0',
                position: 'relative',
                overflow: 'hidden',
            },
            uploadIconButtonStyle: {
                fontSize: '2em',
                position: 'absolute',
                marginTop: '-0.5em',
                marginLeft: '-1em',
                fontSize: '2em',
                width: '2em',
                height: '2em',
                opacity: 0.6,
            },
        }
    },
    methods: {
        uploadOrStop() {
            if (this.uploading) {
                this.stopUpload = true
            } else {
                this.uploadFiles()
            }
        },
        uploadFiles() {
            
            var me = this
            const input = this.$refs.fileToUpload

            this.totalNb = input.files.length
            if (this.totalNb == 0) {
                toastr.error('You must select at least one file to upload!')
                return
            }

            this.uploading = true
            this.uploadIcon = 'fa-times'
            
            const upload = () => {
                
                return new Promise((resolve, reject) => {

                    if (me.stopUpload) {
                        me.currentNb = me.totalNb                        
                        resolve()
                    }

                    const i = me.currentNb
                    const file = input.files[i]
                    console.log(file)
                    var formData = new FormData()

                    const extra = 2 * (('' + me.totalNb).length - 2)
                    me.currentName = file.name.length <= 34 - extra ? file.name : file.name.substring(0, 31 - extra) + '...'

                    formData.append('uploader', me.item.name)
                    formData.append('input_file_full_path_' + 0, file.webkitRelativePath == '' ? './' + file.name : file.webkitRelativePath)
                    formData.append('input_file_' + 0, file)

                    axios.post( '/upload',//this.upload_url,
                        formData,
                        {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            },
                            onUploadProgress: function( progressEvent ) {
                                me.barProgress = ((progressEvent.loaded / progressEvent.total) * 95) + '%'
                            }.bind(me),
                        }
                    )
                    .then(response => {
                        me.barProgress = '100%'
                        resolve()                        
                    })
                    .catch(error => {
                        toastr.error(error.message)
                        reject()
                    })
                })
            }

            const uploadAll = () => {
                return upload().then(() => {
                    if (++me.currentNb < me.totalNb) {
                        return uploadAll()
                    } else {
                        if (!me.stopUpload) {
                            toastr.success('Successfully added ' + input.files.length + ' file' + (input.files.length > 1 ? 's' : '') + ' to the ' + me.item.name + '\'s channel!')
                        }
                        me.currentNb = 0
                        me.totalNb = 0
                        me.currentName = '...'
                        input.value = '' // will make the input.files.length equal to 0
                        me.uploadText = me.isFile ? 'Select files...' : 'Select a folder...'
                        me.barProgress = 0
                        me.uploading = false
                        me.stopUpload = false
                        me.uploadIcon = 'fa-arrow-up'
                    }
                })
            }

            uploadAll()

        },
        inputClick() {
            this.$refs.fileToUpload.click()
        },
        changeUploadText() {
            const length = this.$refs.fileToUpload.files.length
            if (length > 0) {
                this.uploadText = length + ' file' + (length > 1 ? 's' : '') + ' selected'
            }
        },
        enterSelect() {
            if (!this.uploading) {
                this.selectButtonStyle.opacity = 1
            }
        },
        toggle() {
            this.isFile = !this.isFile
            this.uploadText = this.isFile ? 'Select files...' : 'Select a folder...'
        }
    }
}
</script>