<template>
  <v-list three-line subheader>
    <h3 class="headline mb-0">&nbsp;&nbsp;&nbsp;Запросы на покупку химии</h3>
    <v-list-tile v-for="r in requests" :key="r.id" :data="r">
      <v-list-tile-content>
        <v-list-tile-title>{{ r.sum }} тг</v-list-tile-title>
        <v-list-tile-sub-title v-if="r.argument != null">{{ r.argument }}</v-list-tile-sub-title>
      </v-list-tile-content>
      <v-list-tile-action>
        <v-layout row v-if="hasPermission('money_send')">
          <v-flex>
            <v-btn color="primary" @click="sendMoney(r.id)">Отправить деньги</v-btn>
          </v-flex>
          <v-flex>
            <v-btn @click="cancelRequest(r.id)">Отклонить запрос</v-btn>
          </v-flex>
        </v-layout>
      </v-list-tile-action>
    </v-list-tile>
  </v-list>
</template>

<script>
import axios from 'axios'
import { mapGetters } from 'vuex'

export default {

  name: 'new-money-requets',

  computed: mapGetters({
    user: 'authUser'
  }),

  data () {
    return {
      requests: [],
    }
  },

  methods: {
    getData() {
      axios.get('/api/purse/request/unconfirmed')
        .then(response => {
          this.requests = response.data
        })
    },

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

    sendMoney(id) {
      const url = '/api/purse/request/money-request-reponse/' + id
      axios.post(url, {
        success: true
      }).then(response => {
        this.getData()
      })
    },

    cancelRequest(id) {
      const url = '/api/purse/request/money-request-reponse/' + id
      axios.post(url, {
        success: false
      }).then(response => {
        this.getData()
      })
    }
  },

  mounted() {
    this.getData()
  }
}
</script>

<style lang="css" scoped>
</style>