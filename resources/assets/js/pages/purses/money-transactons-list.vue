<template>
 <v-card>
    <v-card-title>
      <v-spacer></v-spacer>
      <!-- <v-text-field
        append-icon="search"
        label="Поиск"
        single-line
        hide-details
        v-model="search"
      ></v-text-field> -->
    </v-card-title>
    <v-data-table
        v-bind:headers="headers"
        v-bind:items="items"
        v-bind:search="search"
        hide-actions
      >
      <template slot="items" slot-scope="props">
        <td>{{ props.item.id }}</td>
        <td class="text-xs-right">{{ props.item.sum }}</td>
        <td class="text-xs-right">{{ props.item.proposal ? props.item.proposal.code : '' }}</td>
        <td class="text-xs-right">{{ props.item.purseFrom ? props.item.purseFrom.name : '' }}</td>
        <td class="text-xs-right">{{ props.item.purseTo ? props.item.purseTo.name : '' }}</td>
        <td class="text-xs-right">{{ props.item.user ? props.item.user.name : '' }}</td>
        <td>{{ props.item.argument ? props.item.argument : '' }}</td>
        <td class="text-xs-right">{{ props.item.created_at}}</td>
      </template>
    </v-data-table>
     <div class="text-xs-center pt-2">
      <v-pagination 
        v-model="pagination.page" 
        :length="pages"
        :total-visible="7"
        @next="getNextPage"
        @previous="getPrevPage"
        @input="getPage"
      ></v-pagination>
    </div>
  </v-card>
</template>

<script>
import axios from 'axios';
/**
 * 1. with simple pagination
 * 2. with sorting data via action types
 */
export default {

  name: 'action-page',

  data () {
    return {
      pages: 1,
      search: '',
      pagination: {},
      selected: [],
      tmp: '',
      nextPageUrl: '',
      prevPageUrl: '',
      headers: [
        {
          text: '#',
          align: 'left',
          value: ''
        },
        { text: 'Сумма', value: 'sum' },
        { text: 'Заявка', value: 'proposal.code' },
        { text: 'С кошелька', value: '' },
        { text: 'На кошелек', value: '' },
        { text: 'Пользователь', value: '' },
        { text: 'Аргумент', value: 'argument' },

        { text: 'Дата создания', value: 'created_at' },
      ],
      busy: false,
      items: [],
    }
  },

  methods: {
    getData() {
      const url = '/api/action/transactions';
      axios.get(url)
        .then(response => {
          this.items = response.data.data
          this.pagination.page = response.data.current_page;
          this.pages = response.data.last_page;
          this.nextPageUrl = response.data.next_page_url;
          this.prevPageurl = response.data.prev_page_url;
        })
    },
    getNextPage() {
      axios.get(this.nextPageUrl)
        .then(response => {
          this.items = response.data.data;
          this.pagination.page = response.data.current_page;
          this.pages = response.data.last_page;
          this.nextPageUrl = response.data.next_page_url;
          this.prevPageUrl = response.data.prev_page_url;
        })
    },
    getPrevPage() {
      axios.get(this.prevPageUrl)
        .then(response => {
          this.items = response.data.data;
          this.pagination.page = response.data.current_page;
          this.pages = response.data.last_page;
          this.nextPageUrl = response.data.next_page_url;
          this.prevPageUrl = response.data.prev_page_url;
        })
    },
    getPage(id) {
      const url = '/api/action/transactions?page=' + id;

      axios.get(url).then(response => {
        this.items = response.data.data;
        this.pagination.page = response.data.current_page;
        this.pages = response.data.last_page;
        this.nextPageUrl = response.data.next_page_url;
        this.prevPageurl = response.data.prev_page_url;
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