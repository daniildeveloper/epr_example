<template>
  <v-dialog v-model="newAccountingDialog" v-if="hasPermission('end_accounting_period')">
    <v-btn :disabled="disabled" color="primary" slot="activator" dark>Закончить отчетный период</v-btn>
    <v-card>
      <v-card-title>
        <span class="headline">Закончить отчетный период</span>
        <v-spacer></v-spacer>
        <v-menu bottom left>
          <v-btn @click="newAccountingDialog = false" icon slot="activator">
            <v-icon>close</v-icon>
          </v-btn>
        </v-menu>
      </v-card-title>
      <v-card-text>
        
        <h3 class="headline" v-if="proposals.length === 0 && proposalsInfoGet === true">Нет заявок принятых клиентом. Прибыль посчитать невозможно</h3>

        <h3 class="headline" v-if="disabled === true">Имеются гарантийные случаи. Закрыть период невозможно.</h3>

        <v-btn @click="close" v-if="proposals.length > 0 && proposalsInfoGet === true && disabled != true">Закрыть выбранное</v-btn>
        <!-- <v-btn @click="closeSelected" v-if="selected.length > 0">Закрыть выбранное</v-btn> -->
        <v-data-table
            no-data-text="Нет данных"
            v-model="selected"
            v-bind:headers="headers"
            v-bind:items="proposals"
            select-all
            item-key="name"
            v-if="proposals.length > 0 && proposalsInfoGet === true"
          >
          <template slot="headers" slot-scope="props">
            <tr>
              <th>
                <!-- <v-checkbox
                  primary
                  hide-details
                  @click.native="toggleAll"
                  :input-value="props.all"
                  :indeterminate="props.indeterminate"
                ></v-checkbox> -->
              </th>
              <th v-for="header in props.headers" :key="header.text"
                :class="['column sortable', pagination.descending ? 'desc' : 'asc', header.value === pagination.sortBy ? 'active' : '']"
                @click="changeSort(header.value)"
              >
                <v-icon>arrow_upward</v-icon>
                {{ header.text }}
              </th>
            </tr>
          </template>
          <template slot="items" slot-scope="props">
            <tr :active="props.item.selected" @click="props.item.selected = !props.item.selected">
              <td>
                <v-checkbox
                  primary
                  hide-details
                  :input-value="props.item.selected"
                ></v-checkbox>
              </td>
              <td class="text-xs-left">{{ props.item.id }}</td>
              <td class="text-xs-left">{{ props.item.code }}</td>
              <td class="text-xs-left">{{ props.item.creator.name }}</td>
              <td class="text-xs-left">{{ props.item.client.name}}</td>
              <td class="text-xs-left">{{ props.item.created_at }}</td>
            </tr>
          </template>
        </v-data-table>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" flat @click="newAccountingDialog = false">Закрыть</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import {mapGetters} from 'vuex';
import axios from 'axios';

/**
 * Отчетный период - закрыть - подтвердить все остатки и так прочее
 */
export default {

  name: 'new-accounting',

  props: [
    'disabled',
  ],

  computed: mapGetters({
    user: 'authUser'
  }),

  data () {
    return {
      allMoneyTransactions: [],
      proposals: [],
      proposalsInfoGet: false, // signal of this.getData()
      last_proposal: null,

      unclosedProposalsUrl: '/api/purse/accounting/finances/unclosed-proposals', // unclosed proposals
      newAccountingDialog: false,

      pagination: {
        sortBy: 'name'
      },
      selected: [],
      headers: [
        { text: '#', value: 'id' },
        { text: 'Заявка', value: '' },
        { text: 'Ответственный', value: '' },
        { text: 'Клиент', value: '' },
        { text: 'Создано', value: '' },
      ],
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

    getData() {
      // get all unclosed proposals list
      axios.get(this.unclosedProposalsUrl)
        .then(response => {
          let items = response.data

          items.forEach(item => {
            item.selected = true
          })
          this.proposals = items;
          this.proposalsInfoGet = true;
          console.log(items)
        })
    },

    submit() {
      const url = '/api/purse/accounting/store';
      // const proposalsToClose = [];
      // 
      this.$store.dispatch('setLoading', {
          loading: true,
        })

      axios.post(url, {}).then(response => {
        this.newAccountingDialog = false;
        this.$emit('new-accountng-created');
        this.$store.dispatch('setLoading', {
          loading: false,
        })
      }).catch(err => {
        this.$store.dispatch('setLoading', {
          loading: false,
        })
      })
    },

    toggleAll () {
      if (this.selected.length) this.selected = []
      else this.selected = this.items.slice()
    },
    changeSort (column) {
      if (this.pagination.sortBy === column) {
        this.pagination.descending = !this.pagination.descending
      } else {
        this.pagination.sortBy = column
        this.pagination.descending = false
      }
    },

    close() {
      let selectedproposals = [];

      this.$store.dispatch('setLoading', {
          loading: true,
        })

      this.proposals.forEach(proposal => {
        if (proposal.selected === true) {
          selectedproposals.push(proposal.id)
        }
      })

      axios.post('/api/purse/accounting/store', {
        proposals_to_close: selectedproposals,
      }).then(response => {
        this.$store.dispatch('responseMessage', {
          type: 'info',
          text: 'Прибыль по выбраным заявкам на ' + response.data.accounting.created_at + ' в цеху ' + response.data.workers_incomes + ', в офисе ' + response.data.salers_incomes,
          title: 'Прибыль по выбраным заявкам',
          modal: true
        });
        this.$store.dispatch('setLoading', {
          loading: false,
        })
        this.newAccountingDialog = false;
      })
      .catch(err => {
        this.$store.dispatch('setLoading', {
          loading: false,
        })
      })
    },
  },

  mounted() {
    this.getData()
  }
}
</script>

<style lang="css" scoped>
</style>