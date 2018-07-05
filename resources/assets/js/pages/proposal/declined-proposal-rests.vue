<template>
  <v-dialog v-model="problemsDialog" persistent max-width="500px">
    <v-btn color="primary" slot="activator" dark>По товарам</v-btn>
    <v-card>
      <v-card-title>
        <span class="headline">По товарам</span>
        <v-spacer></v-spacer>
        <v-menu bottom left>
          <v-btn @click="problemsDialog = false" icon slot="activator">
            <v-icon>close</v-icon>
          </v-btn>
        </v-menu>
      </v-card-title>
      <v-card-text>
        
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" flat @click.native="problemsDialog = false">Закрыть</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
import axios from 'axios';

/**
 * Component with dialog with all unclosed declined proposals rests
 */
export default {

  name: 'declined-proposal-rests',

  data () {
    return {
      problemsDialog: false,
    }
  },

  methods: {
    getData() {}
  }
}
</script>

<style lang="css" scoped>
</style>