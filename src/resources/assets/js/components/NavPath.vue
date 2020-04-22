<template>
    <div>
        <span
            v-for="(part, index) in computedPath"
            :key="index"
            class="clickable"
            :style="selected ? index <= clickIndex ? {color: '#007bff'} : {color: '#aaa'} : {color: 'inherit'}"
            v-on:mouseover="setClickIndex(index)"
            v-on:mouseout="setClickIndex(-1)"
            v-on:click="clickMe"
        ><span
            v-if="index == computedPath.length - 1 && pathContainsFile"
        >{{ part }}</span><span
            v-else
        >{{ part + '/' }}</span></span>
    </div>
</template>

<script>
export default {
    props: {
        path: String,
        pathContainsFile: Boolean
    },
    data() {
        return {
            clickIndex: 0,
            selected: false
        }
    },
    computed: {
        computedPath: function () {
            return this.path.split('/')
        },
    },
    methods: {
        setClickIndex(index) {
            this.clickIndex = index
            this.selected = index != -1
        },
        clickMe() {
            if (this.path != '.') {
                window.location.href = '/storage' + this.path.split('/').slice(0, this.clickIndex + 1).join('/')
            } else {
                window.location.href = '/storage'
            }
        }
    }
}
</script>

<style scoped>

.clickable {
    cursor: pointer;
}

</style>