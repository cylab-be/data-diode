<template>
    <div>
        <div>
            <input 
                v-model="packageName"
                ref="package"
                v-on:keyup.enter="$refs.add.click()"
            >
            <button
                type="button"
                ref="add"
                :style="addButtonStyle"
                v-on:click="addPackageName"
                :disabled="cannotAdd"
            >
                Add package
            </button>
        </div>
        <button
            type="button"
            v-on:click="installPackages"
            :disabled="cannotInstall || packageNames.length == 0"
        >
            Download Packages
        </button>
        <div  
            style="width:100%;"          
            v-for="(pack, index) in packageNames" :key="index"
        >
            <div class="row">
                <div class="col-sm-1 col-md-1 col-lg-1 col-xl-1">
                    <button
                        type="button"
                        v-on:click="deletePackageName(index)"
                    >
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                    <p>
                        {{ pack }}
                    </p>                    
                </div>
                <div class="col-sm-1 col-md-1 col-lg-1 col-xl-1">
                    <button
                        type="button"
                        v-on:click="upPackageName(index)"
                        :disabled="index <= 0"
                    >
                        <i class="fa fa-arrow-up"></i>
                    </button>
                </div>
                <div class="col-sm-1 col-md-1 col-lg-1 col-xl-1">
                    <button
                        type="button"
                        v-on:click="downPackageName(index)"
                        :disabled="index >= packageNames.length - 1"
                    >
                        <i class="fa fa-arrow-down"></i>
                    </button>
                </div>
            </div>            
        </div>
        <div  
            style="width:100%;white-space:pre-wrap;"
            v-for="(inst, index) in installedNames" :key="index"
        >
            <p>{{ inst }}</p>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            packageName: '',
            packageNames: [],
            installedNames: [],
            cannotInstall: false,
            cannotAdd: false,
        }
    },
    mounted() {
        this.$refs.package.focus()
    },
    methods: {
        addPackageName() {
            this.installedNames = []
            if (this.packageName.length == 0) {
                toastr.error('The package name cannot be empty!')
            } else {
                this.packageNames.push(this.packageName)
                this.packageName = ''
            }
            this.$refs.package.focus()
        },
        deletePackageName(i) {
            this.packageNames.splice(i,  1)
        },
        upPackageName(i) {
            if (i > 0) {
                const save = this.packageNames[i]
                this.packageNames[i] = this.packageNames[i - 1]
                this.packageNames[i - 1] = save
                // refresh list
                this.packageNames.push('')
                this.packageNames.pop()
            }
        },
        downPackageName(i) {
            if (i + 1 < this.packageNames.length) {
                const save = this.packageNames[i]
                this.packageNames[i] = this.packageNames[i + 1]
                this.packageNames[i + 1] = save
                // refresh list
                this.packageNames.push('')
                this.packageNames.pop()
            }
        },
        installPackages() {            
            if (this.packageNames.length > 0) {
                this.cannotAdd = true
                this.cannotInstall = true
                const names = this.packageNames
                this.packageNames = []
                var me = this               
                
                let i = 0

                function send(name) {
                    return new Promise((resolve, reject) => {
                        axios.post('/pipin',
                        {
                            name: name,
                        })
                        .then(function(response){                            
                            resolve(response)
                        })
                        .catch(function(error){
                            reject(error)
                        })
                    })
                }

                function next(name) {
                    if (i == 0) {
                        me.installedNames.push('Packages installation:')
                    }
                    var name = names[i]
                    me.installedNames.push('Downloading ' + name + '...')
                    return send(name).then(response => {
                        me.installedNames.push(response.data.output)
                        i++
                        if (i < names.length) {
                            return next(name)
                        } else {
                            return
                        }
                    }).catch(error => {
                        toastr.error(error.message)
                    })
                }

                next(names[i]).then(() => {
                    me.installedNames.push('Done.')
                    this.cannotAdd = false
                    this.cannotInstall = false
                })
                
            } else {
                toastr.error('You must specify at least one package.')
            }
        },
    },
}
</script>

<style scoped>

</style>