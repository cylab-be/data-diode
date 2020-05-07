<template>
    <div 
        class="card text-center"
        :style="{
            cursor: 'pointer',
            position: 'relative',
        }"
        :title="dirPath + '/' + file.name"
        v-on:mouseover="mouseOver"
        v-on:mouseout="mouseOut"
        v-on:mouseleave="showOptions = false; confirmDownload = false; confirmRemove = false"
    >
        <i 
            class="fa fa-4x text-muted"
            :class="[mainIconClass]"
            :style="iconStyle"
        ></i>
        <button
            v-if="!showOptions"
            v-show="selected && !downloading && !deleting"
            v-on:click="startShowOptions"
            :style="optionsButtonStyle"
            :disabled="downloading || deleting"
            v-on:mouseover="optionsButtonStyle.opacity = 1; optionsIconStyle.color = '#fff'"
            v-on:mouseleave="optionsButtonStyle.opacity = 0.6; optionsIconStyle.color = '#ddd'"            
        >
            <i class="fas fa-ellipsis-v" :style="optionsIconStyle"></i>
        </button>
        <span 
            v-else
            :style="{
                float: 'right',
                position: 'absolute',
            }"
        >
            <button
                v-show="selected && !downloading && !deleting"
                v-on:click="downloadFile"
                :disabled="downloading || deleting"
                :style="downloadButtonStyle"
                v-on:mouseover="downloadButtonStyle.opacity = 1; downloadIconStyle.color = '#fff'"
                v-on:mouseleave="downloadButtonStyle.opacity = 0.6; downloadIconStyle.color = '#ddd'; confirmDownload = false"
            >
                <span v-if="confirmDownload" :style="downloadIconStyle">Confirm</span>
                <i v-else class="fas fa-arrow-down" :style="downloadIconStyle"></i>
            </button>
            <br/>
            <button
                v-show="selected && !downloading && !deleting"
                v-on:click="removeFile"
                :disabled="downloading || deleting"
                :style="removeButtonStyle"
                v-on:mouseover="removeButtonStyle.opacity = 1; removeIconStyle.color = '#fff'"
                v-on:mouseleave="removeButtonStyle.opacity = 0.6; removeIconStyle.color = '#ddd'; confirmRemove = false"
            >
                <span v-if="confirmRemove" :style="removeIconStyle">Confirm</span>
                <i v-else class="fas fa-times" :style="removeIconStyle"></i>
            </button>
        </span>        
        <div 
            class="card-body"
            :style="{
                padding: '2em',
            }"
        >
            <p  
                class="card-text text-muted"
                :style="paragraphStyle"
            >                                
                {{ file.name }}
            </p>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        file: Object,
        dirPath: String,
    },
    data() {
        return {
            optionsButtonStyle: {
                float: 'right',
                position: 'absolute',
                border: 'none',
                backgroundColor: '#999',
                borderRadius: '0.5em',
                opacity: 0.6,
                minWidth: '2em',
                height: '2em',
            },
            optionsIconStyle: {
                color: '#ddd',
            },
            downloadButtonStyle: {                
                border: 'none',
                backgroundColor: '#007bff',
                borderRadius: '0.5em',
                opacity: 0.6,
                minWidth: '2em',
                height: '2em',
                float: 'left',
            },
            downloadIconStyle: {
                color: '#ddd',
            },
            removeButtonStyle: {
                border: 'none',
                backgroundColor: '#dc3545',
                borderRadius: '0.5em',
                opacity: 0.6,
                minWidth: '2em',
                height: '2em',
                float: 'left',
            },
            removeIconStyle: {
                color: '#ddd',
            },
            iconStyle: {
                color:'darkgray',
            },
            paragraphStyle: {
                whiteSpace: 'nowrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                display: 'inline-block',
                maxWidth: '100%',
                color: 'darkgray',
            },
            selected: false,
            mainIconClass: 'fa-file',
            downloading: false,
            deleting: false,
            showOptions: false,
            confirmDownload: false,
            confirmRemove: false,
        }
    },
    methods: {
        mouseOver() {
            this.selected = true
            this.iconStyle.color = 'lightblue'
            this.paragraphStyle.color = 'lightblue'
            this.$emit('change-path', this.dirPath + '/' + this.file.name, true)
        },
        mouseOut() {
            this.selected = false
            this.iconStyle.color = 'darkgray'
            this.paragraphStyle.color = 'darkgray'
            this.$emit('change-path', this.dirPath, false)
        },
        downloadFile() {
            event.stopPropagation()
            this.confirmRemove = false
            if (!this.confirmDownload) {
                this.confirmDownload = true
                return
            }
            var me = this
            this.mainIconClass = 'fa-file-archive blink-me'
            this.downloading = true
            const path = this.dirPath + '/' + this.file.name
            axios({
                url: '/download', //your url
                method: 'POST',
                responseType: 'blob', // important
                data: {
                    path: path,
                }
            }).then((response) => {
                me.mainIconClass = 'fa-file'
                me.downloading = false
                me.showOptions = false
                me.confirmDownload = false
                me.confirmRemove = false
                const url = window.URL.createObjectURL(new Blob([response.data]))
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', me.file.name) //or any other extension
                document.body.appendChild(link)
                link.click()
            }).catch(error => {
                me.mainIconClass = 'fa-file'
                me.downloading = false
                me.showOptions = false
                me.confirmDownload = false
                me.confirmRemove = false                
                if (error.response.status != 422) {
                    toastr.error('An error occured. Impossible to download ' + me.file.name)
                }
                if (error.response.data.errors.path) {
                    toastr.error(error.response.data.errors.path)
                }
            })
        },
        removeFile(event) {
            event.stopPropagation()
            this.confirmDownload = false
            if (!this.confirmRemove) {
                this.confirmRemove = true
                return
            }
            var me = this
            const path = this.dirPath + '/' + this.file.name
            this.mainIconClass = 'fa-trash blink-me'
            this.deleting = true
            axios({
                url: '/remove', //your url
                method: 'POST',
                data: {
                    path: path,
                    name: me.file.name
                }
            }).then((response) => {
                me.mainIconClass = 'fa-file'
                me.showOptions = false
                me.confirmDownload = false
                me.confirmRemove = false
                me.deleting = false
                window.location.reload()
            }).catch(error => {
                me.mainIconClass = 'fa-file'
                me.showOptions = false
                me.confirmDownload = false
                me.confirmRemove = false
                me.deleting = false                
                if (error.response.status != 422) {
                    toastr.error('An error occured. Impossible to remove ' + me.file.name)
                }
                if (error.response.data.errors.name) {
                    toastr.error(error.response.data.errors.name)
                }
                if (error.response.data.errors.path) {
                    toastr.error(error.response.data.errors.path)
                }
            })      
        },
        startShowOptions(event) {
            event.stopPropagation()
            this.showOptions = true
        },        
    }
}
</script>

<style scoped>

</style>