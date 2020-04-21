
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('growing-button', require('./components/GrowingButton.vue'));
Vue.component('restart-button', require('./components/RestartButton.vue'));
Vue.component('python-pip', require('./components/PythonPip.vue'));
Vue.component('uploaders', require('./components/Uploaders.vue'));
Vue.component('main-bis', require('./components/MainBis.vue'));
Vue.component('param-window', require('./components/ParamWindow.vue'));
Vue.component('toggler', require('./components/Toggler.vue'));
Vue.component('uploader', require('./components/Uploader.vue'));
Vue.component('empty-button', require('./components/EmptyButton.vue'));
Vue.component('del-button', require('./components/DelButton.vue'));
Vue.component('add-button', require('./components/AddButton.vue'));
Vue.component('upload', require('./components/Upload.vue'));
Vue.component('upload-toggler', require('./components/UploadToggler.vue'));