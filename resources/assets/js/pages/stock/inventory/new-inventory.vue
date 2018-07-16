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
                ></v-select>
              </v-flex>
              <!-- end component select -->

              <!-- expected rests -->
              <v-flex xs12>
                <headline>Ожидаемые остатки</headline>
              </v-flex>
              <!-- end expected rests -->
              <v-flex xs12>
                <v-text-field label="Password" type="password" required></v-text-field>
              </v-flex>
              <v-flex xs12 sm6>
                <v-select
                  label="Age"
                  required
                  :items="['0-17', '18-29', '30-54', '54+']"
                ></v-select>
              </v-flex>
              <v-flex xs12 sm6>
                <v-select
                  label="Interests"
                  multiple
                  autocomplete
                  chips
                  :items="['Skiing', 'Ice hockey', 'Soccer', 'Basketball', 'Hockey', 'Reading', 'Writing', 'Coding', 'Basejump']"
                ></v-select>
              </v-flex>
            </v-layout>
          </v-container>
          <small>*indicates required field</small>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" flat @click.native="dialog = false">Закрыть</v-btn>
          <v-btn color="blue darken-1" flat @click.native="dialog = false">Сохранить</v-btn>
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
        },

        componentsList: [],
    };
  },

  methods: {
        getData() {
            axios.get('/api/inventory/data')
                .then(response => {
                    this.data = response.data.data;
                    this.types = response.data.types;
                })
        }
    },

    mounted() {
        this.getData();
    },
}
</script>

<style lang="css" scoped>
</style>