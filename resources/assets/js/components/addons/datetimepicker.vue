<template>
  <v-menu
        lazy
        :close-on-content-click="false"
        v-model="menu"
        transition="v-scale-transition"
        offset-y
        :nudge-left="40">
    <v-text-field
            slot="activator"
            :label="label"
            v-model="actualDatetime"
            readonly
    ></v-text-field>
    <v-tabs ref="tabs" :value="selectedTab" grow icons light>
        <v-tabs-bar slot="activators">
            <v-tabs-item href="#calendar">
                <v-icon>event</v-icon>
            </v-tabs-item>
            <v-tabs-item href="#timer">
                <v-icon>access_time</v-icon>
            </v-tabs-item>
        </v-tabs-bar>
        <v-tabs-content
            id="calendar">
            <v-date-picker
                    v-model="dateModel"
                    no-title
                    scrollable
                    actions
                    @input="checkHours"
            ></v-date-picker>
        </v-tabs-content>
        <v-tabs-content
            id="timer">
            <v-time-picker
                    ref="timer"
                    v-model="timeModel"
                    no-title
                    scrollable
                    format="24hr"
                    actions
                    @input="checkMinutes"
            ></v-time-picker>
        </v-tabs-content>
    </v-tabs>
  </v-menu>
</template>

<script>
/**
 * Dataetimepicker module. Относится к конкретно этому проекту
 * Скопировано: https://codepen.io/LeonardoSalvucci/pen/ZyrPJp?editors=1010
 */
export default {

  name: 'datetimepicker',

  props: {
      datetime: {
          type:String,
          required:true,
      },
      label: {
          type:String,
          default:''
      }
  },

  data(){
      return {
          dateModel: '',
          timeModel: '',
          menu:false,
          selectedTab:"calendar"
      }
  },
  watch: {
      menu(val) {
          if (val) {
              this.selectedTab="calendar"
              if(this.$refs.timer)
                  this.$refs.timer.selectingHour=true
          }
      }
  },
  computed: {
      actualDatetime() {
          return this.dateModel+' '+this.timeModel+':00'
      }
  },
  methods: {
      checkMinutes(val) {
          if(this.$refs.timer.selectingHour===false) {
              this.timeModel=val
              this.$refs.timer.selectingHour = true
              this.selectedTab="calendar"
              this.menu=false
              this.$emit('input',this.actualDatetime)
          }
      },
      checkHours(val) {
          this.dateModel=val
          this.selectedTab="timer"
      }
  },
  created(){
      this.dateModel = this.datetime.split(' ')[0]
      this.timeModel = this.datetime.split(' ')[1]
  }
}
</script>

<style lang="css" scoped>
</style>