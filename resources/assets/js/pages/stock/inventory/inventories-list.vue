<template>
  <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-data-table
          no-data-text="Нет данных"
          :headers="headers"
          :items="items"
          hide-actions
          item-key="name"
        >
        <template slot="items" slot-scope="props">
          <tr>
            <td >{{ props.item.id }}</td>
            <td class="text-xs-right">{{ props.item.created_at }}</td>
            <td class="text-xs-right">{{ props.item.answered.name }}</td>
            <td class="text-xs-right">{{ getComponentName(props.item.component_id, props.item.component_type) }}</td>
            <td class="text-xs-right">{{ props.item.real_rest }}</td>
          </tr>
        </template>
        </v-data-table>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
import axios from 'axios';

/**
 * Component to show history inventories data
 */
export default {

  name: 'inventories-list',

  data () {
    return {
        headers: [
          { text: '#', value: 'id' },
          {
            text: 'Дата',
            align: 'left',
            value: 'created_at'
          },
          { text: 'Ответственный', value: 'answered.name' },
          { text: 'Название компонента', value: '' },
          { text: 'Остатки, шт', value: 'price' },
        ],

        types: [],
        items: [],
        components: [],

    }
  },

  methods: {
    getComponentName(id, type) {
        let result = '';
        console.log('id and type', id + ", " + type)
        console.log('components_type', this.components[type])
        this.components[type].forEach(function (item) {
            if (parseInt(item.id) === parseInt(id)) {
                result = item.name;
            }
        });
        console.log('result', result)
        return result;
    },

    getData() {
        axios.get('/api/inventory')
            .then (response => {
                this.types = response.data.types;
                this.components = response.data.components;
                this.items = response.data.inventories.data;
            })
    }
  },

  mounted() {
    this.getData();
  }
}
</script>

<style lang="css" scoped>
</style>