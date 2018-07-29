<template>
    <form @submit.prevent="save">
      <v-card-text>
        <v-text-field
          label="Плата за 30 дней работы"
          v-model="watch.montly_payment"
          :rues="nameRules"
          required
        ></v-text-field>
      </v-card-text>

      <v-card-text>
        <submit-button :block="true" :form="watchForm" label="Обновить"></submit-button>
      </v-card-text>

    </form>
</template>

<script>
import Form from 'vform';
import axios from 'axios';

export default {

  name: 'WatchDialog',

  data () {
    return {
        watch: new Form({
            montly_payment: 0,
        }),
    }
  },

  mounted() {},

  methods: {
    getData() {
      axios.get('/')
    }
  }
}
</script>

<style lang="css" scoped>
</style>