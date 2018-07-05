<template>
  <form>
    <v-select
      autocomplete
      label="Поставщик"
      v-model="form.supplier_id"
      :items="suppliers"
      placeholder="Выбрать поставщика по имени"
      required
      :loading="loadingSupplierSearch"
      cache-items
      required
      :search-input.sync="searchSupplier"
      item-text="name"
      item-value="id"
    ></v-select>

    <v-btn flat
      v-if="form.supplier === ''"
      @click="newSupplierDialog = true">
      Новый поставщик
    </v-btn>
    <v-dialog v-model="newSupplierDialog" max-width="500px">
      <v-card>
        <v-card-title>
          Новый поставщик
        </v-card-title>
        <v-card-text>
          <new-supplier-view @new-supplier="onCreate"></new-supplier-view>
        </v-card-text>
      <v-card-actions>
        <v-btn color="primary" flat @click.stop="newSupplierDialog=false">Закрыть</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-select
      label="Тип компонента"
      v-model="form.ware_table"
      :items="ware_tables"
      item-text="name"
      item-value="table"
      required
    ></v-select>

    <v-select
      v-if="form.ware_table === 'packagings'"
      label="Компонент"
      v-model="form.ware_id"
      :items="packagings"
      item-text="name"
      item-value="id"
      required
    ></v-select>
    <v-select
      v-if="form.ware_table === 'frameworks'"
      label="Компонент"
      v-model="form.ware_id"
      :items="frameworks"
      item-text="name"
      item-value="id"
      required
    ></v-select>
    <v-select
      v-if="form.ware_table === 'stickers'"
      label="Компонент"
      v-model="form.ware_id"
      :items="stickers"
      item-text="name"
      item-value="id"
      required
    ></v-select>

    <v-text-field
      label="Количество"
      v-model="form.count"
      required
    ></v-text-field> 

    <v-text-field
      label="Цена за единицу"
      v-model="form.price"
      required
    ></v-text-field>

    <v-btn @click="submit">Создать</v-btn>
    <v-btn @click="clear">Очистить</v-btn>
  </form>
</template>

<script>
import { validationMixin } from 'vuelidate'
import { required, maxLength, numeric } from 'vuelidate/lib/validators'
import axios from 'axios'
import Form from 'vform'
import Supplier from '~/pages/stock/create/supplier'

export default {

  name: 'supply-new',

  components: {
    'new-supplier-view': Supplier,
  },

  mixins: [validationMixin],
  validations: {
    supplier: {required},
    ware_table: {required},
    ware_id: {required},
    count: {required, numeric},
    price: {required, numeric},
  },
  data () {
    return {
      form: new Form({
        supplier: '',
        price: 0.0,
        count: 0,
        ware_id: 0,
        ware_table: '',
      }),
      ware_tables: [],
      suppliers: [],
      frameworks: [],
      packagings: [],
      stickers: [],

      newSupplierDialog: false,
      loadingSupplierSearch: false,
      searchSupplier: '',

    }
  },
  methods: {
    async submit () {
      this.$v.$touch()
      this.form.price_total = this.form.price * this.form.count
      const {data} = await this.form.post('/api/stock/supply')
      this.$emit('new-supply')

    },
    clear () {
      this.$v.$reset()
    },
    getData() {
      const url = '/api/stock/data/supply'
      axios.get(url).then(result => {
        console.log(result)
        this.ware_tables = result.data.types
        this.frameworks = result.data.frameworks
        this.packagings = result.data.packagings
        this.stickers = result.data.stickers
        this.suppliers = result.data.suppliers
      });
    },
    setComponents(type) {
      this[type].forEach(e => {
        this.ware_ids.push(e)
      })
    },
    onCreate() {
      this.newSupplierDialog = false;
    },
    supplierSearchMethod(v) {
      this.loadingClientSearch = true;

      const url = '/api/stock-data/supplier/search?query=' + v

      axios.get(url).then(result => {
        this.suppliers = result.data
        this.loadingSupplierSearch = false
      });
    },
  },
  mounted() {
    this.getData();
  },
  watch: {
    searchSupplier(val) {
      this.supplierSearchMethod(val)
    }
  }

}
</script>

<style lang="css" scoped>
</style>