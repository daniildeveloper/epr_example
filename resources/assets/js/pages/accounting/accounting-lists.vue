<template>
  <div>
    <v-data-table
      v-bind:headers="headers"
      :items="items"
      hide-actions
      class="elevation-1"
      no-data-text="Нет данных"
    >
     <template slot="items" slot-scope="props">
        <tr @click="props.expanded = !props.expanded">
          <td>{{ props.item.id }}</td>
          <td class="text-xs-right">{{ props.item.created_at }}</td>
        </tr>
      </template>
      <template slot="expand" slot-scope="props">
        <v-card flat>
          <headline>Сводка по отчетному периоду на {{props.item.created_at}}</headline>
          <v-subheader>Себестоимость по химии:</v-subheader>
          <v-subheader>Себестимость по упаковке:</v-subheader>
          <v-subheader>Себестимость по наклейке:</v-subheader>

          <br>

          <v-subheader>Прибыль общая: </v-subheader>
          <v-subheader>Прибыль офиса: </v-subheader>
          <v-subheader>Прибыль цеха: </v-subheader>
        </v-card>
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
  </div>

</template>

<script>
import axios from 'axios';

/**
 * Component to show all accountings
 */
export default {

  name: 'accounting-lists',

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
          sortable: false,
          value: 'id'
        },
        { text: 'Date', value: 'created_at' },
      ],
      items: []
    }
  },

  methods: {
    getData() {
      axios.get('/api/purse/accounting')
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
      const url = '/api/purse/accounting?page=' + id;

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
    this.getData();
  }
}
</script>

<style lang="css" scoped>
</style>