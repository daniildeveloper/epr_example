<template>
  <div class="order-change">
    <div v-for="ware in wares">{{ ware.ware.name }}</div>
  </div>
</template>

<script>
import dragula from 'dragula'
import axios from 'axios'

export default {

  name: 'OrderChange',

  data () {
    return {
      d: null,

      wares: [], // wares array to order change
    }
  },

  methods: {
    setup() {
      // initialize dragula
      this.d = dragula();

      // setup columns
      let columns = document.querySelectorAll('.order-change');

      // add columns to dragula
      for (let column in columns) {
        this.d.containers.push(columns[column]);
      }
    },

    getData() {
      axios.get('/api/order-change')
        .then(response => {
          this.wares = response.data
        });
    },

    update() {
      console.log(this.wares);
    }
  },

  updated() {
    this.setup();
  }
}
</script>

<style lang="css" scoped>
</style>