<template>    
    <div class="main">
        <div class="query-main">
            <input class="query" v-model="query">
            <i class="fas fa-search query-icon"></i>
            <button v-on:click="goToForwardConfig" :style="{float:'right'}">
                <i class="fas fa-cog"></i>
            </button>
        </div>
        <br/>
        <div v-if="diodein" class="add-uploader-main">
            <input class="add-uploader-name-input" v-model="name" placeholder='name'>
            <input class="add-uploader-port-input" v-model="port" placeholder='port'>
            <button class="add-uploader-button" :disabled="addDisabled" v-on:click="addUploader">
                <i class="fas fa-plus add-uploader-button-icon" :class="blinkClass"></i>
            </button>
        </div>
        <i v-if="loadingUploaders" class="fas fa-sync fa-spin"></i>
        <transition-group
            v-else
            class="items"            
            name="staggered-fade"
            tag="div"            
            v-on:before-enter="beforeEnter"
            v-on:enter="enter"
            v-on:leave="leave"
        >
            <uploader
                v-for="(item, index) in computedList"
                v-bind:key="item.name"
                v-bind:data-index="index"
                :item="item"
                v-on:del="del"
                :max-upload-size="maxUploadSize"
                :max-upload-size-error-message="maxUploadSizeErrorMessage"
                :diodein="diodein"
                :ip-addr="ipAddr"
            >
            </uploader>
        </transition-group>

        <!--template id='growing-button'>
            <div>
                Le parent...
                <slot></slot>
                ... englobe l'enfant
            </div>
        </template-->
    </div>
</template>

<script>
export default {
    props: {
        ipAddr: String,
        interval: Number,
        diodein: Boolean,
        maxUploadSize: Number,
        maxUploadSizeErrorMessage: String,
    },
    data() {
        return {            
            uploaders: [],
            query: '',
            name: '',
            port: '',
            canUpdate: true,
            addDisabled: false,
            loadingUploaders: true,
            blinkClass: '',
        }
    },
    computed: {
        computedList: function () {
            var me = this
            const res = this.uploaders.filter(function (item) {
                return item.name.toLowerCase().indexOf(me.query.trim().toLowerCase()) !== -1
            })
            return res.sort((a, b) => {
                if(a.name < b.name) { return -1; }
                if(a.name > b.name) { return 1; }
                return 0;
            })
        },
    },
    mounted() {
        var me = this

        // channel values
        const url = 'uploader'
        const options = {
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url,
        }
        axios(options)
        .then(function(response) {
            me.uploaders = response.data.uploaders
            me.loadingUploaders = false
            me.uploaders.map((uploader, i) => {
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
                const url = 'uploader'
                const options = {
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    url,
                }
                axios(options)
                .then(function(response) {
                    me.uploaders = response.data.uploaders
                    me.uploaders.map((uploader, i) => {
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
            }
        }, me.interval)

    },
    methods: {
        del(item) {
            this.uploaders = this.uploaders.filter( uploader => {
                return uploader.name != item.name
            })
        },
        beforeEnter: function (el) {
            el.style.opacity = 0
            el.style.height = 0
            el.style.width = 0
        },
        enter: function (el, done) {
            var delay = el.dataset.index * 100
            setTimeout(function () {
                
                var pos = 0
                var id = setInterval(frame, 5)
                function frame() {
                    if (pos == 100) {
                        el.style.opacity = 1
                        el.style.height = '3.6em'
                        el.style.width = '24em'
                        el.style.borderWidth = '0.1em'
                        el.style.borderRadius = '0.7em'
                        el.style.fontSize = '1em'
                        el.complete = done
                        clearInterval(id)
                    } else {
                        pos += 2
                        el.style.opacity = pos / 100.0
                        el.style.height = (3.6 * pos / 100.0) + 'em'
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
                    }
                }
                
            }, delay)
        },
        addUploader() {
            var me = this            
            if (isNaN(me.port)) {
                toastr.error('The port must be a number.')
                return
            }            
            me.addDisabled = true
            const url = 'uploader'
            const options = {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url,
                data: {
                    name: me.name,
                    port: parseInt(me.port)
                },
            }
            this.blinkClass = 'blink-me'
            axios(options)
            .then(function(response) {
                const name = me.name
                const port = me.port
                toastr.success('Successfully added ' + name + '\'s channel at port ' + port + '!')
                me.uploaders.push({
                    name: name,
                    port: port,
                    state: '0',
                    status: 'running',
                })
                me.name = ''
                me.port = ''
                me.addDisabled = false
                me.blinkClass = ''
            })
            .catch(function(error) {
                if (error.response.status != 422) {
                    toastr.error(error.response.data.message)
                }
                if (error.response.data.errors.name) {
                    toastr.error(error.response.data.errors.name)
                }
                if (error.response.data.errors.port) {
                    toastr.error(error.response.data.errors.port)
                }
                me.addDisabled = false
                me.blinkClass = ''
            })
        },
        goToForwardConfig() {
            window.location.href = '/config'
        }
    },
}
</script>

<style scoped>

.main {
    width: 24em;
    text-align: center;
    margin: auto;
    margin-bottom: 4em;
}

.query {
    width: 10em;
    height: 3em;
    padding: 0.5em;
    margin-left: 3em;
    margin-right: 0;
    margin-top: 0;
    margin-bottom: 0;
}

.query-main {
    width: 100%;
    text-align: center;
}

.query-icon {
    width: 3em;
    font-size: 1em;
    height: 3em;
    padding: 0;
    margin: 0;
}

.add-uploader-main {
    background-color: #007bff00;
    border: 0.1em dashed #aaa;
    border-radius: 0.7em;  
    font-size: 1em;
    text-align: center;
    margin-left: auto;
    margin-right: auto;
    height: 3.6em;
    width: 24em;
    margin-bottom: 1em;
}

.add-uploader-name-input {
    float: left;
    margin-left: 2em;
    padding-left: 0.5em;
    padding-right: 0.5em;
    margin-top: 0.7em;
    width: 8em;
    height: 2em;
}

.add-uploader-port-input {
    float: left;
    margin-left: 1.5em;
    padding-left: 0.5em;
    padding-right: 0.5em;
    margin-top: 0.7em;
    width: 5.5em;
    height: 2em;
}

.add-uploader-button {
    margin-top: 0.7em;
    margin-right: 2em;
    height: 2em;
    float: right;
    border: none;
    background-color: #007bff;
    border-radius: 0.3em;
}

.add-uploader-button-icon {
    margin: auto;
    color: #ddd;
}

.items {
    width: 100%;
    text-align: center;
    margin: auto;
}

.blink-me {
  animation: blinker 1s linear infinite;
}

@keyframes blinker {
  50% {
    opacity: 0;
  }
}

</style>