<template>
  <v-expansion-panel expand>
    <v-expansion-panel-content v-for="i in purseCategories" :key="i.id">
      <div slot="header">{{ i.name }}</div>
      <v-card>
        <v-list three-line subheader>
          <v-list-tile v-for="purse in i.purses" :key="purse.id" :data="purse">
            <v-list-tile-content>
              <v-list-tile-title>{{ purse.name }}</v-list-tile-title>
              <v-list-tile-sub-title> 
                <v-money v-model="purse.rest" v-bind="money"></v-money></v-list-tile-sub-title>
            </v-list-tile-content>
            <v-list-tile-action>
              <v-layout row v-if="hasPermission(purse.permission)">
                <v-flex>
                  <v-btn @click="selectPurseToTakeDown(purse)">Снять</v-btn>
                </v-flex>

                <v-dialog v-if="purse.category_id === 3"  v-model="closeFundDialog" persistent max-width="700px" >
                  <v-btn slot="activator" dark>Закрыть фонд</v-btn>
                  <v-card>
                    <v-card-title>
                      <span class="headline">Закрыть фонд</span>
                    </v-card-title>
                    <v-card-text>
                      <close-fund-purse-view
                        :purse="purse"
                        @fund-close="onFundClose"
                      ></close-fund-purse-view>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="blue darken-1" flat @click.native="closeFundDialog = false">Закрыть</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>

                <v-flex>
                  <v-btn @click="selectPurseToUp(purse)">Пополнить</v-btn>
                </v-flex>

                <!-- takeDownDialog -->
                <v-dialog v-model="takeDownDialog" max-width="290">
                  <v-card>
                    <v-card-title class="headline">Снять деньги с кошелька {{ currentPurse.name }}</v-card-title>
                    <v-card-text>
                      <p>На кошельке: {{ currentPurse.rest }}</p>
                      <v-text-field
                        slot="activator"
                        label="Сумма для снятия"
                        v-model="sum"
                        prepend-icon="money"
                      ></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="green darken-1" flat="flat" @click.native="takeDownDialog = false">Отмена</v-btn>
                      <v-btn color="green darken-1" flat="flat" @click.native="updatePurseTakeDown">Подтвердить</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
                <!-- end takeDownDialog -->

                <!-- Возможность снять деньги, которые автомтатом попадут на кошелек прибылив -->
                <v-flex v-if="purse.id === 8">
                  <v-btn @click="selectPurseToTakeProfit(purse)">Снять прибыль</v-btn>
                </v-flex>
                <!-- end purse take down activator -->

                <!-- takeDownDialog -->
                <v-dialog v-model="takeProfitDialog" max-width="290">
                  <v-card>
                    <v-card-title class="headline">Снять прибыль {{ currentPurse.name }}</v-card-title>
                    <v-card-text>
                      <p>На кошельке: {{ currentPurse.rest }}</p>
                      <v-text-field
                        slot="activator"
                        label="Сумма для снятия"
                        v-model="sum"
                        prepend-icon="money"
                      ></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="green darken-1" flat="flat" @click.native="takeProfitDialog = false">Отмена</v-btn>
                      <v-btn color="green darken-1" flat="flat" @click.native="updatePurseTakeProfit">Подтвердить</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
                <!-- end takeDownDialog -->

                <v-dialog v-model="takeUpDialog" max-width="290">
                  <v-card>
                    <v-card-title class="headline">Пополнить кошелек {{ currentPurse.name }}</v-card-title>
                    <v-card-text>
                      <p>На кошельке: {{ currentPurse.rest }}</p>
                      <v-text-field
                        slot="activator"
                        label="Сумма для пополнения"
                        v-model="sum"
                        prepend-icon="money"
                      ></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn color="green darken-1" flat="flat" @click.native="takeUpDialog = false">Отмена</v-btn>
                      <v-btn color="green darken-1" flat="flat" @click.native="updatePurseTakeUp">Подтвердить</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-layout>
            </v-list-tile-action>
          </v-list-tile>
        </v-list>
      </v-card>
    </v-expansion-panel-content>
  </v-expansion-panel>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'
import {Money} from 'v-money'

import CloseFundPurse from '~/pages/purses/close-fund-purse'


export default {

  name: 'all-purses',

  computed: mapGetters({
    user: 'authUser'
  }),

  components: {
    'close-fund-purse-view': CloseFundPurse,
    'v-money': Money,
  },

  data () {
    return {
      purseCategories: [],

      takeDownDialog: false,
      takeUpDialog: false,
      closeFundDialog: false,
      takeProfitDialog: false,

      sum: 0,

      currentPurse: {},

      money: {
        decimal: ',',
        thousands: '.',
        prefix: '',
        suffix: ' тг',
        precision: 2,
        masked: false
      }
    }
  },

  methods: {
    getData() {
      axios.get('/api/purse/all')
        .then(response => {
          this.purseCategories = response.data
        })
    },

    selectPurseToTakeDown(purse) {
      this.currentPurse = purse
      this.takeDownDialog = true
    },

    selectPurseToUp(purse) {
      this.currentPurse = purse
      this.takeUpDialog = true
    },

    updatePurseTakeDown() {
      axios.post('/api/purse/take-down', {
        sum: this.sum,
        purse_id: this.currentPurse.id
      }).then(response => {
        this.getData()
        this.takeDownDialog = false
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Снятие с кошелька',
          modal: false
        })
      })
    },

    updatePurseTakeUp() {
      axios.post('/api/purse/take-up', {
        sum: this.sum,
        purse_id: this.currentPurse.id
      }).then(response => {
        this.getData()
        this.takeUpDialog = false
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Кошелек пополнен',
          modal: false
        })
      })
    },

    hasPermission(permission) {
      let permissionsArray = [];
      this.user.data.permissions.forEach(p => {
        permissionsArray.push(p.name);
      });

      if (permissionsArray.indexOf(permission) !== -1) {
        return true;
      }
      return false
    },

    /**
     * Срабатывает при закрытии фонда
     */
    onFundClose() {
      this.getData()
      this.closeFundDialog = false
      this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Фонд закрыт',
          modal: false
        })
    },

    /**
     * Выводить деньги с кошелька системы на кошелек прибыли
     * @param  {purse} purse Кошелек
     * @return {}
     */
    selectPurseToTakeProfit(purse) {
      this.currentPurse = purse
      this.takeProfitDialog = true
    },

    updatePurseTakeProfit() {
      // take profit
      this.takeProfitDialog = false
      const url = '/api/purse/take-profit'

      if (this.sum < this.currentPurse.rest) {
        axios.post(url, {
          sum: this.sum,
          purse_id: this.currentPurse.id
        }).then(response => {
          this.sum = 0;
          this.currentPurse = {};
          this.getData()
          this.$store.dispatch('responseMessage', {
            type: 'success',
            text: 'Процент распределения прибыли изменен. Теперь цех получает ' + this.sum + '%',
            modal: false
          })
        })
      }
    }
  },

  mounted() {
    this.getData();

    window.addEventListener('proposal-created', function () {
      this.getData();
    });

    window.addEventListener('financial-data-updated', function() {
      this.getData();
    });
  },
}
</script>

<style lang="css" scoped>
</style>