<template>
  <v-list three-line subheader>
    <v-subheader>Кошелек "{{ purse.name }}"</v-subheader>
    <v-list-tile avatar>
      <v-list-tile-content>
        <v-list-tile-title>{{ purse.rest }}</v-list-tile-title>
        <v-list-tile-sub-title>на счету</v-list-tile-sub-title>
      </v-list-tile-content>
    </v-list-tile>
    <v-list-tile>
      <v-btn @click="closeFund()">Закрыть и перевести деньги на счет цеха</v-btn>
    </v-list-tile>
  </v-list>
</template>

<script>
import axios from 'axios'

export default {

  name: 'close-fund-purse',

  props: [
    'purse'
  ],

  data () {
    return {

    }
  },

  methods: {
    closeFund() {
      const url = '';
      axios.post(url, {
        purse_id: this.purse.id
      }).then(response => {
        this.$emit('fund-close', this.purse)
      })
      
    }
  }
}
</script>

<style lang="css" scoped>
</style>