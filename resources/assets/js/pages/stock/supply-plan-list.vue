<template>
  <v-layout row>
    <v-flex xs12 v-if="packagings.length === 0 && stickers.length === 0">Нет новых поставок</v-flex>
    <v-flex xs12 sm6 v-if="packagings.length > 0">
      <v-card>
        <v-toolbar color="indigo" dark>
          <v-toolbar-title>Упаковки</v-toolbar-title>
        </v-toolbar>
        <v-list>
          <v-list-tile avatar v-for="item in packagings" v-bind:key="item.title" @click="">
            <v-list-tile-action>
              <!-- <v-icon v-if="item.icon" color="pink">star</v-icon> -->
            </v-list-tile-action>
            <v-list-tile-content>
              {{ item.packaging.name }} x {{ item.total }}
            </v-list-tile-content>
            <v-list-tile-avatar>
              <v-btn @click="confirmPackagings(item.id)">OK</v-btn>
              <v-btn @click="declinePackagings(item.id)">Отмена</v-btn>
            </v-list-tile-avatar>
          </v-list-tile>
        </v-list>
      </v-card>
    </v-flex>

    <v-flex xs12 sm6 v-if="stickers.length > 0">
      <v-card>
        <v-toolbar color="indigo" dark>
          <v-toolbar-title>Наклейки</v-toolbar-title>
        </v-toolbar>
        <v-list>
          <v-list-tile avatar v-for="item in stickers" v-bind:key="item.title" @click="">
            <v-list-tile-action>
              <!-- <v-icon v-if="item.icon" color="pink">star</v-icon> -->
            </v-list-tile-action>
            <v-list-tile-content>
              {{ item.sticker.name }} x {{ item.total }}
            </v-list-tile-content>
            <v-list-tile-avatar>
              <v-btn @click="confirmStickers(item.id)">OK</v-btn>
              <v-btn @click="declineStickers(item.id)">Отмена</v-btn>
            </v-list-tile-avatar>
          </v-list-tile>
        </v-list>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>
import axios from 'axios';

export default {

  name: 'supply-plan-list',

  data () {
    return {
      packagings: [],
      stickers: [],
    }
  },

  methods: {
    getData() {
      axios.get('/api/crud-nomenclatures/manipulations/supplies-plan')
        .then(response => {
          this.packagings = response.data.packagings;
          this.stickers = response.data.stickers;
        })
    },

    confirmPackagings(id) {
      axios.post('/api/crud-nomenclatures/manipulations/packagingBy', {
        by_id: id
      }).then(response => {
        this.getData()
      })
    },

    confirmStickers(id) {
      axios.post('/api/crud-nomenclatures/manipulations/stickerBy', {
        by_id: id
      }).then(response => {
        this.getData()
      })
    },

    declinePackagings(id) {
      axios.post('/api/crud-nomenclatures/manipulations/packagingDecline', {
        by_id: id
      }).then(response => {
        this.getData();
      })
    },

    declineStickers(id) {
      axios.post('/api/crud-nomenclatures/manipulations/stickerDecline', {
        by_id: id
      }).then(response => {
        this.getData();
      })
    },
  },

  mounted() {
    this.getData();
  }
}
</script>

<style lang="css" scoped>
</style>