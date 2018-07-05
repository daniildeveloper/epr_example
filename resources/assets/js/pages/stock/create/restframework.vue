<template>
  <form>
    <v-text-field
      label="Название"
      v-model="form.name"
      :error-messages="nameErrors"
      @input="$v.name.$touch()"
      @blur="$v.name.$touch()"
      required
    ></v-text-field>

    <v-text-field
      label="Цена"
      v-model="form.price"
      required
    ></v-text-field>

    <v-text-field
      label="Минимум на складе"
      v-model="form.minimal_in_stock"
      required
    ></v-text-field>

    <v-btn @click="submit">Создать</v-btn>
    <v-btn @click="clear">Очистить</v-btn>
  </form>
</template>

<script>
import { validationMixin } from 'vuelidate'
import { required, maxLength, numeric } from 'vuelidate/lib/validators'
import { Form } from 'vform'

export default {

  name: 'framework-new',

   mixins: [validationMixin],
    validations: {
      name: { required, maxLength: maxLength(256) },
      price: { required, numeric },
    },
    data () {
      return {
        newRestFrameworkDialog: false,
        form: new Form({
          name: '',
          price: 0.0,
          minimal_in_stock: 10,
        }),
      }
    },
    methods: {
      async submit () {
        this.$v.$touch()
        const {data} = await this.form.post('/api/stock/rest-framework')
        this.$emit('new-restframework')
      },
      clear () {
        this.$v.$reset()
        this.form.name = ''
        this.form.price = 0
        this.form.minimal_in_stock = 0
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
    }
}
</script>

<style lang="css" scoped>
</style>