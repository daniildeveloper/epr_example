<template>
  <form>
    <v-text-field
      label="Название"
      v-model="form.name"
      required
    ></v-text-field>

    <v-select
      label="Основа инвентарная"
      v-model="form.real_id"
      :items="frameworks"
      item-text="name"
      item-value="id"
      required
    ></v-select>

    <v-select
      label="Основа для остатков"
      v-model="form.rest_id"
      :items="rest_frameworks"
      item-text="name"
      item-value="id"
      required
    ></v-select>

    <v-btn @click="submit">Создать</v-btn>
  </form>
</template>

<script>
import Form from 'vform'
import axios from 'axios'

export default {

  name: 'new-rest-to-real',

  data () {
    return {
      form: new Form({
        real_id: 0,
        rest_id: 0
      }),
      frameworks: [],
      rest_frameworks: [],
    }
  },

  methods: {
    async submit() {
      const {data} = await this.form.post('/api/rest-to-real')
      this.$emit('new-rest-to-real')
    },

    getData() {
      const url = '/api/rest-to-real/list';
      axios.get(url).then(res => {
        this.frameworks = res.data.data.frameworks
        this.rest_frameworks = res.data.data.rest_frameworks
      })
    }
  }

}
</script>

<style lang="css" scoped>
</style>