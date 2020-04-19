<template>
    <div class="main" :hidden="!windowVisible">
        <div class="veil-main"></div>
        <div class="window-main">
            <div class="row">
                <span class="window-title" :class="blinkClass">
                    <b>{{ item.name }}</b>
                    &nbsp;
                    {{ 
                        item.status == 'running' ? 
                            'is running on port ' + item.port 
                        : 
                            'is stopped [port ' + item.port + ']' 
                    }}
                </span>
                <button class="window-close-button" v-on:click="close" :disabled="closeDisabled">
                    <i class="fas fa-times window-close-button-icon" :class="closeDisabled ? 'fa-spin' : ''"></i>
                </button>
            </div>
            <hr class="window-title-bottom-bar"/>
            <span v-show="param == 'config'">
                <div class="row" :style="{paddingBottom:'0.5em'}">
                    <div class="text" :style="{float:'left', marginLeft: '2em', width: '12em'}">
                        {{
                            'Click to toggle the FTP channel status'
                        }}
                    </div>
                    <toggler :style="{float:'right', marginRight:'2em'}" v-on:toggled="toggle" ref="toggler"></toggler>
                </div>
                <hr class="window-title-bottom-bar"/>
                <div class="row" :style="{paddingBottom:'0.5em'}">
                    <div class="text" :style="{float:'left', marginLeft: '2em', width: '12em'}">
                        {{
                            'Click to empty - delete the FTP channel'
                        }}
                    </div>
                    <div :style="{float:'right', marginRight:'2em'}">
                        <empty-button v-on:empty="empty" ref="emptyButton"></empty-button>
                        <del-button v-on:del="del" ref="delButton"></del-button>
                    </div>
                </div>
                <hr class="window-title-bottom-bar"/>
            </span>
            <span v-show="param == 'ftp'">
                <upload :item="item"></upload>
                <hr class="window-title-bottom-bar"/>
            </span>
            <div class="row">
                <button v-on:click="param = 'config'">CONFIG</button>
                <button v-on:click="param = 'ftp'">FTP</button>
            </div>
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
            windowVisible: false,
            blinkClass: '',
            closeDisabled: false,
            param: 'config',
        }
    },
    mounted() {
    },
    methods: {
        open() {            
            this.windowVisible = true
            this.$refs.toggler.setStatus(this.item.status == 'running' ? 'ON' : 'OFF')
        },
        close() {
            return new Promise((resolve, reject) => {
                this.windowVisible = false
                if (this.windowVisible) {
                    reject()
                } else {
                    resolve()
                }
            })
        },
        toggle(text) {
            if (text == 'ON') {
                this.restart()
            } else if (text == 'OFF') {
                this.stop()
            }
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
        stop() {
            var me = this
            this.blinkClass = 'blink-me'
            this.closeDisabled = true
            this.act('stop', this.item.name).then(() => {
                me.$emit('stop')
                me.blinkClass = ''
                toastr.success('Successfully stopped ' + me.item.name + '\'s channel!')
                me.closeDisabled = false
            }).catch(error => {
                me.$refs.toggler.move(false)
                me.blinkClass = ''
                me.closeDisabled = false
            })
        },
        restart() {
            var me = this
            this.blinkClass = 'blink-me'
            this.closeDisabled = true
            this.act('restart', this.item.name).then(() => {
                me.$emit('restart')
                me.windowStatus = 'is running on port ' + me.item.port
                me.blinkClass = ''                
                toastr.success('Successfully restarted ' + me.item.name + '\'s channel!')
                me.closeDisabled = false
            }).catch(error => {                
                me.$refs.toggler.move(false)
                me.blinkClass = ''
                me.closeDisabled = false
            })
        },
        empty() {
            var me = this
            this.closeDisabled = true
            this.$refs.emptyButton.startBlink()
            this.act('empty', this.item.name).then(() => {                
                this.$refs.emptyButton.stopBlink()
                toastr.success('Successfully emptied ' + me.item.name + '\'s channel!')
                me.closeDisabled = false
            }).catch(error => {
                this.$refs.emptyButton.stopBlink()
                me.closeDisabled = false
            })
        },
        /*emptyAndRestart(uploader) {
            var me = this
            me.act('empty', uploader.name).then(() => {
                me.act('restart', uploader.name).then(() => {
                    toastr.success('Successfully emptied and restarted ' + uploader.name + '\'s channel!')
                })
            })
        },*/
        del() {
            var me = this
            this.closeDisabled = true
            this.$refs.delButton.startSpin()
            this.act('del', this.item.name).then(() => {
                me.close().then(() => {
                    me.$emit('del')
                })  //  Promise is necessary otherwise this ParamWindow's ref
                    //  could be lost when the associated Uploader is deleted,
                    //  making the complete closing of the ParamWindow 
                    //  impossible.
                this.$refs.delButton.stopSpin()
                toastr.success('Successfully deleted ' + this.item.name + '\'s channel!')
                me.closeDisabled = false
            }).catch(error => {
                this.$refs.delButton.stopSpin()
                me.closeDisabled = false
            })
        },
    }
}
</script>

<style scoped>

.main {
    /* position: absolute avoids the
       "stacking contexts" effect between 
       this  component's z-index and the
       transform css prop used by the 
       fa-spin classes of the Uploaders
       components */
    position: absolute; 
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
    width: 93%;
}

.text {
    font-size: 1.33em;
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