<template>
    <form @submit.prevent="save">
      <v-card-text>
        <v-select
          autocomplete
          label="Выбор рабочего"
          v-model="watch.watcher"
          placeholder="Выбрать рабочего"
          :items="watchers"
          item-text="name"
          item-value="id"
          required
        ></v-select>
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

  props: [
    'watchers',
  ],

  data () {
    return {
        watch: new Form({
            montly_payment: 0,
            watcher: null,
        }),
    }
  },

  mounted() {},

  methods: {
    save() {
      console.log(this.watch);

      this.$emit('new-watch-created');
    }
  }
}
</script>

<style lang="css" scoped>
</style>