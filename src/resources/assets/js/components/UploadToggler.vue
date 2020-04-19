<template>
    <div>
        <button 
            :style="{
                margin: 0,
                border: 'none',
                padding: 0,
                width: '8em',
                height: '4em',
                backgroundColor: '#007bff',
                color: '#bbb',
                fontSize: '1.33em',
                position: 'relative',
                opacity: 0.8,                
            }"
            v-on:click="move"
        >            
            <i 
                class="fa fa-file"
                :style="{
                    position: 'absolute',
                    width: '2em',
                    height: '2em',
                    fontSize: '2em',
                    marginTop: '-0.5em',
                    marginLeft: '-2em',
                }"
            ></i>
            <i 
                class="fa fa-folder"
                :style="{
                    position: 'absolute',
                    width: '2em',
                    height: '2em',
                    fontSize: '2em',
                    marginTop: '-0.5em',
                    marginLeft: '0em',
                }"
            ></i>
            <div :style="moveStyleLeft"></div>
            <div :style="moveStyleRight"></div>
        </button>
    </div>
</template>

<script>
export default {
   data() {
       return {
            moveStyleLeft: {
                position: 'absolute',
                top: 0,
                left: 0,
                padding: 0,
                margin: 0,
                opacity: 0.6,
                width: 0,
                height: '4em',
                backgroundColor: '#bbb',
            },
            moveStyleRight: {
                position: 'absolute',
                top: 0,
                left: 0,
                padding: 0,
                margin: 0,
                opacity: 0.6,
                width: '4em',
                marginLeft: '4em',
                height: '4em',
                backgroundColor: '#bbb',
            },
            moveBegin: true,

       }
   },
   methods: {
       move() {
            var me = this
            if (this.moveBegin) {
                var pos = 0
                var id = setInterval(frame, 20)
                function frame() {
                    if (pos == 40) {
                        clearInterval(id)
                        me.moveStyleLeft.width = '4em'
                        me.moveStyleRight.marginLeft = '8em'
                        me.moveStyleRight.width = 0
                        me.moveBegin = false
                        me.$emit('toggled', 'ON')
                    } else {
                        const abs = Math.abs(pos - 40 / 2)
                        const delta = abs > 15 ? 1 : abs > 10 ? 2 : 4
                        pos += delta
                        me.moveStyleLeft.width = (pos / 10.0) + 'em'
                        me.moveStyleRight.marginLeft = (4 + (pos / 10.0)) + 'em'
                        me.moveStyleRight.width = (4 - (pos / 10.0)) + 'em'
                    }
                }            
            } else {
                var pos = 40
                var id = setInterval(frame, 20)
                function frame() {
                    if (pos == 0) {
                        clearInterval(id)
                        me.moveStyleLeft.width = 0
                        me.moveStyleRight.marginLeft = '4em'
                        me.moveStyleRight.width = '4em'
                        me.moveBegin = true
                        me.$emit('toggled')
                    } else {
                        const abs = Math.abs(pos - 40 / 2)
                        const delta = abs > 15 ? 1 : abs > 10 ? 2 : 4
                        pos -= delta
                        me.moveStyleLeft.width = (pos / 10.0) + 'em'
                        me.moveStyleRight.marginLeft = (4 + (pos / 10.0)) + 'em'
                        me.moveStyleRight.width = (4 - (pos / 10.0)) + 'em'
                    }
                }  
            }
       }
   }

}
</script>

<style scoped>

</style>