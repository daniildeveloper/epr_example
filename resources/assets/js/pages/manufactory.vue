<template>
    <div>
        <!-- first row wirh buttons -->
        <v-layout row justify-start wrap>
            <v-flex>
                <v-dialog v-model="dialog" fullscreen transition="dialog-bottom-transition" :overlay="false">
                  <v-btn color="primary" dark slot="activator">Вахты</v-btn>
                  <v-card>
                    <v-toolbar dark color="primary">
                      <v-btn icon @click.native="watchDialog = false" dark>
                        <v-icon>close</v-icon>
                      </v-btn>
                      <v-toolbar-title>Вахты</v-toolbar-title>
                      <v-spacer></v-spacer>
                      <v-toolbar-items>
                      </v-toolbar-items>
                    </v-toolbar>
                    <!-- here must goes watches -->
                  </v-card>
                </v-dialog>
            </v-flex>
        </v-layout>
        <!-- end first row with buttons -->
        <inventories/>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
import axios from 'axios';

import Inventories from '~/stock/inventory/inventories-list';

/**
 * Component to show all pages
 */
export default {

  name: 'manufactory',

  computed: mapGetters({
    user: 'authUser'
  }),

  components: {
    Inventories,
  },

  metaInfo () {
    return { title: 'Цех' }
  },

  data () {
    return {
        watchDialog: false,
    }
  },

  methods: {
    hasPermission(permission) {
      let permissionsArray = [];
      this.user.data.permissions.forEach(p => {
        permissionsArray.push(p.name);
      });

      if (permissionsArray.indexOf(permission) !== -1) {
        return true;
      }
      return false
    },
  }
}
</script>

<style lang="css" scoped>
</style>