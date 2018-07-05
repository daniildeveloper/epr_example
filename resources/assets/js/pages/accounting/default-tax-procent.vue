<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" width="600px">
      <v-btn color="primary" dark slot="activator">Налоги(%)</v-btn>
      <v-card>
        <v-card-title>
          <span class="headline">Налоговые ставки по умолчанию</span>
          <v-spacer></v-spacer>
          <v-menu bottom left>
            <v-btn @click="dialog = false" icon slot="activator">
              <v-icon>close</v-icon>
            </v-btn>
          </v-menu>
        </v-card-title>
        <v-card-text>
          <v-layout row wrap v-for="item in items" :key="item.id" :data="item">
            <v-flex xs4>
              <v-subheader>{{ item.name }}</v-subheader>
            </v-flex>
            <v-flex xs4>
              <v-text-field
                v-model="item.tax"
              ></v-text-field>
            </v-flex>
            <v-flex xs4>
              <v-btn @click="updateTax(item.id, item.tax)">Изменить</v-btn>
            </v-flex>
          </v-layout>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="green darken-1" flat="flat" @click="dialog = false">Закрыть</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
import axios from 'axios';

/**
 * Component to show tax
 */
export default {

  name: 'default-tax-procent',

  data () {
    return {
      dialog: false,

      /**
       * list of taxes
       * @type {Array}
       */
      items: [],
    }
  },

  methods: {
    /**
     * Get initial data
     * @return {[type]} [description]
     */
    getData() {
      axios.get('/api/owner/taxes')
        .then(response => {
          this.items = response.data
        });
    },

    /**
     * Add defaul tax procent
     */
    addDefaultTaxProcent() {},

    /**
     * Update tax procent
     * @param  {int} id int
     * @return {int}    int
     */
    updateTax(id, tax) {
      axios.put('/api/owner/taxes/update/' + id, {
        tax: tax
      }).then(response => {
        this.getData();

        // show message
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Налоговая ставка изменена',
          modal: false
        })
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