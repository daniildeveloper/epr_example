<template>
    <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-data-table
          :headers="headers"
          :items="items"
          no-data-text="Нет данных"
          hide-actions
        >
          <template slot="items" slot-scope="props">
            <tr @click="selectRow(props.item, props)">
              <!-- <td >{{ props.item. }}</td> -->
            </tr>
          </template>
          <template slot="expand" slot-scope="props" >
            <v-card flat>
              <v-container grid-list-md text-xs-center>
                <v-layout row wrap>
                </v-layout>
              </v-container>
            </v-card>
          </template>
        </v-data-table>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import axios from 'axios'

/**
 * Component let show table with accounting data history
 */
export default {

  name: 'history-data',

  data () {
    return {
        items: [],
        purses: [],

        headers: [
          {
            text: 'Название',
            align: 'left',
            value: 'name'
          },
          { text: 'Цена', value: 'price' },
          { text: 'Минимум на складе', value: 'minimal_in_stock' },
        ],
    }
  },

  methods: {
    /**
     * Get initial data for setup
     * @return {[type]} [description]
     */
    getData() {
        axios.get('api/purse/accounting/history-data ')
            .then(response => {
                this.items = response.data.data;
                this.purses = response.data.purses;
                console.log(response.data)
            });
    }
  },
  mounted() {
    this.getData()
  }
}
</script>

<style lang="css" scoped>
</style>