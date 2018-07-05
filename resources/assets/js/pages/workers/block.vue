<template>
  <v-card>
    <span class="headline">Выберите дни когда производство работать не будет</span>
    <v-container fill-height fluid>
      <v-layout fill-height>
        <v-flex xs12 align-end flexbox>
          <date-picker :date="date" :time.sync="startTime.time" :option="option"></date-picker>
          <v-btn @click="submit">Подтвердить</v-btn>
        </v-flex>
      </v-layout>
    </v-container>
  </v-card>
</template>

<script>
import myDatepicker from 'vue-datepicker/vue-datepicker-es6';
import axios from 'axios';
/**
 * Select days
 * confirm
 */
export default {

  name: 'block-workers',

  components: {
    'date-picker': myDatepicker
  },

  data () {
    return {
      // for Vue 2.0 
      startTime: {
        time: ''
      },
      endtime: {
        time: ''
      },

      date: {
        time: '' // string 
      },
 
      option: {
        type: 'multi-day',
        week: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        month: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        format: 'YYYY-MM-DD',
        placeholder: 'Дата блокировки',
        inputStyle: {
          'display': 'inline-block',
          'padding': '6px',
          'line-height': '22px',
          'font-size': '16px',
          'border': '2px solid #fff',
          'box-shadow': '0 1px 3px 0 rgba(0, 0, 0, 0.2)',
          'border-radius': '2px',
          'color': '#5F5F5F'
        },
        color: {
          header: '#212121',
          headerText: '#fff'
        },
        buttons: {
          ok: 'Подтвердить',
          cancel: 'Отмена'
        },
        overlayOpacity: 0.5, // 0.5 as default 
        dismissible: true // as true as default 
      },
      limit: [{
        type: 'weekday',
        available: [1, 2, 3, 4, 5]
      },
      {
        type: 'fromto',
        from: '2016-02-01',
        to: '2016-02-20'
      }]
    }
  },
  methods: {
    async submit() {
      const items = JSON.parse(this.date.time)
      items.forEach(i => {
        axios.post('/api/user/departament-block', {
          date: i
        }).then(result => {});
      })
      this.$emit('new-block')
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Теперь на выбранные дни невозможно создавать заявки',
        modal: false
      })
    }
  }
}
</script>

<style lang="css" >
.cov-date-box {
  background: #303030 !important;
  color: #fff;
}
</style>