<template>
    <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-layout row wrap>
          <v-flex xs2>
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
                  <watch-dialog
                    :watchers="watchers"
                    @new-watch-created="createNewWatch"
                    :validationErr="validationErr"
                  />
                </v-card-text>
              <v-card-actions>
                <v-btn color="primary" flat @click.stop="newWatchDialog=false">Закрыть</v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-flex>
          <v-flex xs4>
            <v-select
              autocomplete
              label="Выбор рабочего"
              v-model="watcher"
              placeholder="Выбрать рабочего"
              :items="watchers"
              item-text="name"
              item-value="id"
              required
            ></v-select>
          </v-flex>
        </v-layout>
        
          
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
              <td>{{props.item.id}}</td>
              <td>{{ props.item.watcher.name }}</td>
              <td>{{ props.item.begin_date }}</td>
              <td>{{ props.item.end_date ? props.item.end_date : '' }}</td>
              <td>{{props.item.montly_payment}}</td>
              <td>{{props.item.watch_end_payment ? props.item.watch_end_payment : '' }}</td>
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

        watchers: [],
        watcher: null,

        validationErr: null,
    }
  },

  methods: {
    getData() {
        axios.get('/api/watch')
            .then(response => {
                this.items = response.data.data;
            })
    },

    getWatchers() {
      axios.get('/api/manufactory/watch/watchers')
        .then(response => {
          this.watchers = response.data;
        })
    },

    createNewWatch(proposal_data) {
      this.$store.dispatch('setLoading', {
        loading: true
      });
      console.log('proposal_data', proposal_data);

      axios.post('/api/manufactory/watch', proposal_data)
        .then(response => {
          this.$store.dispatch('setLoading', {
            loading: false
          });

          // check for validation errors
          if (response.data.status != 'validator_errors') {
            this.newWatchDialog = false;
            this.$store.dispatch('responseMessage', {
              modal: false,
              text: 'Новая вахта начата',
              type: 'success'
            })
            this.clear();
          } else {
            this.$store.dispatch('responseMessage', {
              modal: false,
              text: 'при создании новой вахты произошла ошибка',
              modal: 'error',
            });
            // update errors
            this.validationErr = response.data.errors;
          }
        })
        .catch(err => {
          this.$store.dispatch('setLoading', {
            loading: false
          });
          this.$store.dispatch('responseMessage', {
            modal: false,
            text: 'при создании новой вахты произошла ошибка',
            type: 'error'
          })
        })
    },

    clear() {
      this.validationErr = null;
    }

  },

  mounted() {
    this.getData();
    this.getWatchers();
  }
}
</script>

<style lang="css" scoped>
</style>