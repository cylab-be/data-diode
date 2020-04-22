<template>
    <div 
        class="card"
        :style="{
            cursor: 'pointer',
            position: 'relative',
        }"
        v-on:mouseover="mouseOver"
        v-on:mouseout="mouseOut"
        v-on:click="click"
    >
        <i 
            class="fa fa-4x"
            :class="[textPrimaryClass, downloadIconClass]"
        ></i>
        <button 
            v-show="selected && !downloading"
            v-on:click="downloadFolder"
            :style="{
                float: 'right',
                position: 'absolute',
            }"
            :disabled="downloading"
        >
            <i class="fas fa-arrow-up"></i>
        </button>
        <div 
            class="card-body"
            :style="{
                padding: '2em',
            }"
        >
            <p 
                class="card-text"
                :class="textPrimaryClass"
                :style="{
                    whiteSpace: 'nowrap',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    display: 'inline-block',
                    maxWidth: '100%',
                }"
            >
                {{ folder.name }}
            </p>
        </div>        
    </div>
</template>

<script>
export default {
    props: {
        folder: Object,
        dirPath: String,
    },
    data() {
        return {
            textPrimaryClass: '',
            selected: false,
            downloadIconClass: 'fa-folder',
            downloading: false,
        }
    },
    methods: {
        mouseOver() {
            this.selected = true
            this.textPrimaryClass = 'text-primary'
            this.$emit('change-path', this.dirPath + '/' + this.folder.name, false)
        },
        mouseOut() {
            this.selected = false
            this.textPrimaryClass = ''
            this.$emit('change-path', this.dirPath, false)

        },
        click() {
            if (!this.downloading) {
                this.textPrimaryClass = ''
                window.location.href = '/storage/' + this.folder.path
            }
        },
        downloadFolder(event) {
            event.stopPropagation()
            var me = this
            const path = this.dirPath + '/' + this.folder.name
            this.downloadIconClass = 'fa-file-archive blink-me'
            this.downloading = true
            axios({
                url: '/download', //your url
                method: 'POST',
                data: {
                    name: me.folder.name,
                    path: path,
                    type: 'folder',
                }
            }).then((response) => {
                me.downloadIconClass = 'fa-download blink-me'
                //toastr.success(me.folder.name + ' has been successfully compressed!')
                axios({
                    url: '/download', //your url
                    method: 'POST',
                    responseType: 'blob', // important
                    data: {
                        path: path + '.zip',
                        type: 'file',
                    }
                }).then((response) => {
                    me.downloadIconClass = 'fa-folder'
                    me.downloading = false
                    const url = window.URL.createObjectURL(new Blob([response.data]))
                    const link = document.createElement('a')
                    link.href = url
                    link.setAttribute('download', me.folder.name + '.zip') //or any other extension
                    document.body.appendChild(link)
                    link.click()
                }).catch(error => {
                    me.downloadIconClass = 'fa-folder'
                    me.downloading = false
                    toastr.error('An error occured. Impossible to download ' + me.folder.name + '.zip')
                })
            }).catch(error => {
                me.downloadIconClass = 'fa-folder'
                me.downloading = false
                toastr.error(error.response.data.message)
            })
        },
    }
}
</script>

<style scoped>

.blink-me {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
}

</style>