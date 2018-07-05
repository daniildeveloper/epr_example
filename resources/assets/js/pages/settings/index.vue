<template>
  <v-layout row>
    <v-flex xs12 sm8 offset-sm2 lg6 offset-lg3>
      <v-card>
        <progress-bar :show="busy"></progress-bar>
        <v-card-title primary-title>
          <h3 class="headline mb-0">{{ $t('settings') }}</h3>
        </v-card-title>
        <v-tabs icons centered fixed>
          <v-tabs-bar>
            <v-tabs-slider color="primary"></v-tabs-slider>
            <v-tabs-item href="#tab-person">
              <v-icon>person</v-icon>
              {{ $t('profile') }}
            </v-tabs-item>
            <v-tabs-item href="#tab-password">
              <v-icon>lock</v-icon>
              {{ $t('password') }}
            </v-tabs-item>
            <v-tabs-item href="#tab-mark" v-if="userIsWorker">
              <v-icon>check-all</v-icon>
              Отметиться
            </v-tabs-item>
            <!-- <v-tabs-item href="#tab-task-list">
              <v-icon>check</v-icon>
              Задачи
            </v-tabs-item> -->
          </v-tabs-bar>
          <v-divider></v-divider>

          <v-tabs-items>
            <v-tabs-content id="tab-person">
              <profile-view v-on:busy="busy = $event"></profile-view>
            </v-tabs-content>
            <v-tabs-content id="tab-password">
              <password-view v-on:busy="busy = $event"></password-view>
            </v-tabs-content>
            <v-tabs-content id="tab-mark">
              <mark-view></mark-view>
            </v-tabs-content>
            <!-- <v-tabs-content id="tab-task-list">
              <task-list-view></task-list-view>
            </v-tabs-content> -->
          </v-tabs-items>
        </v-tabs>
      </v-card>
    </v-flex>
  </v-layout>
</template>

<script>
import Profile from '~/pages/settings/profile'
import Password from '~/pages/settings/password'
import Mark from '~/pages/workers/mark'
// import TaskList from '~/pages/users/task/tasklist'

export default {
  name: 'settings-view',
  components: {
    'profile-view': Profile,
    'password-view': Password,
    'mark-view': Mark,
    // 'task-list-view': TaskList,
  },
  data () {
    return {
      busy: false,
      userIsWorker: false,
    }
  }
}
</script>
