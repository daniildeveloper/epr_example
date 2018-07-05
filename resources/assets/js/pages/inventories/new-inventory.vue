<template>
  <form>
    <v-select
      label="Тип компонента"
      v-model="form.component_type"
      :items="componentTypes"
      item-text="name"
      item-key="table"
      required
    ></v-select>

    <!-- Ожидаемые остатки -->
    <!-- Реальные остатки -->

    <v-select
      v-if="form.component_type.table === 'packagings'"
      label="Компонент"
      v-model="form.component"
      :items="packagings"
      item-text="name"
      item-value="id"
      required
    ></v-select>
    <v-select
      v-if="form.component_type.table === 'stickers'"
      label="Компонент"
      v-model="form.component"
      :items="stickers"
      item-text="name"
      item-value="id"
      required
    ></v-select>
    <v-select
      v-if="form.component_type.table === 'frameworks'"
      label="Компонент"
      v-model="form.component"
      :items="stickers"
      item-text="name"
      item-value="id"
      required
    ></v-select>

    <v-btn @click="submit">Создать</v-btn>
    <v-btn @click="clear">Очистить</v-btn>
  </form>
</template>

<script>
import { validationMixin } from 'vuelidate'
import { required, maxLength, numeric } from 'vuelidate/lib/validators'
import axios from 'axios'
import Form from 'vform'

export default {

  name: 'supply-new',

  mixins: [validationMixin],
  validations: {
    supplier: {required},
    componentType: {required},
    component: {required},
    count: {required, numeric},
    price: {required, numeric},
  },
  data () {
    return {
      form: new Form({
        name: '',
        price: 0.0,
        count: 0,
        component: 0,
        componentType: '',
      }),
      componentTypes: [],
      suppliers: [],
      frameworks: [],
      packagings: [],
      stickers: [],
    }
  },
  methods: {
    submit () {
      this.$v.$touch()
    },
    clear () {
      this.$v.$reset()
    },
    getData() {
      const url = '/api/stock/data/supply'
      axios.get(url).then(result => {
        console.log(result)
        this.componentTypes = result.data.types
        this.frameworks = result.data.frameworks
        this.packagings = result.data.packagings
        this.stickers = result.data.stickers
        this.suppliers = result.data.suppliers
      });
    },
    setComponents(type) {
      this[type].forEach(e => {
        this.components.push(e)
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