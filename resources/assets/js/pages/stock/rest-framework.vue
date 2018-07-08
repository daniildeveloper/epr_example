<template>
  <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-btn color="primary" dark @click.stop="newRestFrameworkDialog = true">Новая основа</v-btn>
          <v-dialog v-model="newRestFrameworkDialog" max-width="500px">
            <v-card>
              <v-card-title>
                <span>
                  Новая основа
                </span>
                <v-spacer></v-spacer>
                <v-menu bottom left>
                  <v-btn @click="newRestFrameworkDialog = false" icon slot="activator">
                    <v-icon>close</v-icon>
                  </v-btn>
                </v-menu>
              </v-card-title>
              <v-card-text>
                <new-rest-view @new-restframework="onCreate"></new-rest-view>
              </v-card-text>
            <v-card-actions>
              <v-btn color="primary" flat @click.stop="newRestFrameworkDialog=false">Закрыть</v-btn>
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
        <template slot="expand" slot-scope="props" v-if="hasPermission('crud_nomenclatures')">
          <v-container grid-list-md text-xs-center>
            <v-layout row wrap>
              <v-flex xs12 sm6>
                <progress-bar :show="busy"></progress-bar>
                <form @submit.prevent="update">

                  <v-card-title primary-title>
                    <h3 class="headline mb-0">{{ props.item.name }} редактирование</h3>
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
                <v-btn color="primary" dark @click.stop="addFrameworkDialog = true">Добавить основ</v-btn>
                <v-btn color="primary" dark @click.stop="frameworkToMoneyDialog = true">Перевести в деньги</v-btn>
                <v-dialog v-model="addFrameworkDialog" max-width="400px">
                  <v-card>
                    <v-card-title>
                      <span>Добавить основ</span>
                      <v-spacer></v-spacer>
                      <v-menu bottom left>
                        <v-btn @click="addFrameworkDialog = false" icon slot="activator">
                          <v-icon>close</v-icon>
                        </v-btn>
                      </v-menu>
                    </v-card-title>
                    <v-card-text>
                      <form>
                        <span>Добавить</span>
                        <v-spacer></v-spacer>
                        <p> На сумму {{ addForm.count * updateForm.price }} тг </p>
                        <v-text-field
                          label="Добавить единиц ..."
                          v-model="addForm.count"
                          required
                        ></v-text-field>
                        <v-btn
                          @click="addFrameworkToRests"
                          >Добавить
                        </v-btn>
                      </form>
                    </v-card-text>
                    <v-card-actions>
                      <v-btn color="primary" flat @click.stop="addFrameworkDialog=false">Закрыть</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
                <v-dialog v-model="frameworkToMoneyDialog" max-width="400px">
                  <v-card>
                    <v-card-title>
                      <span>Перевести в деньги</span>
                      <v-spacer></v-spacer>
                      <v-menu bottom left>
                        <v-btn @click="frameworkToMoneyDialog = false" icon slot="activator">
                          <v-icon>close</v-icon>
                        </v-btn>
                      </v-menu>
                    </v-card-title>
                    <v-card-text>
                      <form>
                        <v-text-field
                          label="Перевести в деньги ..."
                          v-model="toMoneyForm.count"
                          required
                        ></v-text-field>
                        <v-btn
                          @click="frameworkToMoney"
                          >Перевести
                        </v-btn>
                      </form>
                    </v-card-text>
                    <v-card-actions>
                      <v-btn color="primary" flat @click.stop="frameworkToMoneyDialog=false">Закрыть</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </v-flex>
            </v-layout>
          </v-container>
        </template>
      </v-data-table>
    </v-flex>
    </v-layout>
  </v-container>
</template>


<script>
import axios from 'axios'
import Form from 'vform'
import NewRestFramework from '~/pages/stock/create/restframework'

import {mapGetters} from 'vuex'

/**
 * Страница для отображения всех наименований основ (чиcтых, которые поступают на склад)
 */
export default {

  name: 'framework',

  components: {
    'new-rest-view': NewRestFramework,
  },
  computed: mapGetters({
    user: 'authUser'
  }),

  data () {
      return {
        newRestFrameworkDialog: false,
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
        addForm: new Form({
          id: 0,
          count: 10,
        }),
        toMoneyForm: new Form({
          id: 0,
          count: 10
        }),
        nameRules: [
          (v) => !!v || 'Название обязательно',
          (v) => v && v.length <= 2 || 'В названии не может быть меньше 2-х букв'
        ],
        priceRules: [
          (v) => v && v < 0 || 'Цена не может быть отрицательной. Пройдите школу заново'
        ],
        addFrameworkDialog: false,
        frameworkToMoneyDialog: false,
        sumToAdd: 0, // sum for money to add
      }
  },

  methods: {
    getFrameworks() {
      const url = '/api/stock-data/rest-frameworks'
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
      
      console.log(this.updateForm)
      const url = 'api/stock/rframework/' + this.updateForm.id
      const {data} = await this.updateForm.put(url)
      this.getFrameworks()

      this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Основа обновлена',
          modal: false
        })
    },

    getData() {
      this.getFrameworks()
    },

    selectRow(item, props) {
      props.expanded = !props.expanded

      this.updateForm.id = item.id
      this.updateForm.name = item.name
      this.updateForm.price = item.price
      this.updateForm.minimal_in_stock = item.minimal_in_stock

      // add and to money forms ids
      this.addForm.id = item.id
      this.toMoneyForm.id = item.id
    },

    onCreate() {
      this.newRestFrameworkDialog = false;
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Новая основа создана',
        modal: false
      })
    },

    addFrameworkToRests() {
      axios.post('/api/crud-nomenclatures/manipulations/store', {
        framework_id: this.updateForm.id,
        count: this.addForm.count,
        refill: true,
      }).then(response => {
        this.addFrameworkDialog = false
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Добавлены остатки по основам',
          modal: false
        })
      })
      
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

    frameworkToMoney() {
      axios.post('/api/crud-nomenclatures/manipulations/store', {
        framework_id: this.toMoneyForm.id,
        count: this.toMoneyForm.count,
        refill: false,
      }).then(response => {
        this.frameworkToMoneyDialog = false
      })
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Основа переведена в деньги',
        modal: false
      })
    },
  },

  beforeCreate() {
    const url = '/api/stock-data/rest-frameworks'

    axios.get(url).then(result => {
        this.items = result.data.data
    });
  },

  mounted() {
    this.getFrameworks();
  }
}
</script>

<style lang="css" scoped>
</style>