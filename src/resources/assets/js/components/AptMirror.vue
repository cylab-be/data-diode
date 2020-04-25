<template>
    <div>
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
                <input v-model="mirrorUrl">
                <button v-on:click="downloadMirror" placeholder="mirror url">
                    <i class="fas" :class="aptIconClass"></i>
                </button>                
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
            aptport: '',
            isAptModule: false,
            mirrorUrl: '',
            aptIconClass: 'fa-plus',
        }
    },
    mounted() {
        var me = this
        const url = '/getAptPort'
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
            me.isAptModule = response.data.aptport != 0
            if (me.aptport != 0) {
                me.aptport = response.data.aptport
            }
        })
        .catch(function(error) {
            toastr.error('Unable to get the ' + me.item.name + '\'s channel apt port module')
        })
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
            const url = '/addApt'
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
                me.isAptModule = true
                toastr.success(response.data.message)
                me.$refs.addButton.stopBlink()
            })
            .catch(function(error) {
                toastr.error(error.response.data.message)
                me.$refs.addButton.stopBlink()
            })
        },
        removeApt() {
            var me = this
            const url = '/removeApt'
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
                me.isAptModule = false
                toastr.success(response.data.message)
                me.$refs.delButton.stopSpin()
            })
            .catch(function(error) {
                toastr.error(error.response.data.message)
                me.$refs.delButton.stopSpin()
            })
        },
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