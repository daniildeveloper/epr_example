<template>
  <v-progress-linear
    :indeterminate="true"
    :active="loading"
    height="3"
    :color="canSuccess ? color : failedColor"
  >
  </v-progress-linear>
</template>

<script>
// Based on https://github.com/nuxt/nuxt.js/blob/master/lib/app/components/nuxt-loading.vue
import Vue from 'vue';
import {mapGetters} from 'vuex';

export default {
  name: 'v-loading',
  data: () => ({
    percent: 0,
    show: true,
    canSuccess: true,
    duration: 3000,
    color: 'accent',
    failedColor: 'error'
  }),
  computed: mapGetters({
    loading: 'loading'
  }),

  methods: {
    start () {
      this.show = true
      this.canSuccess = true
      if (this._timer) {
        clearInterval(this._timer)
        this.percent = 0
      }
      this._cut = 10000 / Math.floor(this.duration)
      this._timer = setInterval(() => {
        this.increase(this._cut * Math.random())
        if (this.percent > 95) {
          this.finish()
        }
      }, 100)
      return this
    },
    set (num) {
      this.show = true
      this.canSuccess = true
      this.percent = Math.floor(num)
      return this
    },
    get () {
      return Math.floor(this.percent)
    },
    increase (num) {
      this.percent = this.percent + Math.floor(num)
      return this
    },
    decrease (num) {
      this.percent = this.percent - Math.floor(num)
      return this
    },
    finish () {
      this.percent = 100
      this.hide()
      return this
    },
    pause () {
      clearInterval(this._timer)
      return this
    },
    hide () {
      clearInterval(this._timer)
      this._timer = null
      setTimeout(() => {
        this.show = false
        Vue.nextTick(() => {
          setTimeout(() => {
            this.percent = 0
          }, 200)
        })
      }, 500)
      return this
    },
    fail () {
      this.canSuccess = false
      return this
    }
  }
}
</script>

<style lang="stylus" scoped>
.progress-linear
  height: 2px
  margin: 0
  position: absolute
  top: 0
  left: 0
  right: 0
  width: 100%
  z-index: 999999
</style>
