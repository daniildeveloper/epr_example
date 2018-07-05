<template>
  <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-data-table
          :headers="headers"
          :items="items"
          hide-actions
          item-key="name"
        >
          <template slot="items" slot-scope="props">
            <tr>
            <!-- <tr @click="selectRow(props.item, props)"> -->
              <td >{{ props.item.address}}</td>
              <!-- <td class="text-xs-right">{{ props.item.phone }}</td> -->
            </tr>
          </template>
          <template slot="expand" slot-scope="props">
            <v-card flat>
              <v-container grid-list-md text-xs-center>
                <progress-bar :show="busy"></progress-bar>
                <form @submit.prevent="update">

                  <v-card-title primary-title>
                    <h4 class="headline mb-0">{{ props.item.name }} редактирование</h4>
                  </v-card-title>

                  <v-text-field
                    label="Адрес"
                    v-model="updateForm.address"
                    :rues="nameRules"
                    required
                  ></v-text-field>

                  <v-text-field
                    label="Телефон"
                    v-model="updateForm.phone"
                    required
                  ></v-text-field>

                  <v-text-field
                    label="Email"
                    v-model="updateForm.email"
                    required
                  ></v-text-field>

                  <v-layout row>
                    <v-flex xs4>
                      <v-subheader>Заметки</v-subheader>
                    </v-flex>
                    <v-flex xs8>
                      <v-text-field
                        v-model="updateFor.notes"
                        name="notes"
                        label="Заметки о поставщике."
                        value=""
                        multi-line
                      ></v-text-field>
                    </v-flex>
                  </v-layout>

                  <v-card-text>
                    <submit-button :block="true" :form="updateForm" label="Обновить"></submit-button>
                  </v-card-text>

                </form>
              </v-container>
            </v-card>
          </template>
        </v-data-table>
      </v-flex>
    </v-layout>

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
  </v-container>
</template>


<script>
import axios from 'axios'
import Form from 'vform'

/**
 * Страница для отображения всех наименований основ (чиcтых, которые поступают на склад)
 */
export default {

  name: 'supplier',

  components: {
  },

  data () {
    return {
      newSupplierDialog: false,
      headers: [
        {
          text: 'Адрес',
          align: 'left',
          value: 'address'
        },
        // { text: 'Телефон', value: 'phone' },
      ],
      busy: false,
      items: [],
      updateForm: new Form({
        id: 0,
        name: '',
        phone: '',
        email: '',
        notes: '',
      }),
      nameRules: [
        (v) => v && v.length <= 2 || 'В названии не может быть меньше 2-х букв'
      ],
      priceRules: [
        (v) => v && v < 0 || 'Цена не может быть отрицательной. Пройдите школу заново'
      ],

      pages: 1,
      search: '',
      pagination: {},
      selected: [],
      tmp: '',
      nextPageUrl: '',
      prevPageUrl: '',
    }
  },

  methods: {
    getClients() {
      const url = '/api/proposal/object'
      axios.get(url).then(response => {
          this.items = response.data.data
          this.pagination.page = response.data.current_page;
          this.pages = response.data.last_page;
          this.nextPageUrl = response.data.next_page_url;
          this.prevPageurl = response.data.prev_page_url;
      });
    },

    /**
     * Update framework
     * @param  {[type]} id [description]
     * @return {[type]}    [description]
     */
    async update() {
      const url = 'api/stock/client/' + this.updateForm.id
      const {data} = await this.updateForm.put(url)
      this.getClients()
    },

    getData() {
      this.getClients()
    },

    selectRow(item, props) {
      props.expanded = !props.expanded

      this.updateForm.id = item.id
      this.updateForm.name = item.name
      this.updateForm.price = item.price
      this.updateForm.minimal_in_stock = item.minimal_in_stock
    },

    onCreate() {
      this.newSupplierDialog = false;
    },

    getPage(id) {
      console.log(id)
      const url = '/api/proposal/client?page=' + id;

      axios.get(url).then(response => {
        this.items = response.data.data;
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

  },

  mounted() {
    this.getClients();
  }
}
</script>

<style lang="css" scoped>
</style>