<template>
    <button
        :style="moveBackgroundStyle"
        v-on:click="move(true)"
        v-on:mouseenter="moveBackgroundStyle.backgroundColor = '#ddd'; moveStyle.opacity = 1"
        v-on:mouseleave="moveBackgroundStyle.backgroundColor = '#eee'; moveStyle.opacity = 0.6"
    >
        <div
            :style="moveStyle"
        >
        </div>
    </button>
</template>

<script>
export default {
    props: {
        status: String
    },
    data() {
        return {            
            moveBackgroundStyle: {
                width: '8em',
                height: '4em',
                borderRadius: '2em',
                backgroundColor: '#eee',
                padding: 0,
                //margin: 'auto',
                //overflow: 'hidden',
                border: 'none',
                boxShadow: 'inset 0 0 4em rgba(100,100,100,0.6)',
                position: 'relative',
            },
            moveStyle: {
                width: '4em',
                height: '4em',
                margin: 0,
                backgroundColor: '#dc3545',
                borderRadius: '50%',
                marginLeft: '0em',
                opacity: 0.6,
                position: 'absolute',
                marginTop: '-2em',
            },
            moveAvailable: true,
            moveBegin: true,
        }
    },
    mounted() {
    },
    methods: {
        setStatus(status) {
            if (status == 'ON') {
                this.moveBegin = false
                this.moveStyle.backgroundColor = '#5bc85c'
                this.moveStyle.marginLeft = '4em'
            } else if (status == 'OFF') {
                this.moveBegin = true
                this.moveStyle.backgroundColor = '#dc3545'
                this.moveStyle.marginLeft = '0em'
            }
        },
        move(emit) {
            /**
             * emit: if true, the move emits a message back to the parent,
             *       otherwise not.
             */
            var me = this
            if (me.moveAvailable) {
                me.moveAvailable = false
                if (me.moveBegin) {
                    var pos = 0
                    var id = setInterval(frame, 20)
                    function frame() {
                        if (pos == 40) {
                            clearInterval(id)
                            me.moveStyle.marginLeft = '4em'
                            me.moveStyle.backgroundColor = '#5bc85c'
                            me.moveBegin = false
                            me.moveAvailable = true
                            if (emit) {
                                me.$emit('toggled', 'ON')
                            }
                        } else {
                            const abs = Math.abs(pos - 40 / 2)
                            const delta = abs > 15 ? 1 : abs > 10 ? 2 : 4
                            pos += delta
                            me.moveStyle.marginLeft = (pos / 10.0) + 'em'
                            const r = 220 + pos * (92 - 220) / 40
                            const g = 53 + pos * (184 - 53) / 40
                            const b = 69 + pos * (92 - 69) / 40
                            me.moveStyle.backgroundColor = 'rgba(' + r + ',' + g + ',' + b + ',1)'
                        }
                    }
                } else {
                    var pos = 40
                    var id = setInterval(frame, 20)
                    function frame() {
                        if (pos == 0) {
                            clearInterval(id)
                            me.moveStyle.marginLeft = '0em'
                            me.moveStyle.backgroundColor = '#dc3545'
                            me.moveBegin = true
                            me.moveAvailable = true
                            if (emit) {
                                me.$emit('toggled', 'OFF')
                            }
                        } else {
                            const abs = Math.abs(pos - 40 / 2)
                            const delta = abs > 15 ? 1 : abs > 10 ? 2 : 4
                            pos -= delta
                            me.moveStyle.marginLeft = (pos / 10.0) + 'em'
                            const r = 220 + pos * (92 - 220) / 40
                            const g = 53 + pos * (184 - 53) / 40
                            const b = 69 + pos * (92 - 69) / 40
                            me.moveStyle.backgroundColor = 'rgba(' + r + ',' + g + ',' + b + ',1)'
                        }
                    }
                }
            }
        },
    }
}
</script>

<style scoped>

</style>