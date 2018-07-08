<template>
    <v-container grid-list-xl>
    <v-layout column>
      <v-flex xs12>
        <v-layout row wrap>
          <v-flex>
            <v-btn color="primary" dark @click="changeWareOrderDialog = true">Изменить порядок выдачи</v-btn>
          </v-flex>
          <v-flex>
            <v-btn color="primary" dark @click.stop="newWareDialog = true">Новый товар</v-btn>
          </v-flex>
        </v-layout>
      </v-flex>
      <v-flex>
          <v-dialog v-model="newWareDialog" max-width="500px">
            <v-card>
              <v-card-title>
                <span>
                  Новый товар
                </span>
                <v-spacer></v-spacer>
                <v-menu bottom left>
                  <v-btn @click="newWareDialog = false" icon slot="activator">
                    <v-icon>close</v-icon>
                  </v-btn>
                </v-menu>
              </v-card-title>
              <v-card-text>
                <new-ware-view @new-ware="onWareCreation" ></new-ware-view>
              </v-card-text>
            <v-card-actions>
              <v-btn color="primary" flat @click.stop="newWareDialog=false">Закрыть</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        <v-dialog v-model="changeWareOrderDialog" max-width="500px">
            <v-card>
              <v-card-title>
                <span>
                  Изменить порядок выдачи
                </span>
                <v-spacer></v-spacer>
                <v-menu bottom left>
                  <v-btn @click="changeWareOrderDialog = false" icon slot="activator">
                    <v-icon>close</v-icon>
                  </v-btn>
                </v-menu>
              </v-card-title>
              <v-card-text>
                <ware-order-view @updated="changeWareOrderDialog = false"></ware-order-view>
              </v-card-text>
            <v-card-actions>
              <v-btn color="primary" flat @click.stop="changeWareOrderDialog=false">Закрыть</v-btn>
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
        <template slot="items" slot-scope="props" >
          <tr @click="selectRow(props.item, props)" v-if="canShowRow(props.item)">
            <td >{{ props.item.name }}</td>
            <td class="text-xs-right">{{ props.item.minimal_price }}</td>
            <td class="text-xs-right">{{ props.item.framework.name }}</td>
            <td class="text-xs-right">{{ props.item.packaging.name }}</td>
            <td class="text-xs-right">{{ props.item.sticker.name }}</td>
          </tr>
        </template>
        <template slot="expand" slot-scope="props" v-if="hasPermission('crud_nomenclatures')">
          <v-container grid-list-md text-xs-center>
            <v-card flat>
              <progress-bar :show="busy"></progress-bar>
              <form @submit.prevent="update">

                <v-card-title primary-title>
                  <h4 class="headline mb-0">{{ props.item.name }} редактирование</h4>
                </v-card-title>

                <v-text-field
                  label="Название"
                  v-model="updateForm.name"
                  required
                ></v-text-field>

                <v-select
                  label="Основа"
                  v-model="updateForm.framework_id"
                  :items="frameworks"
                  item-text="name"
                  item-value="id"
                  required
                ></v-select>

                <v-select
                  label="Упаковка"
                  v-model="updateForm.packaging_id"
                  :items="packagings"
                  item-text="name"
                  item-value="id"
                  required
                ></v-select>

                <v-select
                  label="Наклейка"
                  v-model="updateForm.sticker_id"
                  :items="stickers"
                  item-text="name"
                  item-value="id"
                  required
                ></v-select>

                <v-card-text v-if="canShowRow(props.item)">
                  <v-layout row>
                    <v-flex>
                      <submit-button :block="true" :form="updateForm" label="Обновить"></submit-button>
                    </v-flex>

                    <v-flex v-if="props.item.is_show">
                      <v-btn  @click="hideWare(props.item.id)">Скрыть</v-btn>
                    </v-flex>

                    <v-flex v-if="!props.item.is_show">
                      <v-btn  @click="showWare(props.item.id)">Опубликовать</v-btn>
                    </v-flex>

                    <v-flex>
                      <v-btn @click="deleteWare(props.item.id)">Удалить</v-btn>
                    </v-flex>
                  </v-layout>
                </v-card-text>

              </form>
            </v-card>
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
import {mapGetters} from 'vuex'

import NewWare from '~/pages/stock/create/ware'
import WareOrder from '~/pages/stock/ware-order'

/**
 * Страница для отображения всех наименований основ (чиcтых, которые поступают на склад)
 */
export default {

  name: 'ware',

  components: {
    'new-ware-view': NewWare,
    'ware-order-view': WareOrder,
  },

  computed: mapGetters({
    user: 'authUser'
  }),

  data () {
      return {
        newWareDialog: false,
        changeWareOrderDialog: false,

        headers: [
          {
            text: 'Название',
            align: 'left',
            value: 'name'
          },
          { text: 'Себестоимость', value: 'minimal_price' },
          { text: 'Основа', value: 'framework' },
          { text: 'Упаковка', value: 'packaging' },
          { text: 'Наклейка', value: 'sticker' },
        ],
        busy: false,
        items: [],
        updateForm: new Form({
          id: 0,
          name: '',
          packaging_id: null,
          framework_id: null,
          sticker_id: null,
        }),
        frameworks: [],
        packagings: [],
        stickers: [],
        nameRules: [
          (v) => !!v || 'Название обязательно',
          (v) => v && v.length <= 2 || 'В названии не может быть меньше 2-х букв'
        ],
        priceRules: [
          (v) => v && v < 0 || 'Цена не может быть отрицательной. Пройдите школу заново'
        ]
      }
  },

  methods: {
    getWares() {
      const url = '/api/stock-data/ware'
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
      const url = 'api/stock/ware/' + this.updateForm.id
      console.log('ware_update_id', url)
      const {data} = await this.updateForm.put(url)
      this.getWares()
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Товар успешно обновлен',
        modal: false
      })
    },

    getData() {
      const url = '/api/stock/data/ware';
      axios.get(url).then(result => {
        this.frameworks = result.data.frameworks
        this.stickers = result.data.stickers
        this.packagings = result.data.packagings
      })
    },

    selectRow(item, props) {
      props.expanded = !props.expanded
      this.getData();

      this.updateForm.id = item.id
      this.updateForm.name = item.name
      this.updateForm.framework_id = item.framework.id
      this.updateForm.packaging_id = item.packaging.id
      this.updateForm.sticker_id = item.sticker.id
    },

    onWareCreation() {
      setTimeout(() => {this.getWares()}, 2000)
      this.newWareDialog = false
    },

    hideWare(wareId) {
      const url = '/api/stock/wareshow/hide/' + wareId;
      axios.get(url).then(response => {
        this.getWares()
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Товар скрыт',
          modal: false
        })
      })
    },

    showWare(wareId) {
      const url = '/api/stock/wareshow/show/' + wareId
      axios.get(url).then(response => {
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Товар опубликован',
          modal: false
        })
        this.getWares()
      })
    },

    deleteWare(id) {
      const url = '/api/stock/ware/' + id

      axios.delete(url)
        .then(response => {
          this.getData()
          this.$store.dispatch('responseMessage', {
            type: 'info',
            text: 'Товар успешно удален',
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

    canShowRow(item) {
      if (item.is_show) {
        return true
      } else {
        if(this.hasPermission('hide_wares')) {
          return true
        } else {
          return false
        }
      }
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
  },

  mounted() {
    this.getWares();
  }
}
</script>

<style lang="css" scoped>
</style>