<template>
    <div>
        <button
            type="button"
            class="btn btn-primary"
            v-on:click="restartServer"
        >
            RESTART THE {{ diodein ? 'CLIENT' : 'SERVER' }}
        </button>
        <br/>
        <p>
            {{ diodein ? 'CLIENT' : 'SERVER' }} {{ state }}
        </p>
    </div>
</template>

<script type="application/javascript"> 
export default {
    props: {
        diodein: Boolean,
        serverState: String,
    },
    data() {
        return {
            state: '',
        }
    },
    mounted() {
        var me = this
        this.state = me.serverState
    },
    methods: {
        restartServer() {
            var me = this
            var button = $('.btn')
            if(!button.hasClass('disabled')) {
                button.addClass('disabled').html('RESTARTING... <span class="fa fa-spinner fa-pulse"></span>')
            }
            const url = me.diodein ? '/ftpclient' : '/ftpserver'
            const options = {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
            }
            axios(options)
                .then(function(response) {
                    button.removeClass('disabled').html('RESTART THE ' + (me.diodein ? 'CLIENT' : 'SERVER'))
                    me.state = response.serverState
                })
                .catch(function(error) {
                    toastr.error(error)
                })
        },
    }
}
</script>

<style scoped>
    div {
        width: 100%;
        text-align: center;
    }
    p, .btn {
        width: 25%;
    }    
</style>