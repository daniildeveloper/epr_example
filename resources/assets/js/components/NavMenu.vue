<template>
  <div>
    <v-toolbar flat dark>
      <v-list>
        <v-list-tile>
          <v-list-tile-title class="title">
            {{ name }}
          </v-list-tile-title>
        </v-list-tile>
      </v-list>
    </v-toolbar>
    <v-divider></v-divider>
    <v-list>
      <v-list-tile
        value="true"
        v-for="(item, i) in items"
        :key="i"
        :to="item.route"
      >
        <v-list-tile-action>
          <v-icon light v-html="item.icon"></v-icon>
        </v-list-tile-action>
        <v-list-tile-content>
          <v-list-tile-title v-text="item.title"></v-list-tile-title>
        </v-list-tile-content>
      </v-list-tile>
      
      <!-- finances -->
      <v-list-tile
        v-if="hasPermission('finances')"
        value="true"
        :to="{ name: 'finances' }"
      >
        <v-list-tile-action>
          <v-icon light v-html="'dashboard'"></v-icon>
        </v-list-tile-action>
        <v-list-tile-content>
          <v-list-tile-title v-text="'Финансы'"></v-list-tile-title>
        </v-list-tile-content>
      </v-list-tile>
      <!-- end finances -->

      <!-- actions archive -->
      <v-list-tile
        v-if="hasPermission('actions_archive')"
        value="true"
        :to="{ name: 'archive.list' }"
      >
        <v-list-tile-action>
          <v-icon light v-html="'dashboard'"></v-icon>
        </v-list-tile-action>
        <v-list-tile-content>
          <v-list-tile-title v-text="'Архив действий'"></v-list-tile-title>
        </v-list-tile-content>
      </v-list-tile>
      <!-- end actions archive -->

      <!-- transations -->
      <v-list-tile
        v-if="hasPermission('actions_archive')"
        value="true"
        :to="{ name: 'transactions' }"
      >
        <v-list-tile-action>
          <v-icon light v-html="'dashboard'"></v-icon>
        </v-list-tile-action>
        <v-list-tile-content>
          <v-list-tile-title v-text="'Транзакции'"></v-list-tile-title>
        </v-list-tile-content>
      </v-list-tile>
      <!-- end transactions -->
    </v-list>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  data () {
    return {
      name: this.$t('nav_menu_title'),
      items: [
        { title: 'Главная', icon: 'dashboard', route: { name: 'home' } },
        { title: 'База данных', icon: 'account_box', route: { name: 'database' } },
        { title: 'Мой аккаунт', icon: 'account_box', route: { name: 'settings.profile' } },
      ]
    }
  },

  computed:{
    ...mapGetters({
      user: 'authUser',
      authenticated: 'authCheck'
    }),
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
