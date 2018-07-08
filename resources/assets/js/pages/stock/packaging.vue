<template>
  <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-btn color="primary" dark @click.stop="newPackagingDialog = true">Новая упаковка</v-btn>
          <v-dialog v-model="newPackagingDialog" max-width="500px">
            <v-card>
              <v-card-title>
                <span>
                  Новая упаковка
                </span>
                <v-spacer></v-spacer>
                <v-menu bottom left>
                  <v-btn @click="newPackagingDialog = false" icon slot="activator">
                    <v-icon>close</v-icon>
                  </v-btn>
                </v-menu>
              </v-card-title>
              <v-card-text>
                <new-packaging-view @new-packaging="onCreate"></new-packaging-view>
              </v-card-text>
            <v-card-actions>
              <v-btn color="primary" flat @click.stop="newPackagingDialog=false">Закрыть</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
      </v-flex>

      <v-flex>
        <v-data-table
          no-data-text="Нет данных"
          :headers="headers"
          :items="items"
          hide-actions
          item-key="name"
        >
          <template slot="items" slot-scope="props">
            <tr @click="selectRow(props.item, props)">
              <td >{{ props.item.name }}</td>
              <td class="text-xs-right">{{ props.item.price }}</td>
              <td class="text-xs-right">{{ props.item.minimal_in_stock }}</td>
            </tr>
          </template>
          <template slot="expand" slot-scope="props" >
            <v-card flat>
              <v-container grid-list-md text-xs-center>
                <v-layout row wrap>
                  <v-flex xs12 sm6 v-if="hasPermission('crud_nomenclatures')">
                    <progress-bar :show="busy"></progress-bar>
                    <form @submit.prevent="update">

                      <v-card-title primary-title>
                        <h4 class="headline mb-0">{{ props.item.name }} редактирование</h4>
                      </v-card-title>

                      <v-text-field
                        label="Название"
                        v-model="updateForm.name"
                        :rues="nameRules"
                        required
                      ></v-text-field>

                      <v-text-field
                        label="Стоимость"
                        v-model="updateForm.price"
                        required
                      ></v-text-field>

                      <v-text-field
                        label="Минимум на складе"
                        v-model="updateForm.minimal_in_stock"
                        required
                      ></v-text-field>

                      <v-card-text>
                        <submit-button :block="true" :form="updateForm" label="Обновить"></submit-button>
                      </v-card-text>

                    </form>
                  </v-flex>

                  <v-flex xs12 sm6>
                    <h3 class="headline mb-0">Операции</h3>
                    <v-btn color="primary" dark @click.stop="addPackagingDialog = true">Добавить упаковок</v-btn>
                    <!-- <v-btn color="primary" dark @click.stop="packagingToMoneyDialog = true">Перевести в деньги</v-btn> -->
                    <v-dialog v-model="addPackagingDialog" max-width="400px">
                      <v-card>
                        <v-card-title>
                          <span>Добавить упаковок</span>
                          <v-spacer></v-spacer>
                          <v-menu bottom left>
                            <v-btn @click="addPackagingDialog = false" icon slot="activator">
                              <v-icon>close</v-icon>
                            </v-btn>
                          </v-menu>
                        </v-card-title>
                        <v-card-text>
                          <form>
                            <p> На сумму {{ addForm.count * updateForm.price }} тг </p>
                            <v-text-field
                              label="Добавить единиц ..."
                              v-model="addForm.count"
                              required
                            ></v-text-field>
                            <v-btn
                              @click="addPackagingToRests"
                              >Добавить
                            </v-btn>
                          </form>
                        </v-card-text>
                        <v-card-actions>
                          <v-btn color="primary" flat @click.stop="addPackagingDialog=false">Закрыть</v-btn>
                        </v-card-actions>
                      </v-card>
                    </v-dialog>
                  </v-flex>
                </v-layout>
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
import {mapGetters} from 'vuex'

import NewPackaging from '~/pages/stock/create/packaging'

/**
 * Страница для отображения всех наименований основ (чиcтых, которые поступают на склад)
 */
export default {

  name: 'framework',

  components: {
    'new-packaging-view': NewPackaging,
  },

  computed: mapGetters({
    user: 'authUser'
  }),

  data () {
      return {
        // dialogs
        newPackagingDialog: false,
        addPackagingDialog: false,
        packagingToMoneyDialog: false,

        headers: [
          {
            text: 'Название',
            align: 'left',
            value: 'name'
          },
          { text: 'Цена', value: 'price' },
          { text: 'Минимум на складе', value: 'minimal_in_stock' },
        ],
        busy: false,
        items: [],
        updateForm: new Form({
          id: 0,
          name: '',
          price: 0,
          minimal_in_stock: 0,
        }),
        nameRules: [
          (v) => !!v || 'Название обязательно',
          (v) => v && v.length <= 2 || 'В названии не может быть меньше 2-х букв'
        ],
        priceRules: [
          (v) => v && v < 0 || 'Цена не может быть отрицательной. Пройдите школу заново'
        ],

        // forms to add or delete
        addForm: new Form({
          id: 0,
          count: 10,
        }),
        toMoneyForm: new Form({
          id: 0,
          count: 10
        }),
      }
  },

  methods: {
    getPackagings() {
      const url = '/api/stock-data/packagings'
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
      const url = 'api/stock/packaging/' + this.updateForm.id
      const {data} = await this.updateForm.put(url)
      this.getPackagings()
      this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Упаковка обновлена',
          modal: false
        })
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
      this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Упаковка создана',
          modal: false
        })
      this.newPackagingDialog = false;
    },

    hasPermission(permission) {
      let permissionsArray = [];
      this.user.data.permissions.forEach(p => {
        permissionsArray.push(p.name);
      });

      if (permissionsArray.indexOf(permission) !== -1) {
        return true;
      }
      return false
    },

    packagingToMoney() {
    },

    addPackagingToRests() {
      const url = '/api/crud-nomenclatures/manipulations/packaging';
      axios.post(url, {
        packaging_id: this.updateForm.id,
        count: this.addForm.count,
        refill: true,
      }).then(response => {
        this.addStickerDialog = false
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Упаковки пополнены',
          modal: false
        })
      })
    }
  },

  mounted() {
    this.getPackagings();
  }
}
</script>

<style lang="css" scoped>
</style>