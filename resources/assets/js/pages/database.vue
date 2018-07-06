<template>
  <v-tabs fixed centered v-model="selectedTab">
    <v-toolbar>
      <v-text-field
        dark
        label="Поиск по базе заявок"
        prepend-icon="search"
        v-model="searchString"
        @keyup.enter="search"
      ></v-text-field>
      <v-tabs-bar color="transparent" slot="extension" dark>
        <v-tabs-slider color="yellow"></v-tabs-slider>
        <v-tabs-item
          v-if="searching"
          :href="'#search'"
        >
          Результаты поиска
        </v-tabs-item>

        <v-tabs-item
          :href="'#packagings'"
          v-if="hasPermission('show_stock_info')"
        >
          Упаковки
        </v-tabs-item>

        <v-tabs-item
          :href="'#rframeworks'"
          v-if="hasPermission('show_stock_info')"
        >
          Основы
        </v-tabs-item>

        <v-tabs-item
          :href="'#wares'"
          v-if="hasPermission('show_stock_info')"
        >
          Товары
        </v-tabs-item>

        <v-tabs-item
          :href="'#stickers'"
          v-if="hasPermission('show_stock_info')"
        >
          Наклейки
        </v-tabs-item>

        <v-tabs-item
          :href="'#proposals'"
        >
          Заявки
        </v-tabs-item>

        <v-tabs-item
          v-if="hasPermission('finances')"
          :href="'#accountings'"
          >Отчетные периоды</v-tabs-item>


      </v-tabs-bar>
    </v-toolbar>
    <v-tabs-items>

      <v-tabs-content
        :id="'search'"
      >
        <v-card flat>
          <search-view
            :proposals="searchItems.proposals"
            :clients="searchItems.clients"
            :objects="searchItems.objects">
          </search-view>
        </v-card>
      </v-tabs-content>
      
      <!-- packagings -->
      <v-tabs-content
        :id="'packagings'"
        v-if="hasPermission('show_stock_info')"
      >
        <v-card flat>
          <packagings-view></packagings-view>
        </v-card>
      </v-tabs-content>
    <!-- packagings -->

    <!-- rest frameworks -->
      <v-tabs-content
        :id="'rframeworks'"
        v-if="hasPermission('show_stock_info')"
      >
        <v-card flat>
          <rframework-view></rframework-view>
        </v-card>
      </v-tabs-content>
    <!-- end rest frameworks -->

    <!-- wares -->
      <v-tabs-content
        :id="'wares'"
        v-if="hasPermission('show_stock_info')"
      >
        <v-card flat>
          <wares-view></wares-view>
        </v-card>
      </v-tabs-content>
    <!-- end wares -->

    <!-- wares -->
      <v-tabs-content
        :id="'stickers'"
        v-if="hasPermission('show_stock_info')"
      >
        <v-card flat>
          <stickers-view></stickers-view>
        </v-card>
      </v-tabs-content>
    <!-- end wares -->

    <!-- accounting periods -->
    <v-tabs-content :id="'accountings'" v-if="hasPermission('finances')">
      <!-- <history-data-view></history-data-view> -->
    </v-tabs-content>
    <!-- end accounting periods -->

    </v-tabs-items>

    <v-tabs-content
      :id="'proposals'"
    >
      <v-card flat>
        <proposals-list-view></proposals-list-view>
      </v-card>
    </v-tabs-content>
  </v-tabs>
</template>

<script>
import axios from 'axios';
import { mapGetters } from 'vuex'

import Packagings from '~/pages/stock/packaging'
import Frameworks from '~/pages/stock/framework'
import RestFrameworks from '~/pages/stock/rest-framework'
import Wares from '~/pages/stock/ware'
import Stickers from '~/pages/stock/stickers'
import Search from '~/pages/search'
import Proposals from '~/pages/proposal/proposals-list'
import AccountingPeriodsEnd from '~/pages/accounting/history-data'

/**
 * На странице отображаются текущие остатки по складу
 */
export default {

  name: 'stock-dashboard',

  components: {
    'packagings-view': Packagings,
    'frameworks-view': Frameworks,
    'rframework-view': RestFrameworks,
    'wares-view': Wares,
    'stickers-view': Stickers,
    'search-view': Search,
    'proposals-list-view': Proposals,
    'history-data-view': AccountingPeriodsEnd,
  },

  computed: mapGetters({
    user: 'authUser',
    authenticated: 'authCheck'
  }),

  metaInfo () {
    return { title: "База данных" }
  },

  data () {
    return {
      active: null,
      searchString: '',
      searching: false,
      searchItems: {
        proposals: [],
        objects: [],
        clients: []
      },
      selectedTab: 'packagings',
    }
  },

  methods: {
    search() {
      const searchUrl = '/api/search'
      axios.post(searchUrl, {
        search: this.searchString
      }).then(response => {
        console.log(response)
        this.searchItems.proposals = response.data.data.proposals
        this.searching = true
        this.selectedTab = 'search'
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

  }
}
</script>

<style lang="css" scoped>
</style>