<template>
  <form>
    <v-flex xs10>
      <v-select
        autocomplete
        label="С кошелька"
        v-model="form.purse_from"
        placeholder="Выбрать кошелек"
        :items="availablePurses"
        item-text="name"
        item-value="id"
        required
      ></v-select>
    </v-flex>
    <v-flex xs10>
      <v-select
        autocomplete
        label="На кошелек"
        v-model="form.purse_to"
        placeholder="Выбрать кошелек"
        :items="allPurses"
        item-text="name"
        item-value="id"
        required
      ></v-select>
    </v-flex>
    
    <v-flex xs10>
      <v-text-field
        label="Сумма"
        v-model="form.sum"
        required
      ></v-text-field>
    </v-flex>

    <v-btn @click="submit">Создать</v-btn>

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
    submit() {}
  }
}
</script>

<style lang="css" scoped>
</style>