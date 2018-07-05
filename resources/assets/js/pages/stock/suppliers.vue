<template>
  <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-btn color="primary" dark @click.stop="newSupplierDialog = true">Новый поставщик</v-btn>
          <v-dialog v-model="newSupplierDialog" max-width="500px">
            <v-card>
              <v-card-title>
                Новый поставщик
              </v-card-title>
              <v-card-text>
                <new-supplier-view @new-supplier="onCreate"></new-supplier-view>
              </v-card-text>
            <v-card-actions>
              <v-btn color="primary" flat @click.stop="newSupplierDialog=false">Закрыть</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
      </v-flex>

      <v-flex>
        <v-data-table
          :headers="headers"
          :items="items"
          hide-actions
          item-key="name"
        >
          <template slot="items" slot-scope="props">
            <tr @click="selectRow(props.item, props)">
              <td >{{ props.item.name }}</td>
              <td class="text-xs-right">{{ props.item.phone }}</td>
            </tr>
          </template>
          <template slot="expand" slot-scope="props">
            <v-card flat>
              <v-container grid-list-md text-xs-center>
                <progress-bar :show="busy"></progress-bar>
                <form @submit.prevent="update">

                  <v-card-title primary-title>
                    <h4 class="headline mb-0">{{ props.item.name }} редактирование</h4>
                  </v-card-title>

                  <v-text-field
                    label="Имя"
                    v-model="updateForm.name"
                    :rues="nameRules"
                    required
                  ></v-text-field>

                  <v-text-field
                    label="Телефон"
                    v-model="updateForm.phone"
                    required
                  ></v-text-field>

                  <v-text-field
                    label="Email"
                    v-model="updateForm.email"
                    required
                  ></v-text-field>

                  <v-layout row>
                    <v-flex xs4>
                      <v-subheader>Заметки</v-subheader>
                    </v-flex>
                    <v-flex xs8>
                      <v-text-field
                        v-model="updateFor.notes"
                        name="notes"
                        label="Заметки о поставщике."
                        value=""
                        multi-line
                      ></v-text-field>
                    </v-flex>
                  </v-layout>

                  <v-card-text>
                    <submit-button :block="true" :form="updateForm" label="Обновить"></submit-button>
                  </v-card-text>

                </form>
              </v-container>
            </v-card>
          </template>
        </v-data-table>
      </v-flex>
    </v-layout>
  </v-container>
</template>


<script>
import axios from 'axios'
import Form from 'vform'

import NewSupplier from '~/pages/stock/create/supplier'

/**
 * Страница для отображения всех наименований основ (чиcтых, которые поступают на склад)
 */
export default {

  name: 'supplier',

  components: {
    'new-supplier-view': NewSupplier,
  },

  data () {
    return {
      newSupplierDialog: false,
      headers: [
        {
          text: 'Имя',
          align: 'left',
          value: 'name'
        },
        { text: 'Телефон', value: 'phone' },
      ],
      busy: false,
      items: [],
      updateForm: new Form({
        id: 0,
        name: '',
        phone: '',
        email: '',
        notes: '',
      }),
      nameRules: [
        (v) => v && v.length <= 2 || 'В названии не может быть меньше 2-х букв'
      ],
      priceRules: [
        (v) => v && v < 0 || 'Цена не может быть отрицательной. Пройдите школу заново'
      ]
    }
  },

  methods: {
    getPackagings() {
      const url = '/api/stock/supplier/'
      axios.get(url).then(result => {
          this.items = result.data.data
      });
    },

    /**
     * Update framework
     * @param  {[type]} id [description]
     * @return {[type]}    [description]
     */
    async update() {
      const url = 'api/stock/supplier/' + this.updateForm.id
      const {data} = await this.updateForm.put(url)
      this.getPackagings()
    },

    getData() {
      this.getPackagings()
    },

    selectRow(item, props) {
      props.expanded = !props.expanded

      this.updateForm.id = item.id
      this.updateForm.name = item.name
      this.updateForm.price = item.price
      this.updateForm.minimal_in_stock = item.minimal_in_stock
    },

    onCreate() {
      this.newSupplierDialog = false;
    }
  },

  mounted() {
    this.getPackagings();
  }
}
</script>

<style lang="css" scoped>
</style>