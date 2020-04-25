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
                        Click to toggle the FTP channel status
                    </div>
                    <toggler :style="{float:'right', marginRight:'2em'}" v-on:toggled="toggle" ref="toggler"></toggler>
                </div>
                <hr class="window-title-bottom-bar"/>
                <div class="row" :style="{paddingBottom:'0.5em'}">
                    <div class="text" :style="{float:'left', marginLeft: '2em', width: '12em'}">
                            {{
                                diodein ?
                                    'Click to empty - delete the FTP channel'
                                :
                                    'Click to empty the FTP channel'
                            }}
                    </div>
                    <div :style="{float:'right', marginRight:'2em'}">
                        <empty-button v-on:empty="empty" ref="emptyButton"></empty-button>
                        <del-button v-if="diodein" v-on:del="del" ref="delButton"></del-button>
                    </div>
                </div>
            </span>
            <span v-if="diodein" v-show="param == 'ftp'">
                <upload 
                    :item="item"
                    :max-upload-size="maxUploadSize"
                    :max-upload-size-error-message="maxUploadSizeErrorMessage"
                ></upload>                
            </span>
            <span v-if="diodein" v-show="param == 'pip'">
                <python-pip :item="item"></python-pip>
            </span>
            <span v-if="diodein" v-show="param == 'apt'">
                <input v-model="mirrorUrl">
                <button v-on:click="downloadMirror"><i class="fas" :class="aptIconClass"></i></button>
            </span>
            <div  v-if="diodein" v-show="diodein" class="row" :style="{position: 'absolute', bottom: '1em', width: '100%', margin: 'auto'}">
                <hr class="window-title-bottom-bar"/>
                <button class="param-button" v-on:click="param = 'config'">CONFIG</button><!-- This comment 
                avoids spaces --><button class="param-button" v-on:click="param = 'ftp'">FTP</button><!-- This comment 
                avoids spaces --><button class="param-button" v-on:click="param = 'pip'">PIP</button><!-- This comment 
                avoids spaces --><button class="param-button" v-on:click="param = 'apt'">APT</button>
            </div>
            <span v-if="!diodein">
                <hr class="window-title-bottom-bar"/>
                <button 
                    class="button"
                    :style="{verticalAlign: 'middle'}"
                    v-on:click="toStorage"
                ><span>STORAGE </span></button>
            </span>
        </div>
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
            windowVisible: false,
            blinkClass: '',
            closeDisabled: false,
            param: 'config',
            // apt
            mirrorUrl: '',
            aptIconClass: 'fa-plus',
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
                if (!this.closeDisabled) {
                    this.windowVisible = false
                    if (this.windowVisible) {
                        reject()
                    } else {
                        resolve()
                    }
                }
                resolve()
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
                me.$refs.delButton.stopSpin()
                toastr.success('Successfully deleted ' + this.item.name + '\'s channel!')
                me.closeDisabled = false
                me.close().then(() => {
                    me.$emit('del')
                })  //  Promise is necessary otherwise this ParamWindow's ref
                    //  could be lost when the associated Uploader is deleted,
                    //  making the complete closing of the ParamWindow 
                    //  impossible.                
            }).catch(error => {
                me.$refs.delButton.stopSpin()
                me.closeDisabled = false
            })
        },
        toStorage() {
            window.location.href = '/storage/' + this.item.name
        },
        // apt
        downloadMirror() {
            var me = this
            this.item.state = '1'
            this.aptIconClass = 'fa-arrow-down blink-me'
            const url = '/addMirror'
            const options = {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
                data: {
                    uploader: me.item.name,
                    url: me.mirrorUrl,
                },
            }
            axios(options)
            .then(function(response) {                    
                me.aptIconClass = 'fa-plus'
                toastr.success('Mirror successfully downloaded.')
            })
            .catch(function(error) {
                me.aptIconClass = 'fa-plus'
                toastr.error(error.response.data.message)
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

.param-button {
    width: 24%;
    height: 4em;
}

.blink-me {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
}

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