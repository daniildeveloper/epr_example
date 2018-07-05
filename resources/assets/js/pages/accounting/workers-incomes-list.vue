<template>
<v-card>
  <v-list three-line subheader>
    <h3 class="headline">&nbsp;&nbsp;&nbsp;Прибыль цеха</h3>
    <v-list-tile v-for="acc in accs" :key="acc.id" :data="acc">
      <v-list-tile-content>
        <v-list-tile-title>{{ acc.created_at }}</v-list-tile-title>
      </v-list-tile-content>
      <v-list-tile-action>
        <v-layout row>
          <v-flex>
            {{ acc.sum }}
          </v-flex>
        </v-layout>
      </v-list-tile-action>
    </v-list-tile>
  </v-list>
</v-card>
</template>

<script>
import axios from 'axios';

/**
 * Компонент показывает все доходы Цеха по месяцам.
 */
export default {

  name: 'workers-incomes-list',

  data () {
    return {
      accs: []
    }
  },
  methods: {
    getData() {
      const url = '/api/purse/accounting/stock-accounting';

      axios.get(url).then(response => {
        this.accs = response.data.data;
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