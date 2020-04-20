<template>
    <div>
        <span v-show="!isPipModule">
            <p>[add a new module below]</p>
            <input class="port-input" v-model="pipport" placeholder="port">
            <button class="add-package-button" v-on:click="addPip">
                <i class="fas fa-plus add-package-button-icon"></i>
            </button>
        </span>
        <span v-show="isPipModule">
            <div :style="{marginBottom: '0.5em'}">
                <p>[module running on port {{ pipport }}]</p>
                <input 
                    v-model="packageName"
                    placeholder="package name"
                >
                <button
                    type="button"
                    class="add-package-button"                 
                    v-on:click="downloadPackage"
                    :disabled="cannotDownload"
                >
                    <i class="fas fa-plus add-package-button-icon"></i>
                </button>
            </div>
            <div 
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
            me.pipport = response.data.pipport
        })
        .catch(function(error) {
            toastr.error('Unable to get the ' + me.item.name + '\'s channel pip port module')
        })
    },
    methods: {
        addPip() {
            var me = this
            const url = '/addPip'
            const port = parseInt(me.pipport)
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
            })
            .catch(function(error) {
                toastr.error(error.response.data.message)
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
                this.packageName = ''
                send(name).then(response => {
                    me.downloadedData.push(response.data.output)
                    if (response.data.output.startsWith('Failed')) {
                        toastr.error('The download of the ' + name + ' package failed.')
                    } else {
                        toastr.success('The ' + name + ' package has been successfully downloaded and added to the ' + me.item.name + '\'s channel.')
                        me.item.state = '1'
                    }
                    me.cannotDownload = false
                }).catch(error => {
                    toastr.error(error.response.data.message)
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
    height: 2em;
    border: none;
    background-color: #007bff;
    border-radius: 0.3em;
}

.add-package-button-icon {
    margin: auto;
    color: #ddd;
}

.port-input {
    padding-left: 0.5em;
    padding-right: 0.5em;
    width: 5.5em;
    height: 2em;
}

</style>