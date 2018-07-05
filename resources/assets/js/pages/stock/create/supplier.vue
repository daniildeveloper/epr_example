<template>
  <form>
    <v-text-field
      label="Имя"
      v-model="form.name"
      :error-messages="nameErrors"
      required
    ></v-text-field>

    <v-text-field
      label="Телефон"
      v-model="form.phone"
      required
    ></v-text-field>

    <v-layout row>
      <v-flex xs4>
        <v-subheader>Заметки</v-subheader>
      </v-flex>
      <v-flex xs8>
        <v-text-field
          v-model="form.notes"
          name="notes"
          label="Заметки о поставщике."
          value=""
          multi-line
        ></v-text-field>
      </v-flex>
    </v-layout>
    

    <v-btn @click="submit">Создать</v-btn>
    <v-btn @click="clear">Очистить</v-btn>
  </form>
</template>

<script>
import { validationMixin } from 'vuelidate'
import { required, maxLength, numeric } from 'vuelidate/lib/validators'
import Form from 'vform'

export default {

  name: 'supplier-new',

   mixins: [validationMixin],
    validations: {
      name: { required, maxLength: maxLength(256) },
      phone: { required},
    },
    data () {
      return {
        form: new Form({
          name: '',
          phone: '',
          email: ''
        })
      }
    },
    methods: {
      async submit () {
        this.$v.$touch()
        const {data} = await this.form.post('/api/stock/supplier')
        this.$emit('new-supplier')
      },
      clear () {
        this.$v.$reset()
        this.name = ''
        this.notes = ''
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