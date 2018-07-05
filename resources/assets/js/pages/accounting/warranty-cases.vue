<template>
  <v-dialog v-model="problemsDialog" persistent>
    <v-btn color="primary" slot="activator" dark>По гарантиям</v-btn>
    <v-card>
      <v-card-title>
        <span class="headline">По гарантиям</span>
        <v-spacer></v-spacer>
        <v-menu bottom left>
          <v-btn @click="problemsDialog = false" icon slot="activator">
            <v-icon>close</v-icon>
          </v-btn>
        </v-menu>
      </v-card-title>
      <v-card-text>
        <!-- <v-btn @click="closeAll">Закрыть все</v-btn> -->
        <v-btn @click="closeSelected">Закрыть выбранное</v-btn>
        <v-data-table
            v-model="selected"
            v-bind:headers="headers"
            v-bind:items="items"
            select-all
            v-bind:pagination.sync="pagination"
            item-key="name"
            class="elevation-1"
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
              <td class="text-xs-right">{{ props.item.id }}</td>
              <td class="text-xs-right">{{ props.item.proposal.code }}</td>
              <td class="text-xs-right">{{ props.item.transaction.sum }}</td>
              <td class="text-xs-right">{{ props.item.transaction.argument }}</td>
              <td class="text-xs-right">{{ props.item.created_at }}</td>
            </tr>
          </template>
        </v-data-table>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" flat @click.native="problemsDialog = false">Закрыть</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from 'axios';

/**
 * Component with dialog with all unclosed declined proposals rests
 */
export default {

  name: 'warranty-cases',

  data () {
    return {
      problemsDialog: false,
      pagination: {
        sortBy: 'name'
      },
      selected: [],
      headers: [
        { text: '#', value: 'id' },
        { text: 'Заявка', value: '' },
        { text: 'Сумма', value: '' },
        { text: 'Аргумент', value: '' },
        { text: 'Создано', value: '' },
      ],
      items: [
      ]
    }
  },

  methods: {
    getData() {
      const url = '/api/purse/accounting/warranty-cases/actual'

      axios.get(url)
        .then(response => {
          let items = response.data

          items.forEach(item => {
            item.selected = false
          })

          this.items = items
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

    closeAll() {
      const url = '/api/purse/accounting/warranty-cases/close-all'
      let itemsToClose = [];

      items.forEach(function(item) {
        if (item.selected === true) {
          itemsToClose.push(item.id)
        }
      });

      axios.post(url, {})
        .then(response => {
          this.getData();

          this.$store.dispatch('responseMessage', {
            type: 'success',
            text: 'Все гарантийные случаи закрыты',
            modal: false
          })
          this.problemsDialog = false;
        })
    },

    closeSelected() {
      const url = '/api/purse/accounting/warranty-cases/close';
      console.log(this.selected)
      // 1. Протись по всему архиву и закрывать только выбранные заявки
      this.items.forEach(item => {
        if (item.selected === true) {
          axios.post(url, {
            warranty_id: item.id
          }).then(response => {
            this.getData();
          })
        }
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