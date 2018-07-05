<template>
  <v-container grid-list-md text-xs-center>
    <v-layout column wrap>

      <v-flex xs12 v-for="framework in frameworks">
        <v-flex xs4>
          <v-card dark color="primary">
            <v-card-text class="px-0">{{ framework.name }}</v-card-text>
          </v-card>
        </v-flex>
        <v-flex xs4>
          <v-text-field
            label="В наличии"
            v-model="toConfirm[framework.id]"
            required
          ></v-text-field>
        </v-flex>
        <v-flex>
          <v-btn @click="submit(framework.id)">Подтвердить</v-btn>
        </v-flex>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import axios from 'axios'

/**
 * Элемент состоит из хувой тучи основ, где вы вручную записываете ожидаемые остатки и подтверждатее их наличие
 */
export default {

  name: 'framework-inventory',

  data () {
    return {
      frameworks: [],
      toConfirm: {}
    }
  },

  methods: {
    getData() {
      const url = '/api/stock-data/frameworks'
      axios.get(url).then(result => {
          this.frameworks = result.data.data

          // Инициализируем остатки для ввода
          this.frameworks.forEach(el => {
            this.toConfirm[el.id] = 0
          })
      });
    },

    submit(id) {
      
    }
  }
}
</script>

<style lang="css" scoped>
</style>