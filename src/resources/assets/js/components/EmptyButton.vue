<template>
    <button 
        :style="backgroundStyle"
        v-on:click="empty"
        v-on:mouseenter="backgroundStyle.backgroundColor = '#bbb'; iconStyle.opacity = 1"
        v-on:mouseleave="backgroundStyle.backgroundColor = '#bdc3c7'; iconStyle.opacity = 0.6"
    >
        <i class="fas fa-trash" :class="emptyBlinkClass" :style="iconStyle"></i>
    </button>
</template>

<script>
export default {
    data() {
        return {
            backgroundStyle: {
                backgroundColor: '#bdc3c7',
                color: '#5bc85c',
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
                opacity: 0.6,
            },
            emptyBlinkClass: '',
        }
    },
    methods: {
        empty() {
            if (this.emptyBlinkClass == '') {
                this.$emit('empty')
            }
        },
        startBlink() {
            this.emptyBlinkClass = 'blink-me'
        },
        stopBlink() {
            this.emptyBlinkClass = ''
        },
    }
}
</script>

<style scoped>

.blink-me {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
}

</style>