<template>
    <div>
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
    </div>
</template>

<script>
export default {
    props: {
        item: Object,
    },
    data() {
        return {
            packageName: '',
            downloadedData: [],
            cannotDownload: false,
            pipport: '',
            isPipModule: false,
        }
    },
    mounted() {
        var me = this
        const url = '/getPipPort'
        const options = {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url,
            data: {
                uploader: me.item.name,
            },
        }
        axios(options)
        .then(function(response) {
            me.isPipModule = response.data.pipport != 0
            if (response.data.pipport != 0) {
                me.pipport = response.data.pipport
            }
        })
        .catch(function(error) {
            toastr.error('Unable to get the ' + me.item.name + '\'s channel pip port module')
        })
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
            const url = '/addPip'
            const options = {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
                data: {
                    uploader: me.item.name,
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
            const url = '/removePip'
            const options = {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
                data: {
                    uploader: me.item.name,
                },
            }
            this.$refs.delButton.startSpin()
            axios(options)
            .then(function(response) {
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
                        axios.post('/pythonpip',
                        {
                            name: name,
                            uploader: me.item.name
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
                    toastr.error(error.response.data.message)
                    me.$refs.addPackage.stopBlink()
                    me.packageName = ''
                })
            } else {
                toastr.error('You must specify at least one package.')
            }
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