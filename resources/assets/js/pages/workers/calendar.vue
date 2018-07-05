<template>
  <v-container grid-list-md>
    <v-layout wrap>
      <v-dialog v-if="hasPermission('block_stock')" v-model="newBlockDialog" persistent>
        <v-btn color="primary" slot="activator" dark>Новая блокировка</v-btn>
        <v-card>
          <v-card-title>
            <span class="headline">Новая блокировка</span>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-md>
              <v-layout wrap>
                  <new-block-view
                    @new-block="onNewBlockCreated"
                  ></new-block-view>
              </v-layout>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue darken-1" flat @click.native="newBlockDialog = false">Закрыть</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <full-calendar 
        :events="events"
        :config="config"
        :header="header"
        :defaultView="defaultView"
      ></full-calendar>
    </v-layout>
  </v-container>
</template>

<script>
import axios from 'axios'
import {mapGetters} from 'vuex'
import { FullCalendar } from 'vue-full-calendar'
import NewBlock from '~/pages/workers/block'

export default {

  name: 'workers-block-calendar',

  components: {
    'new-block-view': NewBlock,
    FullCalendar,
  },

  computed: mapGetters({
    user: 'authUser'
  }),

  data () {
    return {
      events: [],
      config: {
        locale: 'ru',
      },
      header: {
        prev: '<',
        next: '>',
        today: 'Сегодня'
      },
      defaultView: ['month'],
      newBlockDialog: false,
    }
  },
  methods: {
    getBlocks(){
      const month = Number(new Date().getMonth()) + 1
      const url = '/api/user/departament-block/get-list/' + new Date().getFullYear() + '/' + month
      axios.get(url).then(response => {
        response.data.forEach(e => {
          this.events.push({
            title  : 'Блок',
            start  : e.date,
            allDay : true,
          })
        })
      })
    },

    onNewBlockCreated() {
      this.newBlockDialog = false
      setTimeout(() => {
        this.getBlocks()
      }, 1.5 * 1000)
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
  },
  mounted() {
    this.getBlocks();
  }
}
</script>

<style lang="css">
  @import '~fullcalendar/dist/fullcalendar.css';

  .fc-today-button .fc-button .fc-state-default .fc-corner-left .fc-corner-right {
    display: none;
  }

  .application a {
    color: #fff;
  }

  .fc-unthemed td {
    border-color: #9D9D9D;
  }

  .fc-unthemed td.fc-today {
    background: #666666;
  }
</style>