<template>
  <v-container grid-list-xl>
    <v-layout column v-if="proposals.length === 0 && clients.length === 0 && objects.length === 0">
      <h3>По вашему запросу ничего не найдено</h3>
    </v-layout>
    <v-layout column v-if="proposals.length > 0" >
      <h5>Заявки</h5>
      <v-flex>
        <v-data-table
          :headers="headersProposals"
          :items="proposals"
          hide-actions
          item-key="name"
        >
        <template slot="items" slot-scope="props">
          <tr>
            <td class="text-xs-right">{{ props.item.id }}</td>
            <td class="text-xs-right">{{ props.item.code }}</td>
            <td class="text-xs-right">{{ props.item.client}}</td>
            <td class="text-xs-right" >{{ props.item.object != null ? props.item.object : '' }}</td>
            <td class="text-xs-right">{{ props.item.client_deadline }}</td>
            <td class="text-xs-right">{{ props.item.workers_deadline }}</td>
            <td class="text-xs-right">{{ props.item.created_at }}</td>
          </tr>
        </template>
      </v-data-table>
    </v-flex>
  </v-layout>
</v-container>
  
</template>

<script>
export default {

  name: 'search-global',

  props: [
    'clients',
    'proposals',
    'objects'
  ],

  data () {
    return {
      headersProposals: [
        {
          text: '#',
          value: 'id'
        },
        {
          text: 'Код заявки',
          align: 'left',
          value: 'code'
        },
        { text: 'Клиент', value: 'client.name' },
        { text: 'Объект', value: 'object.address' },
        {
          text: 'Дедлайн',
          value: 'client_deadline',
        },
        {
          text: 'Готово в цеху',
          value: 'workers_deadline'
        },
        {
          text: 'Создана',
          value: 'created_at'
        }
      ],
      headersClients: [
        {
          text: 'ФИО',
          align: 'left',
          value: 'name'
        },
        { text: 'Телефон', value: 'phone' },
        { text: 'Почта', value: 'email' },
      ],
      headersObjects: [
        {
          text: 'Адрес',
          align: 'left',
          value: 'address'
        },
        { text: 'Клиент', value: 'client_id' },
        { text: 'Заметки', value: 'notes' },
      ],
      busy: false,
    }
  }
}
</script>

<style lang="css" scoped>
</style>