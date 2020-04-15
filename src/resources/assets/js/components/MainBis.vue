<template>    
    <div
        :style="{
            width: '24em',
            textAlign: 'center',
            margin: 'auto',
            marginBottom: '4em',
        }"
    >
        <div :style="{width:'100%', textAlign: 'center'}">
            <input                 
                v-model="query"
                :style="{
                    width:'10em',
                    height:'3em',
                    padding: '0.5em',
                    marginLeft:'3em',
                    marginRight:0,
                    marginTop:0,
                    marginBottom:0,
                }"
            >
            <i 
                :style="{
                    width:'3em',
                    fontSize: '1em',
                    height:'3em',
                    padding: 0,
                    margin: 0,
                }"
                class="fas fa-search"
            ></i>
        </div>
        <br />
        <div
            :style="{
                backgroundColor: '#007bff00',
                border: '0.1em dashed #aaa',
                borderRadius: '0.7em',        
                fontSize: '1em',
                textAlign: 'center',
                marginLeft: 'auto',
                marginRight: 'auto',
                height: '3.6em',
                width: '24em',
                marginBottom: '1em',
            }"
        >
            <input
                v-model="name"
                :style="{                    
                    float:'left',
                    marginLeft: '2em',
                    paddingLeft: '0.5em',
                    paddingRight: '0.5em',
                    marginTop: '0.7em',
                    width: '8em', 
                    height: '2em',                   
                }"
                placeholder='name'
            >
            <input
            v-model="port"
                :style="{
                    float:'left',
                    marginLeft: '2em',
                    paddingLeft: '0.5em',
                    paddingRight: '0.5em',
                    marginTop: '0.7em',
                    width: '5em',
                    height: '2em',
                }"
                placeholder='port'
            >
            <button
            :disabled="addDisabled"
                :style="{
                    marginTop: '0.7em',
                    marginRight: '2em',
                    height: '2em',
                    float:'right',
                    border: 'none',
                    backgroundColor: '#007bff',
                    borderRadius: '0.3em',
                }"
                v-on:click="addUploader"
            >
                <i 
                    class="fas fa-plus"
                    :style="{
                        margin: 'auto',
                        color: '#ddd',
                    }"
                ></i>
            </button>
        </div>
        <transition-group
            name="staggered-fade"
            tag="div"
            style="width:100%;text-align:center;margin:auto;"
            v-on:before-enter="beforeEnter"
            v-on:enter="enter"
            v-on:leave="leave"
        >
            <button
                v-for="(item, index) in computedList"
                v-bind:key="item.name"
                v-bind:data-index="index"
                :style="uploaderStyles[index]"
                @mouseenter="mouseEnter(index)"
                @mouseleave="mouseLeave(index)"
            >   
                <i  
                    v-if="item.state == '1'"
                    class="fas fa-sync fa-spin"
                    :style="{
                        margin: 'auto',
                        float:'left',
                        marginTop: '0.3em',
                        marginLeft: '0.5em',
                        width: '1em',
                    }"
                ></i>
                <i
                    v-else
                    class="fas fa-circle"
                    :style="{
                        margin: 'auto',
                        marginLeft: '0.5em',
                        marginTop: '0.3em',
                        width: '1em',
                        float:'left',
                    }"
                ></i>                
                <b 
                    :style="{
                        float:'left',
                        marginLeft: '0.5em'
                    }"
                >
                    {{ item.name }}
                </b>                
                <span 
                    :style="{
                        float:'right',
                        marginRight: '2em'
                    }"
                >
                    [port {{ item.port }}]
                </span>
            </button>
        </transition-group>
    </div>
</template>

<script>
export default {
    props: {
        //items: Array,
        //statuses: Array,
        interval: Number,
        //diodein: Boolean,
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
            },
            uploaderStyles: [],
            uploaders: [],
            query: '',
            name: '',
            port: '',
            canUpdate: true,
            addDisabled: false,
            angle: 0,
        }
    },
    computed: {
        computedList: function () {
            var me = this
            const res = this.uploaders.filter(function (item) {
                return item.name.toLowerCase().indexOf(me.query.trim().toLowerCase()) !== -1
            })
            return res
        },
    },
    mounted() {
        var me = this

        // channel values
        const url = '/channelUpdate'
        const options = {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url,
        }
        axios(options)
        .then(function(response) {
            me.uploaders = response.data.uploaders
            me.uploaders.map((uploader, i) => {
                // Object.assign will make a copy and avoid all styles 
                // to work on the same reference
                me.uploaderStyles.push(Object.assign({}, me.uploaderStyle))
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

        setInterval(() => {
            if (me.canUpdate) {
                const url = '/channelUpdate'
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
                        me.getStatusColor(i)
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
        mouseEnter: function(index) {
            var me = this
            me.uploaderStyles[index].backgroundColor = '#007bff40'
        },
        mouseLeave: function(index) {
            var me = this
            me.uploaderStyles[index].backgroundColor = '#007bff00'
        },
        beforeEnter: function (el) {
            el.style.opacity = 0
            el.style.height = 0
            el.style.width = 0
            /*el.style.paddingTop = 0
            el.style.paddingBottom = 0
            el.style.marginTop = 0
            el.style.marginBottom = 0*/
        },
        enter: function (el, done) {
            var delay = el.dataset.index * 100
            setTimeout(function () {
                
                var pos = 70
                var id = setInterval(frame, 5)
                function frame() {
                    if (pos == 100) {
                        el.style.opacity = 1
                        el.style.height = '3.6em'
                        el.style.width = '24em'
                        el.style.borderWidth = '0.1em'
                        el.style.borderRadius = '0.7em'
                        el.style.fontSize = '1em'
                        /*el.style.paddingTop = '1em'
                        el.style.paddingBottom = '1em'
                        el.style.marginTop = '0.2em'
                        el.style.marginBottom = '0.2em'*/
                        el.complete = done
                        clearInterval(id)
                    } else {
                        pos += 2
                        el.style.opacity = pos / 100.0
                        el.style.height = (3.6 * pos / 100.0) + 'em'
                        el.style.width = (24 * pos / 100.0) + 'em'
                        /*el.style.paddingTop = (pos / 100) + 'em'
                        el.style.paddingBottom = (pos / 100) + 'em'
                        el.style.marginTop = (0.2 * pos / 100)  + 'em'
                        el.style.marginBottom = (0.2 * pos / 100) + 'em'*/
                    }
                }
                
            }, delay)
        },
        leave: function (el, done) {
            var delay = el.dataset.index * 100
            setTimeout(function () {
                
                var pos = 100
                var id = setInterval(frame, 5)
                function frame() {
                    if (pos == 0) {
                        el.style.opacity = 0
                        el.style.height = 0
                        el.style.width = 0
                        el.style.borderWidth = 0
                        el.style.borderRadius = 0
                        el.style.fontSize = 0
                        el.style.margin = 0
                        el.style.padding = 0
                        el.complete = done
                        clearInterval(id)
                    } else {
                        pos -= 2
                        el.style.opacity = pos / 100.0
                        el.style.height = (3.6 * pos / 100.0) + 'em'
                        el.style.width = (24 * pos / 100.0) + 'em'
                        /*el.style.marginTop = (0.5 * 3.6 * (100 - pos) / 100) + 'em'
                        el.style.marginBottom = (0.5 * 3.6 * (100 - pos) / 100) + 'em'
                        el.style.fontSize = 0.5 * pos / 100.0 + 'em'
                        el.style.padding = el.style.padding / 1.5
                        el.style.borderWidth = el.style.borderWidth / 1.5
                        el.style.borderRadius = el.style.borderRadius / 1.5*/
                    }
                }
                
            }, delay)
        },
        addUploader() {
            var me = this
            me.addDisabled = true
            const url = '/channelAdd'
            const options = {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
                data: {
                    uploader: me.name,
                    port: parseInt(me.port)
                },
            }
            axios(options)
            .then(function(response) {
                const name = me.name
                const port = me.port
                toastr.success('Successfully added ' + name + '\'s channel at port ' + port + '!')
                me.uploaderStyles.push(Object.assign({}, me.uploaderStyle))
                me.name = ''
                me.port = ''
                me.addDisabled = false
            })
            .catch(function(error) {
                toastr.error(error.response.data.message)
                me.addDisabled = false
            })
        },
        getStatusColor(index) {
            var me = this
            var uploader = me.uploaders[index]
            if (uploader.status) {
                if (uploader.status == 'running') {
                    me.uploaderStyles[index].color = '#28a745'
                } else if (uploader.status == 'stopped') {
                    me.uploaderStyles[index].color = '#dc3545'
                } else {
                    me.uploaderStyles[index].color = 'inherit'    
                }
            } else {
                me.uploaderStyles[index].color = 'inherit'
            }            
        },
    },
}
</script>

<style scoped>

</style>