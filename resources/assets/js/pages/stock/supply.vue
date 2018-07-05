<template>
  <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-btn color="primary" dark @click.stop="newSupplyDialog = true">Новая поставка</v-btn>
          <v-dialog v-model="newSupplyDialog" max-width="500px">
            <v-card>
              <v-card-title>
                Новая поставка
              </v-card-title>
              <v-card-text>
                <new-supply-view></new-supply-view>
              </v-card-text>
            <v-card-actions>
              <v-btn color="primary" flat @click.stop="newSupplyDialog = false">Закрыть</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
      </v-flex>

        <div>
          <v-data-table
            v-bind:headers="headers"
            v-bind:items="items"
            v-bind:search="search"
            v-bind:pagination.sync="pagination"
            hide-actions
            class="elevation-1"
          >
            <template slot="items" slot-scope="props">
              <td>{{ props.item.ware.name }}</td>
              <td class="text-xs-right">{{ props.item.price }}</td>
              <td class="text-xs-right">{{ props.item.count }}</td>
              <td class="text-xs-right">{{ props.item.price_total }}</td>
              <td class="text-xs-right">{{ props.item.supplier.name }}</td>
            </template>
          </v-data-table>
          <div class="text-xs-center pt-2">
            <!--  @next="" @previous="" -->
            <v-pagination v-model="pagination.page" :length="pages"></v-pagination>
          </div>
        </div>
    </v-layout>
  </v-container>
</template>


<script>
import axios from 'axios'
import Form from 'vform'
import NewSupply from '~/pages/stock/create/supply'

/**
 * Страница для отображения всех поставок
 */
export default {

  name: 'sticker',

  components: {
    'new-supply-view': NewSupply,
  },

  computed: {
    pages () {
      return this.pagination.rowsPerPage ? Math.ceil(this.pagination.totalItems / this.pagination.rowsPerPage) : 0
    }
  },

  data () {
      return {
        search: '',
        pagination: {},
        selected: [],
        newSupplyDialog: false,
        headers: [
          {
            text: 'Наименование товара',
            align: 'left',
            value: 'ware.name'
          },
          { text: 'Цена поставки', value: 'price' },
          { text: 'Количество', value: 'count' },
          { text: 'Общее', value: 'price_total' },
          { text: 'Поставщик', value: 'supplier.name' },
        ],
        busy: false,
        items: [],
        updateForm: new Form({
          id: 0,
          name: '',
          price: 0,
          minimal_in_stock: 0,
        }),
        nameRules: [
          (v) => !!v || 'Название обязательно',
          (v) => v && v.length <= 2 || 'В названии не может быть меньше 2-х букв'
        ],
        priceRules: [
          (v) => v && v < 0 || 'Цена не может быть отрицательной. Пройдите школу заново'
        ],
      }
  },

  methods: {
    getSupplies() {
      const url = '/api/stock/supply'
      axios.get(url).then(result => {
          this.items = result.data.data
          this.pagination.rowsPerPage = result.data.per_page
          this.pagination.totalItems = result.data.total
      });
    },

    /**
     * Update framework
     * @param  {[type]} id [description]
     * @return {[type]}    [description]
     */
    async update() {
      const url = 'api/stock/sticker/' + this.updateForm.id
      const {data} = await this.updateForm.put(url)
      this.getStickers()
    },

    getData() {
      this.getStickers()
    },

    selectRow(item, props) {
      props.expanded = !props.expanded

      this.updateForm.id = item.id
      this.updateForm.name = item.name
      this.updateForm.price = item.price
      this.updateForm.minimal_in_stock = item.minimal_in_stock
    }
  },

  mounted() {
    this.getSupplies();
  }
}
</script>

<style lang="css" scoped>
</style>