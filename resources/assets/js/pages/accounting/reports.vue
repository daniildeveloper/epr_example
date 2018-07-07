<template>
  <v-layout row wrap>
    <v-flex xs11>
      <h4 class="headline">Выгрузка отчетов</h4>
    </v-flex>
    <v-flex xs11 sm5>
      <v-menu
        lazy
        :close-on-content-click="false"
        v-model="menu"
        transition="scale-transition"
        offset-y
        full-width
        :nudge-right="40"
        max-width="290px"
        min-width="290px"
      >
        <v-text-field
          slot="activator"
          label="Дата начала"
          v-model="dateFormattedBegin"
          locale="ru"
          prepend-icon="event"
          @blur="dateBegin = parseDate(dateFormattedBegin)"
        ></v-text-field>
        <v-date-picker v-model="dateBegin" @input="dateFormattedBegin = formatDate($event)" no-title scrollable actions>
          <template slot-scope="{ save, cancel }">
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn flat color="primary" @click="cancel">Отмена</v-btn>
              <v-btn flat color="primary" @click="save">OK</v-btn>
            </v-card-actions>
          </template>
        </v-date-picker>
      </v-menu>
    </v-flex>

    <!-- dateEnd -->
    <v-flex xs11 sm5>
      <v-menu
        lazy
        :close-on-content-click="false"
        v-model="menuEnd"
        transition="scale-transition"
        offset-y
        full-width
        :nudge-right="40"
        max-width="290px"
        min-width="290px"
      >
        <v-text-field
          slot="activator"
          label="Дата конца"
          v-model="dateFormattedEnd"
          locale="ru"
          prepend-icon="event"
          @blur="dateEnd = parseDate(dateFormattedEnd)"
        ></v-text-field>
        <v-date-picker v-model="dateEnd" @input="dateFormattedEnd = formatDate($event)" no-title scrollable actions>
          <template slot-scope="{ save, cancel }">
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn flat color="primary" @click="cancel">Отмена</v-btn>
              <v-btn flat color="primary" @click="save">OK</v-btn>
            </v-card-actions>
          </template>
        </v-date-picker>
      </v-menu>
    </v-flex>
    <!-- dateEnd end -->

    <!-- submit -->
    <v-flex xs12>
      <v-btn @click="submit">Выгрузить по транзакциям</v-btn>
      <v-btn @click="submitWaresReport">Выгрузить по товарам</v-btn>
      <v-btn @click="submitChemieReport">Выгрузить по продажам</v-btn>
    </v-flex>
    <!-- end submit -->
  </v-layout>
</template>

<script>
import axios from 'axios';

export default {

  name: 'reports',

  data () {
    return {
      dateBegin: null,
      dateFormattedBegin: null,
      menu: false,

      dateEnd: null,
      dateFormattedEnd: null,
      menuEnd: false,
    }
  },
  methods: {
    formatDate (date) {
      if (!date) {
        return null
      }
      const [year, month, day] = date.split('-')
      return `${month}/${day}/${year}`
    },
    parseDate (date) {
      if (!date) {
        return null
      }

      const [month, day, year] = date.split('/')
      return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`
    },
    submit() {
      this.$store.dispatch('setLoading', {
        loading: true,
      })
      const url = '/api/reports/finances?period_begin=' + this.dateBegin + '&period_end=' + this.dateEnd;
      axios({
        url: url,
        method: "GET",
      })
      .then(response => {
        console.log(response)
        this.$emit('new-report-created')
        const url = window.URL.createObjectURL(new Blob([response.data.file]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', response.data.name + '.csv');
        document.body.appendChild(link);
        link.click();
        this.$store.dispatch('setLoading', {
          loading: false,
        })
      })
      .catch(err => {
        this.$store.dispatch('setLoading', {
          loading: false,
        })
      })
    },
    submitWaresReport() {
      this.$store.dispatch('setLoading', {
          loading: true,
        })
      const url = '/api/reports/wares?period_begin=' + this.dateBegin + '&period_end=' + this.dateEnd;
      axios({
        url: url,
        method: "GET",
      }).then(response => {
        console.log(response)
        this.$emit('new-report-created')
        const url = window.URL.createObjectURL(new Blob([response.data.file]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', response.data.name + '.csv');
        document.body.appendChild(link);
        link.click();
      }).catch(err => {
        this.$store.dispatch('setLoading', {
          loading: false,
        })
      });
    },

    /**
     * Get per ware incomes and middle price
     * @return {[type]} [description]
     */
    submitChemieReport() {
      this.$store.dispatch('setLoading', {
          loading: true,
        })
      const url = '/api/reports/proposal-wares?period_begin=' + this.dateBegin + '&period_end=' + this.dateEnd;
      axios({
        url: url,
        method: "GET",
      }).then(response => {
        console.log(response)
        this.$emit('new-report-created')
        const url = window.URL.createObjectURL(new Blob([response.data.file]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', response.data.name + '.csv');
        document.body.appendChild(link);
        link.click();
        this.$store.dispatch('setLoading', {
          loading: false,
        });
      }).catch(err => {
        this.$store.dispatch('setLoading', {
          loading: false,
        })
      });
    },
    submitProfitReport() {},
  }
}
</script>

<style lang="css" scoped>
</style>