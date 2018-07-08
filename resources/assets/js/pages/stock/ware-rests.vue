<template>
  <v-data-table
      no-data-text="Нет данных"
      v-bind:headers="headers"
      :items="items"
      class="elevation-1"
      hide-actions
      dark
    >
    <template slot="items" slot-scope="props" >
      <td v-bind:style="{'background-color': getColor(props.item.rests, props.item.minimal_in_stock)}">
        {{ props.item.ware_name }}
      </td>
      <td v-bind:style="{'background-color': getColor(props.item.rests, props.item.minimal_in_stock)}" class="text-xs-right">
        {{ props.item.framework_name }}
      </td>
      <td v-bind:style="{'background-color': getColor(props.item.rests, props.item.minimal_in_stock)}" class="text-xs-right">
        {{ props.item.rests }}
      </td>
      <td v-bind:style="{'background-color': getColor(props.item.rests, props.item.minimal_in_stock)}" class="text-xs-right">
        {{ props.item.sticker_rests }}
      </td>
      <td v-bind:style="{'background-color': getColor(props.item.rests, props.item.minimal_in_stock)}" class="text-xs-right">
        {{ props.item.packaging_rests }}
      </td>
    </template>
  </v-data-table>
</template>

<script>
import axios from 'axios'

export default {

  name: 'ware-rests',

  data () {
    return {
      items: [],
      headers: [
        {
          text: 'Название товара',
          align: 'left',
          sortable: false,
          value: 'ware_name'
        },
        { text: 'Название основы', value: 'framework_name' },
        { text: 'На складе (шт)', value: 'rests' },
        { text: 'Наклеек (шт)', value: 'sticker_rests'},
        { text: 'Упаковок (шт)', value: 'packaging_rests'}
      ],
    }
  },

  methods: {
    getData() {
      const url = '/api/open/stock/ware-rests/all';
      axios.get(url)
        .then(response => {
          this.items = response.data
        })
    },
    getColor(rests, minimal_in_stock) {
      let result = '';
      const r = Number(rests)
      const m = Number(minimal_in_stock)

      if (r < 0) {
        return '#FF5252';
      } else if(r < m)
        return '#ad1457';
      else {
        return 'transparent'
      }
    }
  },

  mounted() {
    this.getData()

    window.addEventListener('proposal-created', function() {
      that.getData();
    }, false);

    // listen for proposal status change
    window.addEventListener('proposal-status-changed', function () {
      that.getData();
    }, false);
  }
}
</script>

<style lang="css" scoped>
</style>