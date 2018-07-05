<template>
  <form>
    <v-flex>
      <h5>Запрос средств на покупку химии</h5>
    </v-flex>
    
    <v-flex xs10>
      <v-text-field
        label="Сумма"
        v-model="form.sum"
        required
      ></v-text-field>
    </v-flex>

    <v-flex xs10>
      <v-text-field
        label="Заметки"
        v-model="form.argument"
        required
      ></v-text-field>
    </v-flex>

    <v-btn @click="submit">Отправить</v-btn>

  </form>
</template>

<script>
import Form from 'vform'

export default {

  name: 'transfer',

  data () {
    return {
      availablePurses: [],

      allPurses: [],

      form: new Form({
        purse_from: {},
        purse_to: {},
        sum: 0,
      })
    }
  },

  methods: {
    async submit() {
      const {data} = await this.form.post('/api/purse/request/money-request')
      this.$emit('new-transfer-request')
    }
  }
}
</script>

<style lang="css" scoped>
</style>