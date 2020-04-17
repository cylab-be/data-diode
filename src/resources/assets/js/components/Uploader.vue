<template>
    <div
        :style="{
            margin: 0,
            padding: 0,
        }"
    >
        <button
            :style="uploaderStyle"
            v-on:mouseenter="mouseEnter"
            v-on:mouseleave="mouseLeave"
            v-on:click="showParamWindow"
        >   
            <i v-if="item.state == '1'" class="fas fa-sync fa-spin item-busy-state-icon"></i>
            <i v-else class="fas fa-circle item-free-state-icon"></i>
            <b class="item-name">{{ item.name }}</b>
            &nbsp;
            <span class="item-port">[port {{ item.port }}]</span>
        </button>
    </div>
</template>

<script>
import EventBus from './eventbus'

export default {
    props: {
        item: Object,
    },
    data() {
        return {
            uploaderStyle: {
                backgroundColor: '#007bff00',
                border: '0.1em dashed #aaa',
                borderRadius: '0.7em',
                fontSize: '1em',
                textAlign: 'center',
                marginLeft: 'auto',
                marginRight: 'auto',
                height: '3.6em',
                width: '24em',
                color: 'inherit',
            },
        }
    },
    mounted() {        
        if (this.item.status) {
            if (this.item.status == 'running') {
                this.uploaderStyle.color = '#28a745'
            } else if (this.item.status == 'stopped') {
                this.uploaderStyle.color = '#dc3545'
            } else {
                this.uploaderStyle.color = 'inherit'    
            }
        } else {
            this.uploaderStyle.color = 'inherit'
        }
    },
    methods: {
        updateMe(isRunning) {
            this.uploaderStyle.color = isRunning ? '#28a745' : '#dc3545'
            this.item.status = isRunning ? 'running' : 'stopped'
        },
        mouseEnter() {
            var me = this            
            this.uploaderStyle.backgroundColor = '#007bff40'
        },
        mouseLeave() {
            this.uploaderStyle.backgroundColor = '#007bff00'
        },
        showParamWindow() {
            var me = this
            // Here and not in mounted so that when a new one
            // is added, it gets an id            
            EventBus.$on('update-status-' + me.item.id, (isRunning) => {
                console.log(isRunning)
                me.updateMe(isRunning)
            })
            EventBus.$emit('paramwindowupdate', this.item, this.uploaderStyle)
        },
    }
}
</script>

<style scoped>

.item-busy-state-icon {
    margin: auto;
    float: left;
    margin-top: 0.3em;
    margin-left: 0.5em;
    width: 1em;
}

.item-free-state-icon {
    margin: auto;
    margin-left: 0.5em;
    margin-top: 0.3em;
    width: 1em;
    float: left;
}

.item-name {
    float: left;
    margin-left: 0.5em;
}

.item-port {
    float: right;
    margin-right: 2em;
}

</style>