import Vue from 'vue'
import Vuetify from 'vuetify'
import money from 'v-money'
import VeeValidate from 'vee-validate'
import store from '~/store'
import router from '~/router'
import { i18n } from '~/plugins'
import App from '~/components/App'
import '~/components'


// define globlly variables
window.jQuery = window.$ = require('jquery');
require('./pusher-integration')
// require('./offline')

Vue.use(Vuetify)
Vue.use(money,  {precision: 0})
Vue.use(VeeValidate)

Vue.config.productionTip = false

new Vue({
  el: '#app',
  i18n,
  store,
  router,
  ...App
})

window.addEventListener('offline', function() {
  console.log('offline mode');
})

if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw.js')
  .then(function () {
    console.log('Service worker registred');
  });
}