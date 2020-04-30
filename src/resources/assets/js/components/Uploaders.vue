<template>    
    <div>
        <div
            v-for="uploader in uploaders"
            :key="uploader.name"
            class="row"
            style="width:100%"
        >
            <div class="col-6 col-sm-1">
                {{ uploader.name }}
            </div>
            <div class="col-6 col-sm-1">
                {{ parseInt(uploader.state) === 0 ? 'free' : 'busy' }}
            </div>
            <div class="col-6 col-sm-1">
                {{ uploader.status }}
            </div>
            <div class="col-6 col-sm-1">
                {{ uploader.port }}
            </div>
            <div class="col-6 col-sm-1">
                <button v-on:click="stop(uploader)" >
                    STOP
                </button>
            </div>
            <div class="col-6 col-sm-1">
                <button v-on:click="restart(uploader)" >
                    RESTART
                </button>
            </div>
            <div class="col-6 col-sm-1">
                <button v-on:click="empty(uploader)" >
                    EMPTY
                </button>
            </div>
            <div class="col-6 col-sm-2">
                <button v-on:click="emptyAndRestart(uploader)" >
                    EMPTY &amp; RESTART
                </button>
            </div>
            <div 
                class="col-6 col-sm-1"
                :hidden="!diodein"
            >
                <button v-on:click="del(uploader)" >
                    DELETE
                </button>
            </div>
        </div>
        <br />
        <div
            class="row"
            style="width:100%"
            :hidden="!diodein"
        >
            Add here a new FTP uploader
            <input 
                type="text" 
                v-model="uploaderToAdd"
                placeholder="name"
            />
            <input 
                type="text" 
                v-model="portToAdd"
                placeholder="port"
            />
            <button
                v-on:click="addUploader"
                :disabled="addDisabled"
            >
                ADD
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        items: Array,
        statuses: Array,
        interval: Number,
        diodein: Boolean,
    },
    data() {
        return {
            uploaders: [],
            canUpdate: true,
            uploaderToAdd: '',
            portToAdd: '',
        }
    },
    mounted() {
        var me = this
        me.uploaders = me.items
        me.uploaders.map((uploader, i) => {
            uploader.status = me.statuses[i]
            return {
                uploader
            }
        })
        setInterval(() => {
            if (me.canUpdate) {
                const url = 'uploader'
                const options = {
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    url,
                }
                axios(options)
                .then(function(response) {
                    me.uploaders = response.data.uploaders
                    me.uploaders.map((uploader, i) => {
                        uploader.status = response.data.statuses[i]
                        return {
                            uploader
                        }
                    })
                })
                .catch(function(error) {
                    toastr.error('Error. Please refresh the page.')
                    me.canUpdate = false
                })
            }
        }, me.interval)
    },
    methods: {
        act(action, name) {
            return new Promise((resolve, reject) => {
                const url = '/channel' + action[0].toUpperCase() + action.slice(1); 
                const options = {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    url,
                    data: {
                        name: name,
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
            this.act('stop', uploader.name).then(() => {
                toastr.success('Successfully stopped ' + uploader.name + '\'s channel!')
            })
        },
        restart(uploader) {
            this.act('restart', uploader.name).then(() => {
                toastr.success('Successfully restared ' + uploader.name + '\'s channel!')
            })
        },
        empty(uploader) {
            this.act('empty', uploader.name).then(() => {
                toastr.success('Successfully emptied ' + uploader.name + '\'s channel!')
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
            this.act('del', uploader.name).then(() => {
                toastr.success('Successfully deleted ' + uploader.name + '\'s channel!')
            })
        },
        addUploader() {
            var me = this            
            const url = '/channelAdd'
            const options = {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
                data: {
                    name: me.uploaderToAdd,
                    port: parseInt(me.portToAdd)
                },
            }
            axios(options)
            .then(function(response) {                    
                const name = me.uploaderToAdd
                const port = me.portToAdd
                toastr.success('Successfully added ' + name + '\'s channel at port ' + port + '!')
                me.uploaderToAdd = ''
                me.portToAdd = ''
            })
            .catch(function(error) {
                toastr.error(error.response.data.message)                
            })
        },
    },
    computed: {
        addDisabled() {
            return this.uploaderToAdd.length == 0 || this.portToAdd.length == 0
        },
    }
}
</script>

<style scoped>

</style>