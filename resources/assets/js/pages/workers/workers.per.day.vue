<template>
  <div class="workers">
    <user-list :users="users"></user-list>
    <!-- <full-calendar :event-sources="eventSources" :events="events" :config="config" ></full-calendar> -->
  </div>
</template>

<script>
import UserList from './chunks/user.list.vue';

/**
 * Логика работы проста - выбирется пользователь, меняется список в графике для просмотра даты прихода и ухода
 */
export default {

  name: 'workers-per-day',

  components: {
    UserList,
  },
  
  methods: {
    next() {
      this.$refs.calendar.fireMethod('next')
    },
    changeView(view) {
      this.$refs.calendar.fireMethod('changeView', view)
    },
  },

  data () {
    return {
      users: [
        {
          id: 1,
          name: 'Daniil'
        }
      ],
      events: [
        {
            title  : 'event1',
            start  : '2010-01-01',
        },
        {
            title  : 'event2',
            start  : '2010-01-05',
            end    : '2010-01-07',
        },
        {
            title  : 'event3',
            start  : '2010-01-09T12:30:00',
            allDay : false,
        },
      ],
      eventSources: [
        {
          events(start, end, timezone, callback) {
            self.$http.get(`/myFeed`, {timezone: timezone}).then(response => {
              callback(response.data.data)
            })
          },
          color: 'yellow',
          textColor: 'black',
        },
        {
          events(start, end, timezone, callback) {
            self.$http.get(`/anotherFeed`, {timezone: self.timezone}).then(response => {
              callback(response.data.data)
            })
          },
          color: 'red',
        },
      ],
      config: {
        weekends: false,
        drop(...args) {
          //handle drop logic in parent
        },
      },
    }
  }
}
</script>

<style lang="css" scoped>
@import '~fullcalendar/dist/fullcalendar.css';
</style>