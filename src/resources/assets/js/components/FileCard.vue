<template>
    <div 
        class="card text-center"
        :style="{cursor: 'pointer'}"
        :title="dirPath + '/' + file.name"
        v-on:mouseover="mouseOver"
        v-on:mouseout="mouseOut"
        v-on:click="click"
    >
        <i 
            class="fa fa-file fa-4x text-muted"
            :style="iconStyle"
        ></i>
        <div 
            class="card-body"
            :style="{
                padding: '2em',
            }"
        >
            <p  
                class="card-text text-muted"
                :style="paragraphStyle"
            >                                
                {{ file.name }}
            </p>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        file: Object,
        dirPath: String,
    },
    data() {
        return {
            iconStyle: {
                color:'darkgray',
            },
            paragraphStyle: {
                whiteSpace: 'nowrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
                display: 'inline-block',
                maxWidth: '100%',
                color: 'darkgray',
            },
        }
    },
    methods: {
        mouseOver() {
            this.iconStyle.color = 'lightblue'
            this.paragraphStyle.color = 'lightblue'
            this.$emit('change-path', this.dirPath + '/' + this.file.name, true)
        },
        mouseOut() {
            this.iconStyle.color = 'darkgray'
            this.paragraphStyle.color = 'darkgray'
            this.$emit('change-path', this.dirPath, false)
        },
        click() {
            var me = this
            const path = this.dirPath + '/' + this.file.name
            axios({
                url: '/download', //your url
                method: 'POST',
                responseType: 'blob', // important
                data: {
                    path: path,
                    type: 'file',
                }
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]))
                const link = document.createElement('a')
                link.href = url
                link.setAttribute('download', me.file.name) //or any other extension
                document.body.appendChild(link)
                link.click()
            }).catch(error => {
                toastr.error('An error occured. Impossible to download ' + me.file.name)
            })
        },
    }
}
</script>

<style scoped>

</style>