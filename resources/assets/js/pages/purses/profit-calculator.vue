<template>
  <v-card dark>
    <v-card-text>
      <p>Цеху: {{ val }}%</p>
      <p>Офису: {{ 100 - val }}%</p>
      <v-slider :label="label" v-model="val" :track-color="'green lighten-1'"></v-slider>
    </v-card-text>
    <v-btn @click="save"> Сохранить </v-btn>

  </v-card>
</template>

<script>
import axios from 'axios'

export default {

  name: 'profit-calculator',

  data () {
    return {
      label: '',
      val: 50,
      color: 'green lighten-1'
    }
  },

  methods: {
    save() {
      axios.post('/api/purse/profit-calculator', {
        val: this.val
      }).then(response => {
        this.$emit('profit-calculator-update')
      })
    },
    getData() {
      const url = '/api/purse/profit-calculator/current'
      axios.get(url)
        .then(response => {
          this.val = response.data.workers_profit;
        })
    }
  },

  mounted() {
    this.getData()
  }
}
</script>

<style lang="css" scoped>
</style>