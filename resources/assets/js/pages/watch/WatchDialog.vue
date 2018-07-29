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
          v-model="watch.monthly_payment"
          :rues="nameRules"
          required
        ></v-text-field>
        <submit-button :form="watch" label="Начать вахту"></submit-button>
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
            monthly_payment: 0,
            watcher: null,
        }),
    }
  },

  mounted() {},

  methods: {
    save() {
      console.log(this.watch);

      this.$emit('new-watch-created', {
        watcher_id: this.watch.watcher.id,
        monthly_payment: this.watch.monthly_payment,
      });
    }
  }
}
</script>

<style lang="css" scoped>
</style>