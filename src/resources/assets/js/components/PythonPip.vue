<template>
    <div>
        <span v-if="diodein">
            <span v-show="!isPipModule">
                <div class="text" :style="{float:'left', marginLeft: '2em', width: '12em'}">
                    Choose a <input class="port-input" v-model="pipport" placeholder="port"> and click to add a new PIP module
                </div>
                <add-button ref="addButton" v-on:add="addPip"></add-button>
            </span>
            <span v-show="isPipModule">
                <div :style="{marginBottom: '0.5em'}">
                    <div class="text" :style="{float:'left', marginLeft: '2em', width: '12em'}">
                        Module running on port {{ pipport }}. Click to remove
                    </div>
                    <del-button ref="delButton" v-on:del="removePip"></del-button>
                    <hr class="window-title-bottom-bar"/>
                    <div class="text" :style="{float:'left', marginLeft: '2em', width: '12em'}">
                        Add a <input :disabled="cannotDownload" class="package-input" v-model="packageName" placeholder="package name"> and click to download
                    </div>
                    <add-button :style="{marginTop: '2em'}" :disabled="cannotDownload" ref="addPackage" v-on:add="downloadPackage"></add-button>
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
            <span v-if="pipport == ''" class="text">
                There is no PIP module on this channel.
            </span>
            <span v-else class="text" >
                <div 
                    :style="{
                        width: '90%',
                        textAlign: 'left',
                        margin: 'auto',
                    }"
                >
                    <b>Install a package:</b><br/>
                    <ul
                        :style="{
                            margin: 0,
                            padding: 0,
                            listStyle: 'none',
                        }"
                    >
                        <li
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            <b>1.</b> Write the <input placeholder="package name" v-model="packageName" :style="{width: '10em'}">
                        </li>
                        <li
                            v-if="!copyError"
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            <b>2.</b> <button v-on:click="copyMe">CLICK</button> to copy the pip install command 
                        </li>
                        <li
                            v-else
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            <b>2.</b> Copy <input :value="command + packageName">
                        </li>                        
                        <li
                            :style="{
                                margin: 0,
                                padding: 0,
                                float: 'left',
                            }"
                        >
                            <b>3.</b> Paste and launch the copied command in a terminal
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
            pipport: '',
            isPipModule: false,
            command: '',
            copyError: false,
        }
    },
    mounted() {
        if (this.item.pipport != 0 && this.item.pipport != undefined) {
            this.pipport = this.item.pipport
            this.isPipModule = true
            var ip = '192.168.102.1'
            var command = 'sudo -H python3 -m pip install --trusted-host'
            command += ' ' + ip + ' '
            command += '-i http://' + ip + ':'
            command += this.item.pipport + '/simple '
            this.command = command
        }
    },
    methods: {
        addPip() {
            var me = this
            if (isNaN(me.pipport)) {
                toastr.error('The pip port must be a number.')
                return
            }
            this.$refs.addButton.startBlink()
            const port = parseInt(me.pipport)
            const url = 'pip/' + me.item.id
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
                me.isPipModule = true
                toastr.success(response.data.message)
                me.$refs.addButton.stopBlink()
            })
            .catch(function(error) {
                toastr.error(error.response.data.message)
                me.$refs.addButton.stopBlink()
            })
        },
        removePip() {
            var me = this
            const url = 'pip/' + me.item.id
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
                me.pipport = ''
                me.isPipModule = false
                toastr.success(response.data.message)
                me.$refs.delButton.stopSpin()
            })
            .catch(function(error) {
                toastr.error(error.response.data.message)
                me.$refs.delButton.stopSpin()
            })
        },
        downloadPackage() {
            if (this.packageName.length > 0) {
                this.item.state = '1'
                this.downloadedData = []
                this.cannotDownload = true
                var me = this            
                function send(name) {
                    return new Promise((resolve, reject) => {
                        axios.post('pip/package/' + me.item.id,
                        {
                            package: me.packageName,
                        })
                        .then(function(response){                            
                            resolve(response)
                        })
                        .catch(function(error){
                            reject(error)
                        })
                    })
                }
                this.downloadedData.push('Downloading ' + this.packageName + '...')
                const name = this.packageName                
                this.$refs.addPackage.startBlink()
                send(name).then(response => {
                    me.downloadedData.push(response.data.output)
                    if (response.data.output.startsWith('Failed')) {
                        toastr.error('The download of the ' + name + ' package failed.')                        
                    } else {
                        toastr.success('The ' + name + ' package has been successfully downloaded and added to the ' + me.item.name + '\'s channel.')
                        
                        me.item.state = '1'
                    }
                    me.cannotDownload = false
                    me.$refs.addPackage.stopBlink()
                    me.packageName = ''
                }).catch(error => {
                    me.cannotDownload = false
                    toastr.error(error.response.data.message)
                    me.$refs.addPackage.stopBlink()
                    me.packageName = ''
                })
            } else {
                toastr.error('You must specify at least one package.')
            }
        },
        copyMe() {
            //this.$refs.copyText.select()
            //this.$refs.copyText.setSelectionRange(0, 99999)
            //document.execCommand('copy')
            var me = this
            navigator.clipboard.writeText(this.command + this.packageName).then(() => {
                toastr.success('Successfully copied: ' + me.command + me.packageName)
            }).catch(error => {
                me.copyError = true
                toastr.error('Impossible to copy to clipboard. Select and copy the text instead.')
            })
        },
    },
}
</script>

<style scoped>

.add-package-button {
    height: 4em;
    width: 4em;
    border: none;
    background-color: #007bff;
    border-radius: 0.3em;
}

.remove-package-button {
    height: 4em;
    width: 4em;
    border: none;
    background-color: #dc3545;
    border-radius: 0.3em;
}

.add-package-button-icon {
    margin: auto;
    color: #ddd;
    font-size: 3em;
}

.port-input {
    padding-left: 0.1em;
    padding-right: 0.1em;
    width: 4.5em;
    height: 2em;
}

.package-input {
    padding-left: 0.1em;
    padding-right: 0.1em;
    width: 11em;
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

</style>