<template>
    <button 
        :style="backgroundStyle"
        v-on:click="del"
        v-on:mouseenter="backgroundStyle.backgroundColor = '#bbb'; iconStyle.opacity = 1"
        v-on:mouseleave="backgroundStyle.backgroundColor = '#bdc3c7'; iconStyle.opacity = 0.6"
    >
        <i class="fas fa-times" :class="delSpinClass" :style="iconStyle"></i>
    </button>
</template>

<script>
export default {
    data() {
        return {
            backgroundStyle: {
                backgroundColor: '#bdc3c7',
                color: '#dc3545',
                borderRadius: '1em',
                border: 'none',
                width: '4em',
                height: '4em',
                position: 'relative',
                overflow: 'hidden',
            },
            iconStyle: {
                margin: 'auto',
                position: 'absolute',
                marginTop: '-0.5em',
                marginLeft: '-1.5em',
                fontSize: '3em',
                width: '3em',
                height: '3em',
                transformOrigin: '1.5em 0.5em',
            },
            delSpinClass: '',
        }
    },
    methods: {
        del() {
            if (this.delSpinClass == '') {
                this.$emit('del')
            }
        },
        startSpin() {
            this.delSpinClass = 'fa-spin'
        },
        stopSpin() {
            this.delSpinClass = ''
        },
    },
}
</script>

<style scoped>

</style>