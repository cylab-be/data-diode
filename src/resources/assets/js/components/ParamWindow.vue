<template>
    <div class="main" :hidden="!windowVisible">
        <div class="veil-main"></div>
        <div class="window-main">
            <div class=row>
                <span class="window-title" :class="blinkClass">
                    <b>{{ windowName }}</b> {{ windowStatus }}
                </span>
                <button class="window-close-button" v-on:click="windowVisible = false" :disabled="closeDisabled">
                    <i class="fas fa-times window-close-button-icon" :class="closeDisabled ? 'fa-spin' : ''"></i>
                </button>
            </div>
            <hr class="window-title-bottom-bar"/>
            <toggler v-on:toggled="toggle" ref="toggler"></toggler>
            <button
                :style="{
                    margin: 'auto',
                    backgroundColor: '#bdc3c7',
                    color: '#5bc85c',
                    borderRadius: '1em',
                    border: 'none',
                    width: '4em',
                    height: '4em',
                }"
                v-on:click="empty(item)"
            >
                <i 
                    class="fas fa-2x fa-trash"
                    :class="emptyBlinkClass"
                    :style="{
                        margin: 'auto',
                    }"        
                ></i>
            </button>
            <button
                :style="{
                    margin: 'auto',
                    backgroundColor: '#bdc3c7',
                    color: '#dc3545',
                    borderRadius: '1em',
                    border: 'none',
                    width: '4em',
                    height: '4em',
                }"
                v-on:click="del(item)"
            >
                <i 
                    class="fas fa-2x fa-times"
                    :class="delBlinkClass"
                    :style="{
                        margin: 'auto',
                    }"
                ></i>
            </button>
        </div>
    </div>
</template>

<script>
import EventBus from './eventbus'

export default {
    data() {
        return {
            windowVisible: false,
            windowName: '',
            windowStatus: '',
            item: {},
            uploaderStyle: {},
            blinkClass: '',
            emptyBlinkClass: '',
            delBlinkClass: '',
            closeDisabled: false,
        }
    },
    mounted() {
        EventBus.$on('paramwindowupdate', (item, uploaderStyle) => {
            this.updateMe(item, uploaderStyle)
        })
    },
    methods: {
        toggle(text) {
            if (text == 'ON') {
                this.restart(this.item)
            } else if (text == 'OFF') {
                this.stop(this.item)
            }
        },
        updateMe(item, uploaderStyle) {
            var me = this
            me.$refs.toggler.setStatus(item.status == 'running' ? 'ON' : 'OFF')
            me.windowVisible = true
            const runStr = 'is running on port ' + item.port
            const stopStr = 'is stopped [port ' + item.port + ']'
            me.windowName = item.name
            me.windowStatus = item.status == 'running' ? runStr : stopStr
            me.item = item
            me.uploaderStyle = uploaderStyle
        },
        act(action, name) {
            return new Promise((resolve, reject) => {
                const url = '/channel' + action[0].toUpperCase() + action.slice(1);
                const options = {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    url,
                    data: {
                        uploader: name,
                    },
                }
                axios(options)
                .then(function(response) {                    
                    return resolve()
                })
                .catch(function(error) {
                    toastr.error(error.response.data.message)
                    return reject()
                })
            })
        },
        stop(uploader) {
            var me = this
            me.blinkClass = 'blink-me'
            me.closeDisabled = true
            this.act('stop', uploader.name).then(() => {
                me.windowStatus = 'is stopped [port ' + me.item.port + ']'
                me.blinkClass = ''                
                EventBus.$emit('update-status-' + me.item.id, false)
                toastr.success('Successfully stopped ' + uploader.name + '\'s channel!')
                me.closeDisabled = false
            }).catch(error => {                
                me.$refs.toggler.move(false)
                me.blinkClass = ''
                me.closeDisabled = false
            })
        },
        restart(uploader) {
            var me = this
            me.blinkClass = 'blink-me'
            me.closeDisabled = true
            this.act('restart', uploader.name).then(() => {
                me.windowStatus = 'is running on port ' + me.item.port
                me.blinkClass = ''
                EventBus.$emit('update-status-' + me.item.id, true)
                toastr.success('Successfully restarted ' + uploader.name + '\'s channel!')
                me.closeDisabled = false
            }).catch(error => {                
                me.$refs.toggler.move(false)
                me.blinkClass = ''
                me.closeDisabled = false
            })
        },
        empty(uploader) {
            var me = this
            me.closeDisabled = true
            me.emptyBlinkClass = 'blink-me'
            this.act('empty', uploader.name).then(() => {
                toastr.success('Successfully emptied ' + uploader.name + '\'s channel!')
                me.emptyBlinkClass = ''
                me.closeDisabled = false
            }).catch(error => {
                me.emptyBlinkClass = ''
                me.closeDisabled = false
            })
        },
        emptyAndRestart(uploader) {
            var me = this
            me.act('empty', uploader.name).then(() => {
                me.act('restart', uploader.name).then(() => {
                    toastr.success('Successfully emptied and restarted ' + uploader.name + '\'s channel!')
                })
            })
        },
        del(uploader) {
            var me = this
            me.closeDisabled = true
            me.delBlinkClass = 'fa-spin'
            this.act('del', uploader.name).then(() => {
                me.delBlinkClass = ''
                me.windowVisible = false
                toastr.success('Successfully deleted ' + uploader.name + '\'s channel!')
                EventBus.$emit('remove-uploader', uploader)
                me.closeDisabled = false
            }).catch(error => {
                me.delBlinkClass = ''
                me.closeDisabled = false
            })
        },
    }
}
</script>

<style scoped>

.main {
    padding: 0;
    margin: 0;
}

.veil-main {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #666;
    opacity: 0.7;
    z-index: 998;
}

.window-main {
    position: fixed;
    top: calc(50% - 15em);
    left: calc(50% - 15em);
    z-index: 999;
    width: 30em;
    height: 30em;
    background-color: #f5f8fa;
    border: 0.1em dashed #aaa;
    border-radius: 0.7em;
}

.window-title {
    margin-left: 2em;
    margin-top: 0.8em;
    float: left;
    font-size: 1.33em;
}

.window-close-button {
    margin-top: 1.0em;
    margin-right: 2em;
    height: 2em;
    float: right;
    border: none;
    background-color: #dc3545;
    border-radius: 0.3em;
}

.window-close-button-icon {
    margin: auto;
    color: #ddd;
}

.window-title-bottom-bar {
    margin-left: auto;
    margin-right: auto;
    border: 0.16em dashed #aaa;
    border-bottom-width: 0;
    border-left-width: 0;
    border-right-width: 0;
    padding: 0;
    height: 0;
    width: 28em;
}

.blink-me {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
}

</style>