<template>
    <div>
        <h4 v-if="cannotBeSeen" class="text-danger">
            This folder may contain symlinks or other unsupported types. Its content cannot be accessed.
        </h4>
        <h4 v-else class="text-primary">
            <nav-path 
                :path="fullPath"
                :path-contains-file="pathContainsFile"
                :style="{
                    position: 'fixed',
                    backgroundColor: '#f5f8fa',
                    padding: '1em',
                    border: '1px dashed #bbb',
                    borderRadius: '2em',
                    zIndex: 999,
                }"
            ></nav-path>
        </h4>
        <h4 :style="{padding: '1em', userSelect:'none'}">&nbsp;</h4>
        <div 
            class="container-fluid"
            :style="{
                width: '80%',
                height: '100%',
            }"
        >
            <div class="tab-v1">
                <div class="row">
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 text-center">
                        <root-folder-card
                            :quick-navigation="quickNavigation"
                            :dir-path="dirPath"
                            v-on:change-path="changePath"
                        ></root-folder-card>
                    </div>
                    <div 
                        v-for="(folder, index) in directories"
                        :key="'folder' + index"
                        class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 text-center"
                    >
                        <folder-card
                            :folder="folder"
                            :dir-path="dirPath"
                            v-on:change-path="changePath"
                        ></folder-card>
                    </div>
                    <div 
                        v-for="(file, index) in files"
                        :key="'file' + index"
                        class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 text-center"
                    >
                        <file-card
                            :file="file"
                            :dir-path="dirPath"
                            v-on:change-path="changePath"
                        ></file-card>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        cannotBeSeen: Boolean,
        quickNavigation: Array,
        dirPath: String,
        directories: Array,
        files: Array,
    },
    data() {
        return {
            fullPath: '',
            pathContainsFile: false,
        }
    },
    mounted() {
        this.fullPath = this.dirPath
    },
    methods: {
        changePath(path, pathContainsFile) {
            this.fullPath = path
            this.pathContainsFile = pathContainsFile
        }
    },
}
</script>

<style scoped>

</style>