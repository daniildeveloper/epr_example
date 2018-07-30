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
          :error="validationErr != null && typeof validationErr.watcher_id != 'undefined' && validationErr.watcher_id.length > 0"
          :error-messages="validationErr != null && typeof validationErr.watcher_id != 'undefined' ? validationErr.watcher_id : []"
        ></v-select>
        <v-text-field
          label="Плата за 30 дней работы"
          v-model="watch.monthly_payment"
          :error="validationErr != null && validationErr.monthly_payment && validationErr.monthly_payment.length > 0"
          :error-messages="validationErr != null && typeof validationErr.monthly_payment != 'undefined'  ? validationErr.monthly_payment : []"
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
    'validationErr',
  ],

  data () {
    return {
        watch: new Form({
            monthly_payment: null,
            watcher: null,
        }),
    }
  },

  mounted() {},

  methods: {
    save() {
      this.$emit('new-watch-created', {
        watcher_id: this.watch.watcher,
        monthly_payment: this.watch.monthly_payment,
      });
    }
  }
}
</script>

<style lang="css" scoped>
</style>