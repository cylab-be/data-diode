<template>
    <div>
        <span v-if="diodein">
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
        </span>
        <span v-else>
            <hr class="window-title-bottom-bar"/>
            <button 
                class="button"
                :style="{verticalAlign: 'middle'}"
                v-on:click="toStorage"
            ><span>STORAGE </span></button>
        </span>
    </div>
</template>

<script>
export default {
    props: {
        item: Object,
        maxUploadSize: Number,
        maxUploadSizeErrorMessage: String,
        diodein: Boolean,
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
            var size = 0
            for (let i = 0; i < input.files.length; i++) {
                size = input.files[i].size // given in bytes (octets)
                if (size > this.maxUploadSize) {
                    toastr.error(this.maxUploadSizeErrorMessage)
                    return
                }
            }

            this.uploading = true
            this.uploadIcon = 'fa-times'
            this.item.state = '1' // to make the Uploader's icon spin
            
            const upload = () => {
                
                return new Promise((resolve, reject) => {

                    if (me.stopUpload) {
                        me.currentNb = me.totalNb                        
                        resolve()
                    }

                    const i = me.currentNb
                    const file = input.files[i]
                    var formData = new FormData()

                    const extra = 2 * (('' + me.totalNb).length - 2)
                    me.currentName = file.name.length <= 34 - extra ? file.name : file.name.substring(0, 31 - extra) + '...'

                    formData.append('input_file_full_path', file.webkitRelativePath == '' ? './' + file.name : file.webkitRelativePath)
                    formData.append('input_file', file)

                    axios.post( '/upload/' + me.item.id,
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
                        me.currentNb = 0
                        me.totalNb = 0
                        me.currentName = '...'
                        input.value = '' // will make the input.files.length equal to 0
                        me.uploadText = me.isFile ? 'Select files...' : 'Select a folder...'
                        me.barProgress = 0
                        me.uploading = false
                        me.stopUpload = false
                        me.uploadIcon = 'fa-arrow-up'
                        if (error.response.status != 422) {
                            toastr.error(error.response.data.message)
                        }
                        if (error.response.data.errors.input_file) {
                            toastr.error(error.response.data.errors.input_file)
                        }
                        if (error.response.data.errors.input_file_full_path) {
                            toastr.error(error.response.data.errors.input_file_full_path)
                        }
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
        },
        toStorage() {
            window.location.href = '/storage/' + this.item.name
        },

    }
}
</script>

<style scoped>

.button {
  display: inline-block;
  border-radius: 1em;
  background-color: #007bff;
  border: none;
  color: #FFFFFF;
  text-align: center;
  font-size: 1.33em;
  padding: 0.8em;
  width: 8em;
  transition: all 0.5s;
  cursor: pointer;
  margin: 0.4em;
}

.button span {
  cursor: pointer;
  display: inline-block;
  position: relative;
  transition: 0.5s;
}

.button span:after {
  content: '\00bb';
  position: absolute;
  opacity: 0;
  top: 0;
  right: -0.8em;
  transition: 0.5s;
}

.button:hover span {
  padding-right: 1em;
}

.button:hover span:after {
  opacity: 1;
  right: 0;
}

</style>