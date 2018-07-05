<template>
  <form>
    <v-container grid-list-md>
      <v-layout row wrap>
        <!-- Выбор товара -->
        <v-flex xs12 sm6>
          <v-layout align-content-start row wrap>
            <!-- wrap each ware config -->
            <v-layout xs10 row wrap >
                <v-flex xs10>
                  <v-select
                    autocomplete
                    label="Товар"
                    v-model="form.ware_id"
                    placeholder="Выбрать товар"
                    :items="wares"
                    item-text="name"
                    item-value="id"
                    required
                  ></v-select>
                </v-flex>
                
                <v-flex xs10>
                  <v-text-field
                    label="Цена за единицу товара"
                    v-model="form.price_per_count"
                    required
                  ></v-text-field>
                </v-flex>

                <v-flex xs10>
                  <v-text-field
                    label="Количество товара"
                    v-model="form.count"
                    required
                  ></v-text-field>
                </v-flex>

                <v-flex xs10>
                  <v-checkbox
                    label="Без цвета"
                    v-model="form.color_doesnt_exist"
                  ></v-checkbox>
                </v-flex>

                <v-flex xs10 v-if="form.color_doesnt_exist === false">
                  <v-text-field
                    label="Цвет"
                    v-model="form.color"
                    required
                  ></v-text-field>
                </v-flex>

                <v-flex xs10 v-if="form.color_doesnt_exist === false">
                  <v-text-field
                    label="Стоимость цвета"
                    v-model="form.color_price"
                    required
                  ></v-text-field>
                </v-flex>
            </v-layout>
            <!-- end each ware config -->
          </v-layout>
        </v-flex>

        <!-- выбор товара -->

        <!-- выбор клиента -->
        <v-flex xs12 sm6>
          <v-layout align-content-start row wrap>
            <v-flex xs12>
              <v-select
                label="Клиент"
                autocomplete
                :loading="loadingClientSearch"
                cache-items
                required
                :items="clients"
                :search-input.sync="searchClient"
                item-text="name"
                item-value="id"
                v-model="form.client_id"
              ></v-select>
            </v-flex>

            <v-flex xs12 v-if="form.client_id == 0">
              <v-btn color="primary" dark @click.stop="newClientDialog = true">Создать нового клиента</v-btn>
              <v-dialog v-model="newClientDialog" max-width="700px">
                <v-card>
                  <v-card-title>
                    Новый клиент
                  </v-card-title>
                  <v-card-text>
                    <new-client-view></new-client-view>
                  </v-card-text>
                  <v-card-actions>
                    <v-btn color="primary" flat @click.stop="newClientDialog=false">Закрыть</v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
            </v-flex>

            <v-flex xs12 v-if="form.client_id !== 0">
              <v-select
                label="Объект"
                placeholder="Выбрать объект"
                v-model="form.object_id"
                autocomplete
                :loading="loadingObjectSearch"
                cache-items
                :items="objects"
                :search-input.sync="searchObject"
                item-text="address"
                item-value="id"
              ></v-select>
            </v-flex>

            <v-flex xs12 v-if="form.object_id == 0 && form.client_id !== 0">
              <v-btn color="primary" dark @click.stop="newObjectDialog = true">Создать новый объект</v-btn>
              <v-dialog v-model="newObjectDialog" max-width="700px">
                <v-card>
                  <v-card-title>
                    Новый объект
                  </v-card-title>
                  <v-card-text>
                    <new-object-view></new-object-view>
                  </v-card-text>
                  <v-card-actions>
                    <v-btn color="primary" flat @click.stop="newObjectDialog=false">Закрыть</v-btn>
                  </v-card-actions>
                </v-card>
              </v-dialog>
            </v-flex>

            <v-flex xs12>
              <v-checkbox
                label="Срочная заявка?"
                v-model="form.is_hot"
              ></v-checkbox>
            </v-flex>

            <v-flex xs11 sm5>
              <v-menu
                lazy
                :close-on-content-click="false"
                v-model="workers_deadline_menu"
                transition="scale-transition"
                offset-y
                full-width
                :nudge-right="40"
                max-width="290px"
                min-width="290px"
              >
                <v-text-field
                  slot="activator"
                  label="Дата готовности в цеху"
                  v-model="form.workers_deadline_formatted"
                  prepend-icon="event"
                  @blur="form.workers_deadline = parseDate(form.workers_deadline_formatted)"
                ></v-text-field>
                <v-date-picker v-model="form.workers_deadline" @input="form.workers_deadline_formatted = formatDate($event)" no-title scrollable actions>
                  <template slot-scope="{ save, cancel }">
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn flat color="primary" @click="save">Выбрать</v-btn>
                      <!-- <v-btn flat color="primary" @click="save">OK</v-btn> -->
                    </v-card-actions>
                  </template>
                </v-date-picker>
              </v-menu>
            </v-flex>

            <v-flex xs11 sm5>
              <v-menu
                lazy
                :close-on-content-click="false"
                v-model="client_deadline_menu"
                transition="scale-transition"
                offset-y
                full-width
                :nudge-right="40"
                max-width="290px"
                min-width="290px"
              >
                <v-text-field
                  slot="activator"
                  label="Дата сдачи клиенту"
                  v-model="form.client_deadline_formatted"
                  prepend-icon="event"
                  @blur="form.client_deadline = parseDate(form.client_deadline_formatted)"
                ></v-text-field>
                <v-date-picker v-model="form.client_deadline" @input="form.client_deadline_formatted = formatDate($event)" no-title scrollable actions>
                  <template slot-scope="{ save, cancel }">
                    <v-card-actions>
                      <v-spacer></v-spacer>
                      <v-btn flat color="primary" @click="save">Выбрать</v-btn>
                      <!-- <v-btn flat color="primary" @click="save">OK</v-btn> -->
                    </v-card-actions>
                  </template>
                </v-date-picker>
              </v-menu>
            </v-flex>

            <v-flex xs12>
              <v-checkbox
                label="С документами"
                v-model="form.with_docs"
              ></v-checkbox>
            </v-flex>

            <v-flex xs10 v-if="form.with_docs != 0">
              <v-text-field
                label="Общая сумма налогов для уплаты"
                v-model="form.tax"
                required
              ></v-text-field>
            </v-flex>

          </v-layout>
        </v-flex>
        <!-- выбор клиента -->
      </v-layout>
    </v-container>

    <v-btn @click="submit">Обновить</v-btn>
  </form>
</template>

<script>
import { validationMixin } from 'vuelidate'
import { required, maxLength, numeric } from 'vuelidate/lib/validators'
import axios from 'axios'
import { Form } from 'vform';

import NewClient from '~/pages/client/new-client'
import NewObject from '~/pages/object/new-object'

/**
 * В модальном окошке открывается сей компонент.
 ** 1. Выбор товара.
 * *  Из выпадающего списка выбирается товар. Отмечается в переменной.
 **  2. Ввод количества
 *    На сервер отсылается запрос, есть ли в наличии столько товара.
 **  3. Отображается количество основы на складе ровно под самим товаром.
 **  4. Если данных позиций нет в достаточном количестве на складе, мы рендерим пункт чекбокс по предоплате, который обязателен.
 **  5. Выбор клиента или создание нового
 **  6. выбор объкта или создание нового
 **  7. выбор сроков сдачи клиенту
 *  8. выбор сроков сдачи продажникам
 *  9. галочка срочности заявки(по сути, она становится в приоритете)
 *  10. Выбор с документами заявка или без.
 *  11. Цена отпуска за единицу товара *  
 */
export default {

  name: 'proposal-new',

  mixins: [validationMixin],

  validations: {
    
  },

  components: {
    'new-client-view': NewClient,
    'new-object-view': NewObject,
  },

  props: [
    'object_id',
    'client_id',
    'ware_id',
    'price_per_count',
    'count',
    'color_doesnt_exist',
    'color_price',
    'color'
    'is_hot',
    'with_docs',
    'tax',
    'workers_deadline',
    'workers_deadline_formatted',
    'client_deadline',
    'client_deadline_formatted',
  ],

  data () {
    return {
      form: new Form({
        object_id: 0,
        client_id: 0,
        ware_id: 0,
        price_per_count: null,
        count: 1,
        color_doesnt_exist: true,
        color_price: 0,
        color: '',
        is_hot: false,
        with_docs: true,
        tax: null,
        workers_deadline: null,
        workers_deadline_formatted: null,
        client_deadline: null,
        client_deadline_formatted: null,
      }),

      workers_deadline_menu: false,
      client_deadline_menu: false,


      wares: [],

      loadingClientSearch: false,
      loadingObjectSearch: false,

      searchClient: '',
      searchObject: '',

      clients: [],
      objects: [],
      
      newClientDialog: false,
      newObjectDialog: false,
    }
  },
  methods: {
    submit () {
      this.$v.$touch()
      const {data} = await this.form.put('/api/proposal/proposal')
    },
    getData () {
      const url = '/api/proposal/data/creation'

      axios.get(url).then(result => {
        this.wares = result.data.data.wares
      })
    },
    clientSearchMethod(v) {
      this.loadingClientSearch = true;

      const url = '/api/proposal/data/client/search?query=' + v

      axios.get(url).then(result => {
        this.clients = result.data
        this.loadingClientSearch = false
      });
    },
    objectSearchMethod(v) {
      this.loadingObjectSearch = true;

      const url = '/api/proposal/data/object/search?query=' + v + '&client_id=' + this.form.client_id

      console.log(url)

      axios.get(url).then(response => {
        console.log('response.data', response.data)
        this.objects = response.data
        this.loadingObjectSearch = false
      })
    },
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
    clear() {
      this.form.object_id = 0
      this.form.client_id = 0
      this.form.ware_id = 0
      this.form.price_per_count = null
      this.form.count = 1
      this.form.color_doesnt_exist = true
      this.form.color_price = 0
      this.form.color = ''
      this.form.is_hot = false
      this.form.with_docs = true
      this.form.tax = null
      this.form.workers_deadline = null
      this.form.workers_deadline_formatted = null
      this.form.client_deadline = null
      this.form.client_deadline_formatted = null
    }
  },
  computed: {
    nameErrors () {
      const errors = []
      if (!this.$v.name.$dirty) return errors
      !this.$v.name.maxLength && errors.push('Максимальная длина названия не более 256 символов')
      !this.$v.name.required && errors.push('Ну как же без названия?')
      return errors
    },
    selectErrors () {
      const errors = []
      if (!this.$v.select.$dirty) return errors
      !this.$v.select.required && errors.push('Поле не должно оставаться пустым')
      return errors
    },
  },
  mounted() {
    this.getData()
  },
  watch: {
    searchClient(val) {
      this.clientSearchMethod(val);
    },
    searchObject(val) {
      this.objectSearchMethod(val);
    },
    object_id(val) {
      this.form.object_id = val
    },
    client_id(val) {
      this.form.client_id = val
    },
    ware_id(val) {
      this.form.ware_id = val
    },
    price_per_count(val) {
      this.form.price_per_count = val
    },
    color(val) {
      this.form.color = val
    },
    color_doesnt_exist(val) {
      this.form.color_doesnt_exist = val
    },
    color_price(val) {
      this.form.color_price = val
    },
    count(val) {
      this.form.count = val
    },
    is_hot(val) {
      this.form.is_hot = val
    },
    with_docs(val) {
      this.form.with_docs = val
    },
    tax(val) {
      this.form.tax = tax
    },
    workers_deadline(val) {
      this.form.workers_deadline = val
    },
    client_deadline(val) {
      this.form.client_deadline = val
    }

  }
}
</script>

<style lang="css" scoped>
</style>