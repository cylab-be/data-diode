<template>
    <div 
        class="card"
        :style="{
            cursor: 'pointer',
            position: 'relative',
        }"
        v-on:mouseover="mouseOver"
        v-on:mouseout="mouseOut"
        v-on:mouseleave="showOptions = false; confirmDownload = false; confirmRemove = false"
        v-on:click="click"
    >
        <i 
            class="fa fa-4x"
            :class="[textPrimaryClass, mainIconClass]"
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
                v-on:click="downloadFolder"
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
                v-on:click="removeFolder"
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
            textPrimaryClass: '',
            selected: false,
            mainIconClass: 'fa-folder',
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
            this.textPrimaryClass = 'text-primary'
            this.$emit('change-path', this.dirPath + '/' + this.folder.name, false)
        },
        mouseOut() {
            this.selected = false
            this.textPrimaryClass = ''            
            this.$emit('change-path', this.dirPath, false)

        },
        click() {
            this.showOptions = false
            if (!this.downloading && !this.deleting) {
                this.textPrimaryClass = ''
                window.location.href = '/storage/' + this.folder.path
            }
        },
        downloadFolder(event) {
            event.stopPropagation()
            this.confirmRemove = false
            if (!this.confirmDownload) {
                this.confirmDownload = true
                return
            }
            var me = this
            const path = this.dirPath + '/' + this.folder.name
            this.mainIconClass = 'fa-file-archive blink-me'
            this.downloading = true
            const time = new Date().getTime()
            axios({
                url: '/zip', //your url
                method: 'POST',
                data: {
                    time: time,
                    name: me.folder.name,
                    path: path,
                }
            }).then((response) => {
                me.mainIconClass = 'fa-download blink-me'
                axios({
                    url: '/getzip', //your url
                    method: 'POST',
                    responseType: 'blob', // important
                    data: {
                        time: time,
                        name: me.folder.name,
                    }
                }).then((response) => {
                    me.mainIconClass = 'fa-folder'
                    me.downloading = false
                    me.showOptions = false
                    me.confirmDownload = false
                    me.confirmRemove = false
                    const url = window.URL.createObjectURL(new Blob([response.data]))
                    const link = document.createElement('a')
                    link.href = url
                    link.setAttribute('download', me.folder.name + '.zip') //or any other extension
                    document.body.appendChild(link)
                    link.click()
                }).catch(error => {
                    me.mainIconClass = 'fa-folder'
                    me.downloading = false
                    me.showOptions = false
                    me.confirmDownload = false
                    me.confirmRemove = false
                    toastr.error('An error occured. Impossible to download ' + me.folder.name + '.zip')
                })
            }).catch(error => {
                me.mainIconClass = 'fa-folder'
                me.downloading = false
                me.showOptions = false
                me.confirmDownload = false
                me.confirmRemove = false
                toastr.error(error.response.data.message)
            })
        },
        removeFolder(event) {
            event.stopPropagation()
            this.confirmDownload = false
            if (!this.confirmRemove) {
                this.confirmRemove = true
                return
            }
            var me = this
            const path = this.dirPath + '/' + this.folder.name
            this.mainIconClass = 'fa-trash blink-me'
            this.deleting = true
            axios({
                url: '/remove', //your url
                method: 'POST',
                data: {
                    path: path,
                    name: me.folder.name,
                }
            }).then((response) => {
                me.mainIconClass = 'fa-folder'
                me.showOptions = false
                me.confirmDownload = false
                me.confirmRemove = false
                me.deleting = false
                window.location.reload()
            }).catch(error => {
                me.mainIconClass = 'fa-folder'
                me.showOptions = false
                me.confirmDownload = false
                me.confirmRemove = false
                me.deleting = false
                toastr.error('An error occured. Impossible to remove ' + me.folder.name)
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

.blink-me {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
}

</style>