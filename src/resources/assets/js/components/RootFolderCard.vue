<template>
    <div 
        class="card"
        :style="{
            cursor: 'pointer',
            position: 'relative',
        }"
        v-on:mouseover="mouseOver"
        v-on:mouseout="mouseOut"
        v-on:click="click"
    >
        <i 
            class="fa fa-folder fa-4x"
            :class="textPrimaryClass"
        ></i>        
        <div 
            class="card-body" 
            :style="{
                padding: '2em',
            }"
        >
            <p 
                class="card-text"
                :class="textPrimaryClass"
                :style="{
                    whiteSpace: 'nowrap',
                    overflow: 'hidden',
                    textOverflow: 'ellipsis',
                    display: 'inline-block',
                    maxWidth: '100%',
                }"
            >
                ..
            </p>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        quickNavigation: Array,
        dirPath: String,
    },
    data() {
        return {
            textPrimaryClass: '',
        }
    },
    methods: {        
        mouseOver() {
            this.textPrimaryClass = 'text-primary'
            const n = this.quickNavigation.length
            const path = (n <= 1) ? '.' : this.quickNavigation[n - 1].path
            this.$emit('change-path', path, false)
        },
        mouseOut() {
            this.textPrimaryClass = ''
            this.$emit('change-path', this.dirPath, false)
        },
        click() {
            this.textPrimaryClass = ''
            const n = this.quickNavigation.length
            window.location.href = "/storage" + (n <= 1 ? '' : this.quickNavigation[n - 1].path)
        },
    }
}
</script>

<style scoped>

</style>