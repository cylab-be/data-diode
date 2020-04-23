<template>
    <div 
        class="card text-center"
        :style="{cursor: 'pointer'}"
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
            :style="{
                float: 'right',
                position: 'absolute',
            }"
            :disabled="downloading || deleting"
        >
            <i class="fas fa-ellipsis-v"></i>
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
            >
                <span v-if="confirmDownload">Confirm</span>
                <i v-else class="fas fa-arrow-down"></i>
            </button>
            <br/>
            <button
                v-show="selected && !downloading && !deleting"
                v-on:click="removeFile"
                :disabled="downloading || deleting"
            >
                <span v-if="confirmRemove">Confirm</span>
                <i v-else class="fas fa-times"></i>
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
                toastr.error('An error occured. Impossible to download ' + me.file.name)
            })
        },
        removeFile(event) {
            event.stopPropagation()
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
                toastr.error('An error occured. Impossible to remove ' + me.file.name)
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