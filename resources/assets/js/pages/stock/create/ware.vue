<template>
  <form>
    <v-text-field
      label="Название"
      v-model="form.name"
      required
    ></v-text-field>

    <v-select
      label="Основа"
      v-model="form.framework_id"
      :items="frameworks"
      item-text="name"
      item-value="id"
      required
    ></v-select>

    <v-select
      label="Упаковка"
      v-model="form.packaging_id"
      :items="packagings"
      item-text="name"
      item-value="id"
      required
    ></v-select>

    <v-select
      label="Наклейка"
      v-model="form.sticker_id"
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
import Form from 'vform';
import axios from 'axios'

export default {

  name: 'ware-new',

   mixins: [validationMixin],
    validations: {
      name: { required, maxLength: maxLength(256) },
      framework: null,
      sticker: null,
      packaging: null,
    },
    data () {
      return {
        form: new Form({
          name: '',
          packaging_id: '',
          framework_id: '',
          sticker_id: '',
        }),
        frameworks: [],
        packagings: [],
        stickers: [],
      }
    },
    methods: {
      async submit () {
        const {data} = await this.form.post('/api/stock/ware')
        this.$emit('new-ware')
      },
      clear () {
        this.$v.$reset()
        this.form.name = ''
        this.form.framework_id = null
        this.form.packaging_id = null
        this.form.sticker_id = null
      },
      getData() {
        const url = '/api/stock/data/ware';
        axios.get(url).then(result => {
          this.frameworks = result.data.frameworks
          this.stickers = result.data.stickers
          this.packagings = result.data.packagings
        })
      }
    },
    computed: {
      nameErrors () {
        const errors = []
        if (!this.$v.name.$dirty) return errors
        !this.$v.name.maxLength && errors.push('Максимальная длина названия не более 256 символов')
        !this.$v.name.required && errors.push('Ну как же без названия?')
        return errors
      },
      selectErrors () {
        const errors = []
        if (!this.$v.select.$dirty) return errors
        !this.$v.select.required && errors.push('Поле не должно оставаться пустым')
        return errors
      },
    },
    mounted() {
      this.getData();
    }
}
</script>

<style lang="css" scoped>
</style>