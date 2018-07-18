<template>
  <div>
    <v-layout row justify-start wrap>

      <!-- profit coordintor -->
      <v-flex>
        <v-dialog v-model="profitCoordinatorDialog" persistent max-width="700px" v-if="hasPermission('invite_users')" >
          <v-btn color="primary" slot="activator" dark>Распределение прибыли</v-btn>
          <v-card>
            <v-card-title>
              <span class="headline">Распределение прибыли</span>
              <v-spacer></v-spacer>
              <v-menu bottom left>
                <v-btn @click="profitCoordinatorDialog = false" icon slot="activator">
                  <v-icon>close</v-icon>
                </v-btn>
              </v-menu>
            </v-card-title>
            <v-card-text>
              <profit-calculator-view
                @profit-calculator-update="onProfitCalculatorUpdate"
              ></profit-calculator-view>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click.native="profitCoordinatorDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-flex>
      <!-- end profit coordinator -->
      
      <!-- new purse dialog -->
      <v-flex>
        <v-dialog v-model="newPurseDialog" persistent max-width="700px" v-if="hasPermission('finances')" >
          <v-btn color="primary" slot="activator" dark>Новый кошелек-фонд</v-btn>
          <v-card>
            <v-card-title>
              <span class="headline">Новый кошелек-фонд</span>
              <v-spacer></v-spacer>
              <v-menu bottom left>
                <v-btn @click="newPurseDialog = false" icon slot="activator">
                  <v-icon>close</v-icon>
                </v-btn>
              </v-menu>
            </v-card-title>
            <v-card-text>
              <new-purse-view
                @new-purse-created="onNewPurseCreated()"
              ></new-purse-view>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click.native="newPurseDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-flex>
      <!-- end new purse dialog -->
      
      <!-- new report dialog -->
      <v-flex>
        <v-dialog v-model="newReportDialog" persistent max-width="700px" v-if="hasPermission('finances')" >
          <v-btn color="primary" slot="activator" dark>Отчеты</v-btn>
          <v-card>
            <v-card-title>
              <span class="headline">Выгрузить отчеты</span>
              <v-spacer></v-spacer>
              <v-menu bottom left>
                <v-btn @click="newReportDialog = false" icon slot="activator">
                  <v-icon>close</v-icon>
                </v-btn>
              </v-menu>
            </v-card-title>
            <v-card-text>
              <reports-generate-view
                @new-report-created="onNewReportCreated()"
              ></reports-generate-view>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click.native="newReportDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-flex>
      <!-- end new report dialog -->
      
      <!-- accounting period end -->
      <v-flex>
        <end-accounting-period-view :disabled="disableAccountingPeriodEnd"></end-accounting-period-view>
      </v-flex>
      <!-- end accounting period end -->

      <!-- Издержки производства: отмена заявок, гарантийные случаи -->
      <v-flex>
        <v-dialog v-model="problemsDialog" persistent max-width="500px" v-if="hasPermission('money_request')" >
          <v-btn color="primary" slot="activator" dark>Издержки</v-btn>
          <v-card>
            <v-card-title>
              <span class="headline">Издержки</span>
              <v-spacer></v-spacer>
              <v-menu bottom left>
                <v-btn @click="problemsDialog = false" icon slot="activator">
                  <v-icon>close</v-icon>
                </v-btn>
              </v-menu>
            </v-card-title>
            <v-card-text>
              <!-- <declined-proposals-rests-view></declined-proposals-rests-view> -->
              <proposal-warranty-cases-view></proposal-warranty-cases-view>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click.native="problemsDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-flex>
      <!-- end problem cases dialog -->

      <!-- default tax procent view -->
      <default-tax-procent-view v-if="hasRole('owner')"></default-tax-procent-view>
      <!-- en default tax procent view -->
    </v-layout>
    <purses-view></purses-view>
    
    <br>
    <br>
    <br>
    
    <v-layout row>

      <v-flex xs12 sm6 v-if="hasPermission('show_stock_privats')">
        <workers-incomes-list-view></workers-incomes-list-view>
      </v-flex>
      <v-flex v-else xs12 sm6>
        <salers-incomes-list-view></salers-incomes-list-view>
      </v-flex>

    </v-layout>

    <v-layout row>
      <v-flex xs12 sm6>
        <proposals-pie-chart-view></proposals-pie-chart-view>
      </v-flex>
      <v-flex xs12 sm6>
        <working-capital/>
      </v-flex>
    </v-layout>
    
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import axios from 'axios';

import Purses from '~/pages/purses/all-purses'
import ProfitCalculator from '~/pages/purses/profit-calculator'
import EndAccountingPeriod from '~/pages/accounting/new-accounting'
import WorkersIncomesList from '~/pages/accounting/workers-incomes-list'
import SalersIncomesList from '~/pages/accounting/salers-incomes-list'
import NewPurse from '~/pages/purses/new-purse'
import ReportGenerate from '~/pages/accounting/reports'
// import DeclinedProposalRests from '~/pages/proposal/declined-proposal-rests'
import ProposalsChart from '~/pages/proposal/proposals-pie-chart'
import ProposalWarrantyCases from '~/pages/accounting/warranty-cases'
import DefaultTaxProcent from '~/pages/accounting/default-tax-procent'
import WorkingCapital from '~/pages/accounting/working-capital'

export default {

  name: 'finances',

  components: {
    'purses-view': Purses,
    'profit-calculator-view': ProfitCalculator,
    'end-accounting-period-view': EndAccountingPeriod,
    'workers-incomes-list-view': WorkersIncomesList,
    'new-purse-view': NewPurse,
    'reports-generate-view': ReportGenerate,
    'proposals-pie-chart-view': ProposalsChart,
    'proposal-warranty-cases-view': ProposalWarrantyCases,
    'default-tax-procent-view': DefaultTaxProcent,
    'salers-incomes-list-view': SalersIncomesList,
    WorkingCapital,
  },

  computed: mapGetters({
    user: 'authUser'
  }),

  metaInfo () {
    return { title: 'Финансы' }
  },

  data () {
    return {
      profitCoordinatorDialog: false,
      moneyRequestDialog: false,
      newPurseDialog: false,
      newReportDialog: false,
      problemsDialog: false,

      disableAccountingPeriodEnd: false, // use to check accountings
    }
  },

  methods: {
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

    hasRole(role) {
      let rolesArray = [];

      this.user.data.roles.forEach(r => {
        rolesArray.push(r.name)
      });

      if (rolesArray.indexOf(role) !== -1) {
        return true;
      }

      return false;
    },

    onProfitCalculatorUpdate() {
      this.profitCoordinatorDialog = false
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Процент распределения средств изменен',
        modal: false
      })
    },

    onNewTransferRequest() {
      this.moneyRequestDialog = false
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Создан новый запрос средств',
        modal: false
      })
    },

    onNewPurseCreated() {
      this.newPurseDialog = false
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Новый кошелек-фонд создан',
        modal: false
      })
    },

    onNewReportCreated() {
      this.newReportDialog = false
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Отчет выгружен',
        modal: false
      })
    },

    getData() {
      const url = '/api/purse/accounting/warranty-cases/actual'

      axios.get(url)
        .then(response => {
          let items = response.data

          if (items.length > 0) {
            this.disableAccountingPeriodEnd = true
          }
        })
    }
  },

  mounted() {
    this.getData();
  }
}
</script>

<style lang="css" scoped>
</style>