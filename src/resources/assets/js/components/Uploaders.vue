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
            <div class="col-6 col-sm-1">
                <button v-on:click="del(uploader)" >
                    DELETE
                </button>
            </div>
        </div>
        <br />
        <div
            class="row"
            style="width:100%"
        >
            Add here a new FTP uploader<input type="text" /><button v-on:click="addUploader">ADD</button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        items: Array,
        statuses: Array,
        interval: Number,
    },
    data() {
        return {
            uploaders: [],
            canUpdate: true,
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
                const url = '/usageUpdate'
                const options = {
                    method: 'POST',
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
                const url = '/usage' + action[0].toUpperCase() + action.slice(1); 
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

        },
        addUploader() {

        },
    },
}
</script>

<style scoped>

</style>