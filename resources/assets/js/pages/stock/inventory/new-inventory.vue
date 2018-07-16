<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" persistent max-width="600px">
      <v-btn color="primary" dark slot="activator">Новая инвентаризация</v-btn>
      <v-card>
        <v-card-title>
          <span class="headline">Новая инвентаризация</span>
          <v-spacer></v-spacer>
        <v-menu bottom left>
          <v-btn @click="dialog = false" icon slot="activator">
            <v-icon>close</v-icon>
          </v-btn>
        </v-menu>
        </v-card-title>
        <v-card-text>
          <v-container grid-list-md>
            <v-layout wrap>
              <v-flex xs12 sm6 md4>
                <v-select
                  v-bind:items="types"
                  label="Тип компонента"
                  single-line
                  bottom
                  item-text="name"
                  item-value="key"
                  :error="validationErr.component_type && validationErr.component_type.length > 0"
                  :error-messages="validationErr.component_type"
                  v-model="inventory_data.component_type"
                ></v-select>
              </v-flex>

              <!-- component id select -->
              <v-flex xs12 sm6 md4>
                <v-select
                  v-if="inventory_data.component_type.slug === 'rest_frameworks'"
                  v-bind:items="data.rest_frameworks"
                  v-model="inventory_data.component_id"
                  label="Основы"
                  single-line
                  bottom
                  item-text="name"
                  item-value="key"
                  :error="validationErr.component_id && validationErr.component_id.length > 0"
                  :error-messages="validationErr.component_id"
                ></v-select>

                <v-select
                  v-if="inventory_data.component_type.slug === 'stickers'"
                  v-bind:items="data.stickers"
                  v-model="inventory_data.component_id"
                  label="Наклейки"
                  single-line
                  bottom
                  item-text="name"
                  item-value="key"
                  :error="validationErr.component_id && validationErr.component_id.length > 0"
                  :error-messages="validationErr.component_id"
                ></v-select>

                <v-select
                  v-if="inventory_data.component_type.slug === 'packagings'"
                  v-bind:items="data.packagings"
                  v-model="inventory_data.component_id"
                  label="Упаковки"
                  single-line
                  bottom
                  item-text="name"
                  item-value="key"
                  :error="validationErr.component_id && validationErr.component_id.length > 0"
                  :error-messages="validationErr.component_id"
                ></v-select>
              </v-flex>
              <!-- end component select -->

              <br>
              <br>

              <!-- expected rests -->
              <v-flex xs12>
                <headline>Ожидаемые остатки</headline>
              </v-flex>
              <v-flex sm6>
                <v-text-field 
                  label="Количество" 
                  type="number" 
                  v-model="inventory_data.expected_rests" 
                  :error="validationErr.expected_rests && validationErr.expected_rests.length > 0"
                  :error-messages="validationErr.expected_rests"
                  required></v-text-field>
              </v-flex>
               <v-flex sm6>
                <v-text-field 
                  label="Сумма" 
                  type="number" 
                  v-model="inventory_data.expected_sum" 
                  :error="validationErr.expected_sum && validationErr.expected_sum.length > 0"
                  :error-messages="validationErr.expected_sum"
                  required></v-text-field>
              </v-flex>
              <!-- end expected rests -->

              <!-- real rests -->
              <v-flex xs12>
                <headline>Реальные остатки остатки</headline>
              </v-flex>
              <v-flex sm6>
                <v-text-field 
                  label="Количество" 
                  type="number"
                  v-model="inventory_data.real_rest" 
                  :error="validationErr.real_rest && validationErr.real_rest.length > 0"
                  :error-messages="validationErr.real_rest"
                  required></v-text-field>
              </v-flex>
               <v-flex sm6>
                <v-text-field 
                  label="Сумма" 
                  type="number"
                  :error="validationErr.real_sum && validationErr.real_sum.length > 0"
                  :error-messages="validationErr.real_sum"
                  required 
                  v-model="inventory_data.real_sum"></v-text-field>
              </v-flex>
              <!-- end reald rests -->
            </v-layout>
          </v-container>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" flat @click.native="dialog = false">Закрыть</v-btn>
          <v-btn color="blue darken-1" flat @click.native="submit()">Сохранить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
import axios from 'axios';

/**
 * Component to make in dialog new inventory
 */
export default {

  name: 'new-inventory',

  data () {
    return {
        dialog: false,
        data: {
            rest_frameworks: [],
            packagings: [],
            stickers: [],
        },
        types: [],

        inventory_data: {
            component_type: '',
            component_id: '',
            expected_rests: null,
            expected_sum: null,
            real_rest: null,
            real_sum: null,
        },

        componentsList: [],

        validationErr: {}
    };
  },

  methods: {
        getData() {
            axios.get('/api/inventory/data')
                .then(response => {
                    this.data = response.data.data;
                    this.types = response.data.types;
                })
        },

        submit() {
          this.$store.dispatch('setLoading', {
            loading: true
          });

          axios.post('/api/inventory', {
            component_type: this.inventory_data.component_type.slug,
            component_id: this.inventory_data.component_id.id,
            expected_rests: this.inventory_data.expected_rests,
            expected_sum: this.inventory_data.expected_sum,
            real_rest: this.inventory_data.real_rest,
            real_sum: this.inventory_data.real_sum,
          }).then(response => {
            // disable loading
            this.$store.dispatch('setLoading', {
              loading: false
            });

            if (response.data.status === 'validator_errors') {
              this.$store.dispatch('responseMessage', {
                type: 'error',
                text: 'Ошибки валидации',
                modal: false,
              });
              this.validationErr = response.data.errors;
            } else {
              // inventory is successfull
              this.$store.dispatch('responseMessage', {
                type: 'success',
                text: 'Проведена инвентаризация',
                modal: false
              });

              // clear object
              this.inventory_data = {
                component_type: '',
                component_id: '',
                expected_rests: null,
                expected_sum: null,
                real_rest: null,
                real_sum: null,
              };
              this.dialog = false;
              this.validationErr = {};
            }
            
          });
        }
    },

    mounted() {
        this.getData();
    },
}
</script>

<style lang="css" scoped>
</style>