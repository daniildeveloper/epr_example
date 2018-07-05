<template>
  <div id="chartdiv"></div>
</template>

<script>
import axios from 'axios';

/**
 * Component with per user proposal sales info
 */
export default {

  name: 'proposals-pie-chart',

  data () {
    return {
      chart: {},
    }
  },

  methods: {
    chartSetup() {
      let data = []; // proposals data

      // get proposals sales statistics
      axios.get('/api/reports/sales')
        .then(response => {
          data = response.data;
          // setup chart\
          this.chart = AmCharts.makeChart( "chartdiv", {
            "type": "pie",
            "theme": "dark",
            "dataProvider": data,
            "valueField": "total_incomes",
            "titleField": "name",
            "outlineAlpha": 0.4,
            "depth3D": 15,
            "balloonText": "[[name]]<br><span style='font-size:14px'><b>[[total_incomes]] тг</b><br><b>Всего заявок: [[proposals]]</b></span>",
            "angle": 30,
            // "export": {
            //   "enabled": true
            // }
          } );
          console.log(this.chart)
        });
    },
  },

  mounted() {
    this.chartSetup();
  }
}
</script>

<style lang="css" scoped>
body { color: #fff; }
#chartdiv {
  width: 100%;
  height: 500px;
  color: #fff !important;
}
</style>