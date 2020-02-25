<template>
    <div>
        <button
            type="button"
            class="btn btn-primary"
            v-on:click="restartServer"
        >
            RESTART THE FTP {{ diodein ? 'CLIENT' : 'SERVER' }}
        </button>
        <br/>
    </div>
</template>

<script type="application/javascript"> 
export default {
    props: {
        diodein: Boolean,
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