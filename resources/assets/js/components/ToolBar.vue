<template>
  <v-toolbar fixed app>
    <v-toolbar-side-icon @click.stop="toggleDrawer" v-if="authenticated"></v-toolbar-side-icon>
    <v-toolbar-title>
      <router-link :to="{ name: 'home' }" class="white--text">
        {{ appName }}
      </router-link>
    </v-toolbar-title>
    <v-toolbar-title>
    </v-toolbar-title>
    <v-spacer></v-spacer>

    <!-- Authenticated -->
    <template v-if="authenticated">
      <v-btn fab dark fixed right floating bottom tob color="indigo" @click="newProposalDialog = true">
        <v-icon dark>add</v-icon>
      </v-btn>
      <progress-bar :show="busy"></progress-bar>

      <chat-view></chat-view>

      <v-dialog v-model="newProposalDialog" persistent>
      <!-- 
        <v-btn color="primary" slot="activator" dark>Новая заявка</v-btn> -->
        <v-card>
          <v-card-title>
            <span class="headline">Новая заявка</span>
            <v-spacer></v-spacer>
            <v-menu bottom left>
              <v-btn @click="newProposalDialog = false" icon slot="activator">
                <v-icon>close</v-icon>
              </v-btn>
            </v-menu>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-md>
              <v-layout wrap>
                  <new-proposal-view @new-proposal="proposalCreated" ></new-proposal-view>
              </v-layout>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue darken-1" flat @click.native="newProposalDialog = false">Закрыть</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>

      <!-- stock button -->
      <v-btn class="hidden-sm-and-down" :to="{ name: 'database' }" color="success" dark>База данных</v-btn>

      <v-btn class="hidden-sm-and-down" v-if="hasPermission('actions_archive')" :to="{name: 'archive.list'}" color="default" dark>Архив действий</v-btn>

      <v-btn class="hidden-sm-and-down" v-if="hasPermission('actions_archive')" :to="{name: 'transactions'}" color="default" dark>Транзакции</v-btn>

      <!-- quick actions bottom sheet -->
      <v-bottom-sheet v-model="sheet" v-if="hasPermission('invite_users')">
        
        <v-btn slot="activator" color="info" dark>Действия</v-btn>
        <v-list>
          <v-subheader>Выберите действие</v-subheader>

          <!-- <v-list-tile :to="{name: 'todo.list'}" >
            <v-list-tile-avatar tile flat>
              <i class="material-icons">check_circle</i>
            </v-list-tile-avatar>
            <v-list-tile-title>
              Список задач
            </v-list-tile-title>
          </v-list-tile> -->

          <v-list-tile :to="{name: 'new.purse'}" >
            <v-list-tile-avatar tile flat>
              <i class="material-icons">check_circle</i>
            </v-list-tile-avatar>
            <v-list-tile-title>
              Создать кошелек
            </v-list-tile-title>
          </v-list-tile>

          <v-list-tile :to="{name: 'archive.list'}" >
            <v-list-tile-avatar tile flat>
              <i class="material-icons">check_circle</i>
            </v-list-tile-avatar>
            <v-list-tile-title>
              Архив
            </v-list-tile-title>
          </v-list-tile>

          <v-list-tile :to="{name: 'proposal.new'}" >
            <v-list-tile-avatar tile flat>
              <i class="material-icons">check_circle</i>
            </v-list-tile-avatar>
            <v-list-tile-title>
              Новая заявка
            </v-list-tile-title>
          </v-list-tile>

          <v-list-tile :to="{name: 'user.table'}" >
            <v-list-tile-avatar tile flat>
              <i class="material-icons">check_circle</i>
            </v-list-tile-avatar>
            <v-list-tile-title>
              Таблица пользователей
            </v-list-tile-title>
          </v-list-tile>

          <v-list-tile :to="{name: 'client.new'}" >
            <v-list-tile-avatar tile flat>
              <i class="material-icons">check_circle</i>
            </v-list-tile-avatar>
            <v-list-tile-title>
              Новый клиент
            </v-list-tile-title>
          </v-list-tile>

          <v-list-tile :to="{name: 'object.new'}" >
            <v-list-tile-avatar tile flat>
              <i class="material-icons">check_circle</i>
            </v-list-tile-avatar>
            <v-list-tile-title>
              Новый объект 
            </v-list-tile-title>
          </v-list-tile>
        </v-list>
      </v-bottom-sheet>
      <!-- end quick actions bottom sheet -->


      <v-btn class="hidden-sm-and-down" v-if="hasPermission('finances')" flat :to="{name:'finances'}" >Финансы</v-btn>
      <v-btn flat :to="{ name: 'settings.profile' }">{{ user.data.name }}</v-btn>
      <v-btn flat @click.prevent="logout">{{ $t('logout') }}</v-btn>
    </template>

    <!-- Guest -->
    <template v-else>
      <v-btn flat :to="{ name: 'login' }">{{ $t('login') }}</v-btn>
    </template>
    <OfflineIndicator message="Отсутствует подключение к сети"></OfflineIndicator>
  </v-toolbar>
</template>

<script>
import { mapGetters } from 'vuex'
import {OfflineIndicator, VueOnline} from 'vue-online'
import NewProposal from '~/pages/proposal/new-proposal';
import Chat from '~/pages/chat';

export default {
  props: {
    drawer: {
      type: Boolean,
      required: true
    }
  },

  components: {
    OfflineIndicator,
    'new-proposal-view': NewProposal,
    'chat-view': Chat,
  },

  data: () => ({
    appName: window.config.appName,
    busy: false,
    sheet: false, // is bottom sheet
    dialog: false,
    newProposalDialog: false,
  }),

  computed:{
    online() {
      return window.network_status;
    },
    ...mapGetters({
      user: 'authUser',
      authenticated: 'authCheck'
    }),
  },

  methods: {
    toggleDrawer () {
      this.$emit('toggleDrawer')
    },
    async logout () {
      this.busy = true

      if (this.drawer) {
        this.toggleDrawer()
      }

      // Log out the user.
      await this.$store.dispatch('logout')
      this.busy = false

      // Redirect to login.
      this.$router.push({ name: 'login' })
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

    proposalCreated(){
      this.newProposalDialog = false;
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Заявка успешно создана',
        modal: false
      })
    },
  },

  mounted() {
    
  }
}
</script>

<style lang="stylus" scoped>

.toolbar__title .router-link-active
  text-decoration: none

</style>
