<template>
  <form>
    <v-text-field
      label="Название"
      v-model="form.name"
      required
    ></v-text-field>

    <v-text-field
      label="Заметки"
      v-model="form.notes"
      multi-line
    ></v-text-field>

    <v-text-field
      label="Откладываемый процент с прибыли"
      v-model="form.profit_procent"
    ></v-text-field>

    <v-text-field
      label="Цель"
      v-model="form.target"
    ></v-text-field>

    <v-btn @click="submit">Создать</v-btn>
  </form>
</template>

<script>
import Form from 'vform'

export default {

  name: 'new-purse',

  data () {
    return {
      form: new Form({
        name: '',
        notes: '',
        profit_procent: 0,
        target: 0,
      })
    }
  },

  methods: {
    async submit() {
      const url = '/api/purse/store';

      const {data} = await this.form.post(url)
      this.$emit('new-purse-created')
    }
  }
}
</script>

<style lang="css" scoped>
</style>