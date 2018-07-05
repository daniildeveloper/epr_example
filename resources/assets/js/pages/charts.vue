<template>
  <div>
    <v-select
      :items="wares"
      v-model="ware"
      label="Выбрать товар"
      item-text="name"
      item-value="id"
      single-line
      bottom
    ></v-select>
    <div id="chartdiv"></div>
  </div>
</template>

<script>
import axios from 'axios';

export default {

  name: 'charts',

  data () {
    return {
      chart: {
        label: 'Объем продаж',
        labels: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май'],
        data: [0, 20, 1000, 2000, 4000]
      },
      wares: [],
      ware: {}
    }
  },

  methods: {
    getData() {
      axios.get('/api/stock-data/ware').then((response) => {
        this.wares = response.data.data
      })
    },

    setupChart() {
      var chartData = [];
      var chart = window.AmCharts.makeChart("chartdiv", {
          "type": "serial",
          "theme": "dark",
          "legend": {
              "useGraphSettings": false
          },
          "dataProvider": chartData,
          "synchronizeGrid": false,
          "valueAxes": [{
              "id":"v1",
              "axisColor": "#FF6600",
              "axisThickness": 2,
              "axisAlpha": 1,
              "position": "left"
          }, {
              "id":"v2",
              "axisColor": "#FCD202",
              "axisThickness": 2,
              "axisAlpha": 1,
              "position": "right"
          }, {
              "id":"v3",
              "axisColor": "#B0DE09",
              "axisThickness": 2,
              "gridAlpha": 0,
              "offset": 50,
              "axisAlpha": 1,
              "position": "left"
          }],
          "graphs": [{
              "valueAxis": "v1",
              "lineColor": "#FF6600",
              "bullet": "round",
              "bulletBorderThickness": 1,
              "hideBulletsCount": 30,
              "title": "Цена",
              "valueField": "visits",
          "fillAlphas": 0
          }, {
              "valueAxis": "v2",
              "lineColor": "#FCD202",
              "bullet": "square",
              "bulletBorderThickness": 1,
              "hideBulletsCount": 30,
              "title": "Оборот",
              "valueField": "hits",
          "fillAlphas": 0
          }, {
              "valueAxis": "v3",
              "lineColor": "#B0DE09",
              "bullet": "triangleUp",
              "bulletBorderThickness": 1,
              "hideBulletsCount": 30,
              "title": "Объем продаж",
              "valueField": "views",
          "fillAlphas": 0
          }],
          "chartCursor": {
              "cursorPosition": "mouse"
          },
          "categoryField": "date",
          "categoryAxis": {
              "parseDates": true,
              "axisColor": "#DADADA",
              "minorGridEnabled": false
          },
          "export": {
            "enabled": false,
              "position": "bottom-right"
           }
      });
    }
  },
  mounted() {
    this.getData()
    this.setupChart()
  }
}
</script>

<style lang="css" scoped>
</style>