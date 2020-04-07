<template>    
    <div>
        <div
            v-for="uploader in uploaders"
            :key="uploader.name"
            class="row"
            style="width:50%"
        >
            <div class="col-6 col-sm-3">
                {{ uploader.name }}
            </div>
            <div class="col-6 col-sm-3">
                {{ parseInt(uploader.state) === 0 ? 'free' : 'busy' }}
            </div>
            <button>
                DISABLE
            </button>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        items: Array,
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
                })
                .catch(function(error) {
                    toastr.error('Error. Please refresh the page.')
                    me.canUpdate = false
                })
            }
        }, me.interval)
    },
}
</script>

<style scoped>

</style>