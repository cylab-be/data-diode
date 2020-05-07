<template>
    <div>
        <span v-if="diodein">
            <span v-show="!isAptModule">
                <div class="text" :style="{float:'left', marginLeft: '2em', width: '12em'}">
                    Choose a <input class="port-input" v-model="aptport" placeholder="port"> and click to add a new APT module
                </div>
                <add-button ref="addButton" v-on:add="addApt"></add-button>
            </span>
            <span v-show="isAptModule">
                <div :style="{marginBottom: '0.5em'}">
                    <div class="text" :style="{float:'left', marginLeft: '2em', width: '12em'}">
                        Module running on port {{ aptport }}. Click to remove
                    </div>
                    <del-button ref="delButton" v-on:del="removeApt"></del-button>
                    <hr class="window-title-bottom-bar"/>
                    <div class="text" :style="{float:'center', width: '19em'}">
                        <input 
                            class="mirror-input"
                            v-model="mirrorUrl"
                            :disabled="downloading"
                            placeholder="mirror url"
                        >
                    </div>
                    <add-button :style="{marginTop: '0.5em'}" :disabled="downloading" ref="addMirror" v-on:add="downloadMirror"></add-button>
                </div>
                <div
                    v-show="false" 
                    :style="{
                        width: '100%',
                        overflow:'scroll',
                        height:'12em',
                    }"
                >
                    <div  
                        :style="{
                            width: '95%',
                            whiteSpace:'pre-wrap',                
                        }"
                        v-for="(item, index) in downloadedData" :key="index"
                    >
                        <p>{{ item }}</p>
                    </div>
                </div>
            </span>            
        </span>
        <span v-else>
            <span v-if="aptport == ''" class="text">
                There is no APT module on this channel.
            </span>
            <span v-else class="text" >
                <div 
                    :style="{
                        width: '90%',
                        textAlign: 'left',
                        margin: 'auto',
                    }"
                >
                    <b>Add a repository:</b> {{ scrollValue }}/{{ maxScrollValue }}
                    <button :disabled="scrollValue == 1" v-on:click="scrollValue = scrollValue - 1 <= 1 ? 1 : scrollValue - 1"><i class="fas fa-arrow-left"></i></button>
                    <button :disabled="scrollValue == maxScrollValue" v-on:click="scrollValue = scrollValue + 1 >= maxScrollValue ? maxScrollValue : scrollValue + 1"><i class="fas fa-arrow-right"></i></button>
                    <br/>
                    <br/>
                    <ul
                        :style="{
                            margin: 0,
                            padding: 0,
                            listStyle: 'none',
                        }"
                    >
                        <li
                            v-if="scrollValue == 1"
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            Write the 
                            <input placeholder="repository url" v-model="mirrorUrl" :style="{width: '100%'}">
                            (without the <i>http://</i> prefix)
                        </li>
                        <li
                            v-if="scrollValue == 2"
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            Add the
                            <input placeholder="release options" v-model="mirrorOptions" :style="{width: '100%'}">
                            separated by spaces (ex: <i>stable non-free</i> or <i>bionic universe</i>...)
                        </li>
                        <li
                            v-if="scrollValue == 3 && !copyError"
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            <button v-on:click="copyMe">CLICK</button> to copy the deb
                        </li>
                        <li
                            v-if="scrollValue == 3 && copyError"
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            Copy <input :value="command + ' ' + mirrorUrl + ' ' + mirrorOptions">
                        </li>
                        <li
                            v-if="scrollValue == 4"
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            Append the copied repository url to the file located at:<br/><br/>
                            <i>/etc/apt/sources.list</i>
                        </li>
                        <li
                            v-if="scrollValue == 5"
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            If a key is needed, open a terminal at your key location and use:<br/><br/>
                            <i>sudo apt-key add [your_key_name]</i>
                        </li>
                        <li
                            v-if="scrollValue == 6"
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            Update the sources list:<br/><br/>
                            <i>sudo apt update</i>
                        </li>
                    </ul>
                </div>                
            </span>
        </span>
    </div>
</template>

<script>
export default {
    props: {
        item: Object,
        diodein: Boolean,
    },
    data() {
        return {
            packageName: '',
            downloadedData: [],
            cannotDownload: false,
            aptport: '',
            isAptModule: false,
            mirrorUrl: '',
            mirrorOptions: '',
            downloading: false,
            command: '',
            copyError: false,
            scrollValue: 1,
            maxScrollValue: 6,
        }
    },
    mounted() {
        if (this.item.aptport != 0 && this.item.aptport != undefined) {
            this.aptport = this.item.aptport
            this.isAptModule = true
            var ip = '192.168.102.1'
            var command = 'deb [trusted=yes] '
            command += 'http://' + ip + ':'
            command += this.item.aptport
            this.command = command
        }
    },
    methods: {
        addApt() {
            var me = this
            if (isNaN(me.aptport)) {
                toastr.error('The apt port must be a number.')
                return
            }
            this.$refs.addButton.startBlink()
            const port = parseInt(me.aptport)
            const url = 'apt/' + me.item.id
            const options = {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
                data: {
                    name: me.item.name,
                    port: port
                },
            }
            axios(options)
            .then(function(response) {
                me.isAptModule = true
                toastr.success(response.data.message)
                me.$refs.addButton.stopBlink()
            })
            .catch(function(error) {
                if (error.response.status != 422) {
                    toastr.error(error.response.data.message)
                }
                if (error.response.data.errors.name) {
                    toastr.error(error.response.data.errors.name)
                }
                if (error.response.data.errors.port) {
                    toastr.error(error.response.data.errors.port)
                }
                me.$refs.addButton.stopBlink()
            })
        },
        removeApt() {
            var me = this
            const url = 'apt/' + me.item.id
            const options = {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
                data: {
                    name: me.item.name,
                },
            }
            this.$refs.delButton.startSpin()
            axios(options)
            .then(function(response) {
                me.aptport = ''
                me.isAptModule = false
                toastr.success(response.data.message)
                me.$refs.delButton.stopSpin()
            })
            .catch(function(error) {
                if (error.response.status != 422) {
                    toastr.error(error.response.data.message)
                }
                if (error.response.data.errors.name) {
                    toastr.error(error.response.data.errors.name)
                }
                me.$refs.delButton.stopSpin()
            })
        },
        downloadMirror() {
            var me = this
            this.item.state = '1'
            this.downloading = true
            this.$refs.addMirror.startBlink()
            const url = 'apt/mirror/' + this.item.id
            const options = {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
                data: {
                    url: me.mirrorUrl,
                },
            }
            axios(options)
            .then(function(response) {                    
                me.$refs.addMirror.stopBlink()
                me.downloading = false
                me.mirrorUrl = ''
                toastr.success('Mirror successfully downloaded.')
            })
            .catch(function(error) {
                me.$refs.addMirror.stopBlink()
                me.downloading = false
                if (error.response.status != 422) {
                    toastr.error(error.response.data.message)
                }
                if (error.response.data.errors.url) {
                    toastr.error(error.response.data.errors.url)
                }
            })
        },
        copyMe() {
            //this.$refs.copyText.select()
            //this.$refs.copyText.setSelectionRange(0, 99999)
            //document.execCommand('copy')
            var me = this
            navigator.clipboard.writeText(this.command + '/' +this.mirrorUrl + ' ' + this.mirrorOptions).then(() => {
                toastr.success('Successfully copied: ' + me.command + '/' +me.mirrorUrl + ' ' + me.mirrorOptions)
            }).catch(error => {
                me.copyError = true
                toastr.error('Impossible to copy to clipboard. Select and copy the text instead.')
            })
        },        
    },
}
</script>

<style scoped>

.port-input {
    padding-left: 0.1em;
    padding-right: 0.1em;
    width: 4.5em;
    height: 2em;
}

.mirror-input {
    padding-left: 0.1em;
    padding-right: 0.1em;
    width: 18em;
    height: 2em;
    text-align: center;
}

.text {
    width: 16em;
    font-size: 1.33em;
    margin: auto;
    margin-bottom: 0.5em;
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

.blink-me {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
}

</style>