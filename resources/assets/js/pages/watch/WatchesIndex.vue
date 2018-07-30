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
          item-key="id"
        >
          <template slot="items" slot-scope="props">
            <tr @click="props.expanded = !props.expanded">
              <td>{{props.item.id}}</td>
              <td>{{ props.item.watcher.name }}</td>
              <td>{{ props.item.begin_date }}</td>
              <td>{{ props.item.end_date ? props.item.end_date : '' }}</td>
              <td>{{props.item.monthly_payment}}</td>
              <td>{{props.item.watch_end_payment ? props.item.watch_end_payment : '' }}</td>
            </tr>
          </template>
          <template slot="expand" slot-scope="props" >
            <v-card flat>
              <v-card-text>
                <div class="headline">
                  {{ props.item.watcher.name }}, с {{ props.item.begin_date }} по {{ props.item.end_date ? props.item.end_date : 'настоящее время' }}
                </div>
                <v-layout row wrap>

                  <!-- add money dialog -->
                  <v-flex md2>
                    <add-money-dialog 
                      :watcher_name="props.item.watcher.name"
                      :watch_id="props.item.id"
                      @new-money-transaction="getData()"
                      />
                  </v-flex>
                  <!-- end add money dialog -->

                  <!-- minus money dialog -->
                  <v-flex md2>
                    <minus-money-dialog 
                      :watcher_name="props.item.watcher.name"
                      :watch_id="props.item.id"
                      @new-money-transaction="getData()"
                      />
                  </v-flex>
                  <!-- end minus money dialog -->

                  <!-- end watch  dialog -->
                  <v-flex md2>
                    <end-watch-dialog
                      :watcher_name="props.item.watcher.name"
                      :watch_id="props.item.id"
                      @watch-end="getData()"
                    />
                  </v-flex>
                  <!-- end end watch dialog -->

                  <!-- pay watch dialog -->
                  <v-flex md2>
                    <pay-watch-dialog
                      :watch_id="props.item.id"
                      @watch-pay="getData()"
                    />
                  </v-flex>
                  <!-- pay watch dialog -->
                </v-layout>
        
                <!-- all watch payments -->
                <v-layout row wrap>
                  <ul v-if="props.item.watch_money_transactions.length > 0">
                    <li v-for="t in props.item.watch_money_transactions">
                      {{t.refill === 1 ? 'Пополнение' : 'Убыток'}} на {{t.sum}} от {{t.created_at}}
                    </li>
                  </ul>
                </v-layout>
                <!-- end all watch payments -->
              </v-card-text>
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
import AddMoneyDialog from '~/pages/watch/AddMoneyDialog';
import MinusMoneyDialog from '~/pages/watch/MinusMoneyDialog';
import EndWatchDialog from '~/pages/watch/EndWatchDialog';
import PayWatchDialog from '~/pages/watch/PayWatchDialog';

/**
 * Tabled component with watches
 */
export default {

  name: 'WatchesIndex',

  components: {
    WatchDialog,
    AddMoneyDialog,
    MinusMoneyDialog,
    EndWatchDialog,
    PayWatchDialog,
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
          { text: 'Сотрудник',align: 'left', value: '' },
          { text: 'Начало',align: 'left', value: '' },
          { text: 'Конец',align: 'left', value: '' },
          { text: 'Плата за 30 дней',align: 'left', value: '' },
          { text: 'Плата за вахту',align: 'left', value: '' },
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
        axios.get('/api/manufactory/watch/watcher-watches/' + this.watcher)
            .then(response => {
                this.items = response.data;
                this.$store.dispatch('setLoading', {
                  loading: false
                });
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
              type: 'error',
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
    // this.getData();
    this.getWatchers();
  },

  watch: {
    watcher: function (val) {
      this.$store.dispatch('setLoading', {
        loading: true
      });
      this.getData();
    }
  }
}
</script>

<style lang="css" scoped>
</style>