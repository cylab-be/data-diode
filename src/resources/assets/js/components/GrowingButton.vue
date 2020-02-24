<template>
    <div :style="root">
        <div :style="square">
            <div 
                :style="content" 
                v-on:mouseover="grow"
                v-on:mouseleave="shrink"
                v-on:click="redirectRoute"
            >
            </div>
            <div :style="iconContainer">
                <i 
                    :class="'fa ' + icon"
                    :style="iconStyle"
                ></i>
            </div>
        </div>        
        <slot></slot>
    </div>
</template>

<script>
export default {
    props: {
        route: String,
        icon: String,
    },
    data() {
        return {
            incInterval: null,
            root: {
                // backgroundColor: 'red',                
                textAlign: 'center',
                width: '10em',
            },
            square: {
                // backgroundColor: 'black',
                width: '10em',
                paddingBottom: '10em',
                textAlign: 'center',
                position: 'relative',
            },
            content: {
                top: '1em',
                left: '1em', // 12%
                position: 'absolute',
                backgroundColor: '#007bff',
                width: '8em', // 25%
                height: '8em',
                borderRadius: '50%',
                color: 'white',
                userSelect: 'none',
            },
            iconContainer: {
                position: 'absolute',
                // backgroundColor: 'green',
                width: '6em',
                height: '6em',
                top: '2em',
                left: '2em', // 12%
                textAlign: 'center',
                pointerEvents: 'none', // cannot be seen by the mouse
              
            },
            iconStyle: {                  
                color: '#eeeeee',
                pointerEvents: 'none', // cannot be seen by the mouse
                fontSize: '4em',
                margin: 'auto',
                marginTop: '0.25em',
           }
        }
    },
    mounted() {
    
    },
    methods: {
        redirectRoute() {
            console.log(this.route);
            window.location.href = this.route;
        },
        grow() {
            var me = this;
            me.content.backgroundColor = '#0050d4'
            me.content.color = '#ffffff'
            if (me.incInterval !== null) {
                clearInterval(me.incInterval)
            }
            me.incInterval= setInterval(function(){                
                var size = parseFloat(me.content.width)
                if (size + 0.2 <= 9) {
                    size += 0.2
                    if (10 - size > 0 && 9 - size < 0.2) {
                        size = 9
                    }                    
                    me.content.width = size + 'em'
                    me.content.height = size + 'em'
                    me.content.top = (5 - size / 2) + 'em'
                    me.content.left = (5 - size / 2) + 'em'
                    me.iconContainer.width = 0.75 * size + 'em'
                    me.iconContainer.height = 0.75 * size + 'em'
                    me.iconContainer.top = (5 - 0.75 * size / 2) + 'em'
                    me.iconContainer.left = (5 - 0.75 * size / 2) + 'em'
                    me.iconStyle.fontSize = 0.5 * size + 'em'
                } else {
                    clearInterval(me.incInterval)
                }
            }, 12);
        },
        shrink() {
            var me = this;
            me.content.backgroundColor = '#007bff'
            me.content.color = '#eeeeee'
            if (me.incInterval !== null) {
                clearInterval(me.incInterval)
            }
            me.incInterval= setInterval(function(){                
                var size = parseFloat(me.content.width)
                if (size - 0.4 >= 8) {
                    size -= 0.4
                    if (size - 8 > 0 && size - 8 < 0.4) {
                        size = 8
                    }
                    me.content.width = size + 'em'
                    me.content.height = size + 'em'
                    me.content.top = (5 - size / 2) + 'em'
                    me.content.left = (5 - size / 2) + 'em'
                    me.iconContainer.width = 0.75 * size + 'em'
                    me.iconContainer.height = 0.75 * size + 'em'
                    me.iconContainer.top = (5 - 0.75 * size / 2) + 'em'
                    me.iconContainer.left = (5 - 0.75 * size / 2) + 'em'
                    me.iconStyle.fontSize = 0.5 * size + 'em'
                } else {
                    clearInterval(me.incInterval)
                }
            }, 12);
        }
    }
}
</script>

<style scoped>
    
</style>