<template>
  <v-layout row justify-center>
    <v-btn color="primary" dark @click.native.stop="dialog = true">Добавить денег</v-btn>
    <v-dialog v-model="dialog" max-width="290">
      <v-card>
        <v-card-title class="headline">Добавить денег за вахту {{watcher_name}}?</v-card-title>
        <v-card-text>
            <v-text-field
              label="Сумма"
              v-model="sum"
            ></v-text-field>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="green darken-1" flat="flat" @click.native="close()">Закрыть</v-btn>
          <v-btn color="green darken-1" flat="flat" @click.native="save()">Добавить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
import axios from 'axios';

export default {

  name: 'AddMoneyDialog',
  props: [
    'watcher_name',
    'watch_id',
  ],

  data () {
    return {
        dialog: false,
        sum: '',
    }
  },

  methods: {
    save() {
      this.$store.dispatch('setLoading', {
        loading: true
      });

      axios.post('/api/manufactory/watch/add-money', {
        watch_id: this.watch_id,
        sum: this.sum,
      }).then(response => {
        this.$store.dispatch('setLoading', {
            loading: false
          });
        this.dialog = false;
        this.$store.dispatch('responseMessage', {
            modal: false,
            type: 'success',
            text: 'Деньги за вахту добвлены'
        })
      }).catch(err => {
        this.$store.dispatch('setLoading', {
            loading: false
          });
        this.$store.dispatch('responseMessage', {
            modal: false,
            type: 'error',
            text: 'При добавлении возникли проблемы'
        })
      })
    },
    close() {
        this.dialog = false;
    }
  }
}
</script>

<style lang="css" scoped>
</style>