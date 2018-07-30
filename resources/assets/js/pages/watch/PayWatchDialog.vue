<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="290">
      <v-btn color="primary" dark slot="activator">Выплатить</v-btn>
      <v-card>
        <v-card-text>
            Выплатить деньги за вахту?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="green darken-1" flat @click.native="dialog = false">Отмена</v-btn>
          <v-btn color="green darken-1" flat @click.native="submit()">Выплатить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
import axios from 'axios';

export default {

  name: 'PayWatchDialog',

  props: [
    'watch_id'
  ],

  data () {
    return {
        dialog: false,
    }
  },

  methods: {
    submit() {
        this.$store.dispatch('setLoading', {
            loading: true,
        });

        axios.post('/api/manufactory/watch/pay', {
            watch_id: this.watch_id,
        }).then(response => {
            this.$emit('watch-pay');
            this.$store.dispatch('setLoading', {
                loading: false,
            });
            this.dialog = false;
            this.$store.dispatch('responseMessage', {
                modal: false,
                type: 'success',
                text: 'За вахту уплачено'
            });
        }).catch(err => {
            this.$store.dispatch('responseMessage', {
                modal: false,
                type: 'error',
                text: 'При оплате произошли некоторые проблемы'
            });

            this.$store.dispatch('setLoading', {
                loading: false
            })
        })
    }
  }
}
</script>

<style lang="css" scoped>
</style>