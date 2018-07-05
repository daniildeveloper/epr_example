<template>
  <form 
    lazy-validation
    v-model="valid" 
    refs="new_proposal_form">
    <v-progress-circular v-if="loading" :indeterminate="loading" color="primary" :style="{position: 'absolute', top: '20px', left: '40%' }"></v-progress-circular>
    <v-container grid-list-md>
      <v-layout row wrap>
        <!-- Выбор товара -->
          <v-flex xs12 sm6>
            <proposal-ware-view
              v-for="(ware, keyWare) in proposalWaresList"
              v-if="ware.is_show"
              v-bind:data="ware"
              v-bind:key="ware.id"
              :wares="wares"
              :keyWare="keyWare"
              :warranty="warranty_case"
              :validationErrors="validationErr"
              @proposal-ware-id-change="setProposalWareID"
              @proposal-ware-count-change="setProposalWareCount"
              @proposal-ware-price-per-count-change="setProposalWarePricePerCount"
              @proposal-ware-color-price-change="setProposalWareColorPrice"
              @proposal-ware-color-change="setProposalWareColor"
              @proposal-ware-color-doesnt-exist-change="setProposalWareColorDoesntExist"
              @proposal-ware-delete="deleteProposalWare"
              @proposal-ware-partner-free-change="setProposalWarePartnerFree"
              @proposal-ware-partner-change="setProposalWarePartner"
              @proposal-ware-partner-notes-change="setProposalWarePartnerNotes"
            ></proposal-ware-view>

            <v-flex xs12 sm6>
              <v-btn
                @click="addWare"
                >Добавить товар
              </v-btn>
            </v-flex>
          </v-flex>

        <!-- выбор клиента -->
        <v-flex xs12 sm6>
          <v-layout align-content-start row wrap>
            <v-flex xs12>
              Итого к оплате: {{ proposalTotal + (tax_type === null ? 0 : ((proposalTotal * tax) / 100) ) }}
            </v-flex>
            
            <!-- client name -->
            <v-flex>
              <v-text-field
                label="Клиент"
                v-model="client"
                :error="validationErr.client && validationErr.client.length > 0"
                :error-messages="validationErr.client"
              ></v-text-field>
            </v-flex>
            <!-- end client name -->

            <!-- client phone -->
            <v-flex>
              <v-text-field
                label="Телефон клиента"
                v-model="client_phone"
                :error="validationErr.client_phone && validationErr.client_phone.length > 0"
                :error-messages="validationErr.client_phone"
              ></v-text-field>
            </v-flex>
            <!-- end client phone -->

            <!-- object address -->
            <v-flex>
              <v-text-field
                label="Адрес объекта"
                v-model="object"
              ></v-text-field>
            </v-flex>
            <!-- end object address -->

            <v-flex xs12>
              <v-checkbox
                label="Срочная заявка?"
                v-model="is_hot"
              ></v-checkbox>
            </v-flex>

             <v-flex xs11 sm6>
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
                  v-model="client_deadline_formatted"
                  prepend-icon="event"
                  @blur="client_deadline = parseDate(client_deadline_formatted)"
                  :error="validationErr.client_deadline && validationErr.client_deadline.length > 0"
                  :error-messages="validationErr.client_deadline"
                ></v-text-field>
                <v-date-picker 
                v-model="client_deadline"
                  @input="client_deadline_formatted = formatDate($event)" 
                  no-title 
                  scrollable 
                  actions>
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

            <v-flex xs11 sm6>
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
                  label="Дедлайн в цеху"
                  v-model="workers_deadline_formatted"
                  prepend-icon="event"
                  @blur="workers_deadline = parseDate(workers_deadline_formatted)"
                  :error="validationErr.workers_deadline && validationErr.workers_deadline.length > 0"
                  :error-messages="validationErr.workers_deadline"
                  required
                ></v-text-field>
                <v-date-picker v-model="workers_deadline" @input="workers_deadline_formatted = formatDate($event)" no-title scrollable actions>
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
                label="Гарантийный случай"
                v-model="warranty_case"
              ></v-checkbox>
            </v-flex>

            <v-flex xs12 v-if="warranty_case === false">
              <v-checkbox
                label="С документами"
                v-model="is_with_docs"
              ></v-checkbox>
            </v-flex>

            <v-flex xs12 v-if="warranty_case === false && is_with_docs === true"
            >
              <v-select
                label="Налог"
                placeholder="Выбрать налог"
                v-model="tax_type"
                cache-items
                :items="taxes"
                item-text="name"
                item-value="id"
                :error="validationErr.tax && validationErr.tax.length > 0"
                :error-messages="validationErr.tax"
              ></v-select>
            </v-flex>

            <v-flex xs10 v-if="tax_type === 3">
              <v-text-field
                label="% налога"
                v-model="tax"
                required
              ></v-text-field>
            </v-flex>

            <v-flex xs12 v-if="warranty_case === false">
              <v-checkbox
                label="С партнерскими отчислениями"
                v-model="partner"
              ></v-checkbox>
            </v-flex>

            <v-flex xs12 v-if="partner && warranty_case === false">
              <v-text-field
                label="Сумма партнерских отчислений"
                v-model="partner_payment"
                :error="validationErr.partner_payment && validationErr.partner_payment.length > 0"
                :errors-messages="validationErr.partner_payment"
              ></v-text-field>
            </v-flex>
            <v-flex xs12 v-if="partner && warranty_case === false">
              <v-text-field
                label="Заметки по партнеру"
                v-model="partner_notes"
                multi-line
              ></v-text-field>
            </v-flex>

            
            <v-flex xs12>
              <v-text-field
                label="Заметки"
                v-model="notes"
                multi-line
              ></v-text-field>
            </v-flex>

          </v-layout>
        </v-flex>
      </v-layout>
      <v-layout row wrap>
      </v-layout>
    </v-container>

    <v-btn @click="submit" :disabled="creating">Создать</v-btn>
    <v-btn @click="clear">Очистить</v-btn>
  </form>
</template>

<script>
import { validationMixin } from 'vuelidate'
import { required, maxLength, numeric } from 'vuelidate/lib/validators'
import axios from 'axios'
import { Form } from 'vform';
import {mapGetters} from 'vuex'
import ProposalWare from '~/pages/proposal/proposal-ware'
import AdditionalService from '~/pages/proposal/proposal-additional-service'

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

  validations() {
    return {
      select: {
        required,
      },
      form: {
        ware_id: {
          required,
        },
        count: {
          required,
        },
        client_id: {
          required,
        },
      }
    };
    
  },

  components: {
    'proposal-ware-view': ProposalWare,
    'additional-service-view': AdditionalService,
  },

  data () {
    return {
      object: null,
      client: null,
      client_phone: null,
      is_hot: false,
      tax: null,
      workers_deadline: null,
      workers_deadline_formatted: null,
      client_deadline: null,
      client_deadline_formatted: null,
      warranty_case: false,
      notes: '',

      is_with_docs: true,
      tax_type: null,

      partner: false,
      partner_payment: null,
      partner_notes: null,

      workers_deadline_menu: false,
      client_deadline_menu: false,

      workers_deadline_availablity: false,

      wares: [],

      loadingClientSearch: false,
      loadingObjectSearch: false,

      searchClient: '',
      searchObject: '',

      clients: [],
      objects: [],
      
      newClientDialog: false,
      newObjectDialog: false,

      taxes: [],

      proposalWaresList: [],

      proposalTotal: 0,
      proposalWaresTotal: 0,

      /**
       * if true - show additional services adding form
       * @type {Boolean}
       */
      additionalServices: false,

      additionalServicesList: [],

      valid: true,

      /**
       * Watch for creating proposal
       * @type {Boolean}
       */
      creating: false,

      /**
       * object to show validationErr
       * @type {Object}
       */
      validationErr: {},

      /**
       * Loading
       * @type {Boolean}
       */
      loading: false,
    }
  },
  methods: {
    submit () {
      this.loading = true
      this.creating = true
      let wares = [];
      let addS = []; // additional servies to push
      this.proposalWaresList.forEach(item => {
        if (item.is_show) {
          wares.push(item)
        }
      })

      // show only additonal services
      this.additionalServicesList.forEach(item => {
        if (item.is_show) {
          addS.push(item)
        }
      })

      let postObject = {
        object: this.object,
        client: this.client,
        client_phone: this.client_phone,
        is_hot: this.is_hot,
        tax: this.tax,
        workers_deadline: this.workers_deadline,
        workers_deadline_formatted: this.workers_deadline_formatted,
        client_deadline: this.client_deadline,
        client_deadline_formatted: this.client_deadline_formatted,
        warranty_case: this.warranty_case,
        notes: this.notes,
        wares: wares,
        additional_services: addS,
        partner: this.partner,
        partner_payment: this.partner_payment,
        partner_notes: this.partner_notes,
        tax_type: this.tax_type,
        is_with_docs: this.is_with_docs,
      }
      axios.post('/api/proposal/proposal', postObject).then(response => {
        this.loading = false
        if (response.data.status === 'validator_errors') {
          this.creating = false;
          this.validationErr = response.data.errors;
          this.$store.dispatch('responseMessage', {
            type: 'error',
            text: 'Введены неполноценные данные',
            modal: false
          })
        } else {
          this.$emit('new-proposal', response.data)
          // this.$store.dispatch('responseMessage', {
          //   type: 'success',
          //   text: 'Новая заявка создана',
          //   modal: false
          // })
          this.clear()
          this.validationErr = {}; // null validation errors
        }
      })
      .catch(error => {
        this.creating = false
      })
    },
    getData () {
      const url = '/api/proposal/data/creation'

      axios.get(url).then(result => {
        this.wares = result.data.data.wares
        this.taxes = result.data.data.taxes
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
      this.creating = false
      this.object = ''
      this.client = ''
      this.client_phone = ''
      this.ware_id = 0
      this.price_per_count = null
      this.count = 1
      this.color_doesnt_exist = true
      this.color_price = 0
      this.color = ''
      this.is_hot = false
      this.with_docs = true
      this.tax = null
      this.workers_deadline = null
      this.workers_deadline_formatted = null
      this.client_deadline = null
      this.client_deadline_formatted = null
      this.warranty_case = false
      this.notes = ''
      this.partner = false
      this.partner_payment = null
      this.partner_notes = null
      this.proposalTotal = 0
      this.proposalWaresTotal = 0
      this.is_with_docs = true

      this.proposalWaresList = []
      // this.proposalWaresList.length = 0
      // this.addWare()
      this.tax_type = null
    },
    setTaxProcent() {
      console.log(this.taxes)
    },
    addWare() {
      this.proposalWaresList.push({
        ware_id: 0,
        price_per_count: null,
        count: 1,
        color_doesnt_exist: true,
        color_price: 0,
        color: '',
        is_show: true,
        partner_free: true,
        partner: 0,
        partner_notes: ''
      })
    },

    setProposalWareID(val, id) {
      this.proposalWaresList[id].ware_id = val
      this.proposalTotalCalculate()
    },

    setProposalWareCount(val, id) {
      // this.proposalWaresList[id].count = val
      this.updateSomeProperty(id, 'count', val)
      this.proposalTotalCalculate()
    },

    setProposalWareColorPrice(val, id) {
      this.proposalWaresList[id].color_price = val
      this.proposalTotalCalculate()
    },

    setProposalWarePricePerCount(val, id) {
      this.proposalWaresList[id].price_per_count = val
      this.proposalTotalCalculate()
    },

    setProposalWareColor(val, id) {
      this.proposalWaresList[id].color = val
      this.proposalTotalCalculate()
    },

    setProposalWareColorDoesntExist(val, id) {
      this.proposalWaresList[id].color_doesnt_exist = val
      this.proposalTotalCalculate()
    },

    deleteProposalWare(id) {
      this.proposalWaresList[id].is_show = false
      this.proposalTotalCalculate()
    },
    updateSomeProperty(id, prop, newValue) {
      this.proposalWaresList[id][prop] = newValue
    },

    setProposalWarePartner(val, id) {
      this.proposalWaresList[id].partner = val
      this.proposalTotalCalculate()
    },

    setProposalWarePartnerFree(val, id) {
      this.proposalWaresList[id].partner_free = val
      this.proposalTotalCalculate()
    },

    setProposalWarePartnerNotes(val, id) {
      this.proposalWaresList[id].partner_notes = val
    },
    proposalTotalCalculate() {
      let total = 0;
      this.proposalWaresList.forEach(item => {
        if (item.is_show) {
          total += item.price_per_count * item.count
          if (item.color_doesnt_exist === false) {
            total += item.color_price * item.count
          }
          if (item.partner_free === true) {
            total += item.partner * item.count
          }
        }
      })

      // calculate additional services
      if (this.additionalServices === true && this.additionalServicesList.length > 0) {
        this.additionalServicesList.forEach(item => {
          total += Number(item.price)
        })
      }
      this.proposalTotal = total
    },

    addAddService() {
      this.additionalServicesList.push({
        service: '',
        price: 0,
        is_show: true
      })
      this.proposalTotalCalculate()
    },

    deleteAddService(id) {
      this.additionalServicesList[id].is_show = false
      this.proposalTotalCalculate()
    },

    setAdditionalService(val, id) {
      this.additionalServicesList[id].service = val
    },

    setAdditionalServicePrice(val, id) {
      this.additionalServicesList[id].price = val
      this.proposalTotalCalculate()
    },
  },
  computed: {
    nameErrors () {
      const errors = []
      !this.$v.name.maxLength && errors.push('Максимальная длина названия не более 256 символов')
      !this.$v.name.required && errors.push('Поле обязательно для заполнения')
      return errors
    },
    selectErrors () {
      const errors = []
      if (!this.$v.select.$dirty) return errors
      !this.$v.select.required && errors.push('Поле не должно оставаться пустым')
      return errors
    },
    ...mapGetters({
      lastCreatedClient: 'lastCreatedClient',
      lastCreatedObject: 'lastCreatedObject',
    }),
  },
  mounted() {
    this.getData()
    this.addWare()
  },
  watch: {
    tax_type(val) {
      if (val != null) {
        this.tax = this.taxes[val - 1].tax

        // clculte total
      }
    }
  }
}
</script>

<style lang="css" scoped>
</style>