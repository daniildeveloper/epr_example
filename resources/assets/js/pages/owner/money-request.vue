<template>
  <form>
    <v-text-field
      label="Сумма"
      v-model="form.sum"
      required
    ></v-text-field>

    <v-text-field
      label="Основание"
      v-model="form.argument"
      multi-line
    ></v-text-field>

    <v-btn @click="submit">Создать</v-btn>
  </form>
</template>

<script>
import {Form} from 'vform'

export default {

  name: 'money-request',

  data () {
    return {
      form: new Form({
        sum: 0,
        argument: '',
      })
    }
  },

  methods: {
    async submit() {
      const {data} = await this.form.post('/api/msr/mr/store')
      this.$emit('new-money-request')
    }
  }
}
</script>

<style lang="css" scoped>
</style>