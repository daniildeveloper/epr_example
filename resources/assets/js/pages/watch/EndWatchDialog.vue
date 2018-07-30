<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="290">
      <v-btn color="primary" dark slot="activator">Закончить вахту</v-btn>
      <v-card>
        <v-card-text>
            действительно закончить вахту?
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="green darken-1" flat @click.native="dialog = false">Отмена</v-btn>
          <v-btn color="green darken-1" flat @click.native="submit()">Закончить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
import xios from 'axios';

export default {

  name: 'EndWatchDialog',

  props: [
    'watcher_name',
    'watch_id'
  ],

  data () {
    return {

    }
  },

  methods: {
    submit() {
        this.$store.dispatch('setLoading', {
            loading: true,
        });
        axios.post('/api/manufactory/watch/end', {
            watch_id: this.watch_id,
        }).then(response=>{
            this.$store.dispatch('responseMessage', {
                modal: false,
                type: 'success',
                text: 'Вахта закончена'
            });

            this.$emit('watch-end');

            this.$store.dispatch('setLoading', {
                loading: false,
            });

            this.dialog = false;
        }).catch(err => {
            this.$store.dispatch('setLoading', {
                loading: false
            });

            this.$store.dispatch('responseMessage', {
                modal: false,
                type: 'error',
                text: 'При завершении произошла ошибка'
            });
        })
    }
  }
}
</script>

<style lang="css" scoped>
</style>