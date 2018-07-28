<template>
    <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-btn color="primary" dark @click.stop="newWatchDialog = true">Новая вахта</v-btn>
          <v-dialog v-model="newWatchDialog" max-width="500px">
            <v-card>
              <v-card-title>
                <span>
                  Новая вахта
                </span>
                <v-spacer></v-spacer>
                <v-menu bottom left>
                  <v-btn @click="newWatchDialog = false" icon slot="activator">
                    <v-icon>close</v-icon>
                  </v-btn>
                </v-menu>
              </v-card-title>
              <v-card-text>
                <watch-dialog/>
              </v-card-text>
            <v-card-actions>
              <v-btn color="primary" flat @click.stop="newWatchDialog=false">Закрыть</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
      </v-flex>

      <v-flex>
        <v-data-table
          :headers="headers"
          :items="items"
          hide-actions
          item-key="name"
        >
          <template slot="items" slot-scope="props">
            <tr @click="selectRow(props.item, props)">
              <td >{{ props.item.name }}</td>
              <td class="text-xs-right">{{ props.item.price }}</td>
              <td class="text-xs-right">{{ props.item.minimal_in_stock }}</td>
            </tr>
          </template>
          <template slot="expand" slot-scope="props" >
            <v-card flat>
              <v-container grid-list-md text-xs-center>
                <v-layout row wrap>
                  <v-flex xs12 sm6 v-if="hasPermission('crud_nomenclatures')">
                    <progress-bar :show="busy"></progress-bar>
                    <form @submit.prevent="update">

                      <v-card-title primary-title>
                        <h4 class="headline mb-0">{{ props.item.name }} редактирование</h4>
                      </v-card-title>

                      <v-text-field
                        label="Название"
                        v-model="updateForm.name"
                        :rues="nameRules"
                        required
                      ></v-text-field>

                      <v-text-field
                        label="Стоимость"
                        v-model="updateForm.price"
                        required
                      ></v-text-field>

                      <v-text-field
                        label="Минимум на складе"
                        v-model="updateForm.minimal_in_stock"
                        required
                      ></v-text-field>

                      <v-card-text>
                        <submit-button :block="true" :form="updateForm" label="Обновить"></submit-button>
                      </v-card-text>

                    </form>
                  </v-flex>
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
import axios from 'axios';

import WatchDialog from '~/pages/watch/WatchDialog';

/**
 * Tabled component with watches
 */
export default {

  name: 'WatchesIndex',

  components: {
    WatchDialog,
  },

  data () {
    return {
        newWatchDialog: false,

        headers: [
          {
            text: '№',
            align: 'left',
            value: 'id'
          },
          { text: 'Сотрудник', value: '' },
          { text: 'Начало', value: '' },
          { text: 'Конец', value: '' },
          { text: 'Плата за 30 дней', value: '' },
          { text: 'Плата за вахту', value: '' },
        ],
        busy: false,
        items: [],
    }
  },

  methods: {
    getData() {
        axios.get('/api/watch')
            .then(response => {
                this.items = response.data.data;
            })
    }
  }
}
</script>

<style lang="css" scoped>
</style>