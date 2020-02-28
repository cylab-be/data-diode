<template>
    <div>
        <div style="text-align:center;">
            <button 
                type="button" 
                v-on:click="toggle"
                :style="buttonStyle"
                v-on:mouseover="hover"
                v-on:mouseleave="unhover"
            >
                <i v-show="isFolder" class="fa fa-file fa-lg"></i>
                <i v-show="!isFolder" class="fa fa-folder fa-lg"></i>
                &nbsp;
                I WANT TO UPLOAD {{ !isFolder ? ' A FOLDER' : 'FILES' }}
                &nbsp;
                <i v-show="isFolder" class="fa fa-file fa-lg"></i>
                <i v-show="!isFolder" class="fa fa-folder fa-lg"></i>
                
            </button>
        </div>
        <span v-show="isFolder">
            <upload-form :upload_url="upload_url" folder_only></upload-form>
        </span>
        <span v-show="!isFolder">
            <upload-form :upload_url="upload_url"></upload-form>
        </span>        
    </div>
</template>

<script>
export default {
    props: {
        upload_url: String,
    },
    data() {
        return {
            isFolder: false,
            buttonStyle: {
                margin: 'auto',
                marginBottom: '2em',
                backgroundColor: '#007bff',
                height: '4em',
                borderRadius: '1em',
                border: 'none',
                color: '#eee',
                padding: '1em',
            }
        }
    },
    methods: {
        toggle() {
            var me = this
            me.isFolder = !me.isFolder
        },
        hover() {
            var me = this
            me.buttonStyle.backgroundColor = '#0050d4'
            me.buttonStyle.color = '#fff'
        },
        unhover () {
            var me = this
            me.buttonStyle.backgroundColor = '#007bff'
            me.buttonStyle.color = '#eee'            
        }
    },
}
</script>

<style scoped>

</style>