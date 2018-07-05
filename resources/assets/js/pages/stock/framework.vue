<template>
  <v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-btn color="primary" dark @click.stop="newFrameworkDialog = true">Новая основа</v-btn>
          <v-dialog v-model="newFrameworkDialog" max-width="500px">
            <v-card>
              <v-card-title>
                <span>
                  Новая основа
                </span>
                <v-spacer></v-spacer>
                <v-menu bottom left>
                  <v-btn @click="newFrameworkDialog = false" icon slot="activator">
                    <v-icon>close</v-icon>
                  </v-btn>
                </v-menu>
              </v-card-title>
              <v-card-text>
                <new-framework-view @new-framework="onCreate"></new-framework-view>
              </v-card-text>
            <v-card-actions>
              <v-btn color="primary" flat @click.stop="newFrameworkDialog=false">Закрыть</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
      </v-flex>

      <v-flex>
        <!-- table with all frameworks -->
        <v-data-table
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
          <template slot="expand" slot-scope="props">
            <v-card flat>
              <v-container grid-list-md text-xs-center>
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
import NewFramework from '~/pages/stock/create/framework'
import { mapGetters } from 'vuex';

/**
 * Страница для отображения всех наименований основ (чиcтых, которые поступают на склад)
 */
export default {

  name: 'framework',

  components: {
    'new-framework-view': NewFramework,
  },

  computed: mapGetters({
    user: 'authUser'
  }),

  data () {
      return {
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
        newFrameworkDialog: false,
      }
  },

  methods: {
    getFrameworks() {
      const url = '/api/stock-data/frameworks'
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
      const url = 'api/stock/framework/' + this.updateForm.id
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

    onCreate() {
      this.newFrameworkDialog = false;
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Новая основа создана',
        modal: false
      })
    }
  },

  beforeCreate: () => {
    const url = '/api/stock-data/frameworks'

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