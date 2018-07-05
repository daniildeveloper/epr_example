<template>
  <form>
    <v-flex>
      <h5>Запрос средств</h5>
    </v-flex>
    <v-flex xs10>
      <!-- После создания метода получения все запросов сдесь должен былть автокомплит с инфо о запросе -->
      <v-select
        autocomplete
        label="Выберите запрос"
        v-model="form.request"
        placeholder="Выбрать запрос"
        :items="requests"
        item-text="name"
        item-value="id"
        required
      ></v-select>
    </v-flex>
    <v-flex xs10>
      <v-text-field
        label="Заметки"
        v-model="form.notes"
        required
      ></v-text-field>
    </v-flex>

    <v-btn @click="submit">Отправить</v-btn>
    <v-btn @click="cancel">Отклонить</v-btn>

  </form>
</template>

<script>
import Form from 'vform'

export default {

  name: 'transfer',

  data () {
    return {
      requests: [],

      form: new Form({
        request: '',
        notes: '',
      })
    }
  },

  methods: {
    submit() {
      this.$emit('new-transfer-response-success')
    },
    cancel() {
      this.$emit('new-transfer-response-cancel')
    }
  }
}
</script>

<style lang="css" scoped>
</style>