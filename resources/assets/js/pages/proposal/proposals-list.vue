<template>
   <v-card>
    <v-card-title>
      <v-spacer></v-spacer>
      <!-- <v-text-field
        append-icon="search"
        label="Поиск"
        single-line
        hide-details
        v-model="search"
      ></v-text-field> -->
    </v-card-title>

    <v-layout row wrap>
      <v-flex xs12>
        <v-btn @click="getData()">Все заявки</v-btn>
        <v-btn v-for="sortable in sortables" @click="sort(sortable.slug)" :key="sortable.slug" :data="sortable">{{sortable.name}}</v-btn>
      </v-flex>

      <v-flex xs12>
        <v-data-table
        no-data-text="Нет данных"
        v-bind:headers="headers"
        v-bind:items="items"
        v-bind:search="search"
        hide-actions
        item-key="id"
          >
          <template slot="items" slot-scope="props">
            <tr @click="selectRow(props.item, props)">
              <td class="text-xs-right">{{ props.item.id }}</td>
              <td class="text-xs-right">{{ props.item.code }}</td>
              <td class="text-xs-right">{{ props.item.client }}</td>
              <td class="text-xs-right">{{ props.item.client_phone }}</td>
              <td class="text-xs-right">{{ props.item.object != null ? props.item.object : '' }}</td>
              <td class="text-xs-right">{{ props.item.created_at }}</td>
              <td class="text-xs-right">{{ props.item.creator.name }}</td>
              <td class="text-xs-right">{{ props.item.status.name }}</td>
              <td class="text-xs-right">{{ props.item.closed ? 'Расчитана' : "" }}</td>
            </tr>
          </template>
          <template slot="expand" slot-scope="props">
            <v-card flat>
              <v-container grid-list-md>
                <v-layout row wrap>
                  <v-flex xs12 sm6>
                    <progress-bar :show="busy"></progress-bar>
                    <form @submit.prevent="update">

                      <v-card-title primary-title>
                        <h4 class="headline mb-0">Заявка #{{ props.item.id }}. Код {{ props.item.code }}</h4>
                      </v-card-title>

                      <v-card-text>
                        <v-flex xs12 v-if="hasRole('owner') && props.item.status_id <= 7">
                          <v-btn @click="changeStatus(props.item.id, 9)">Отменить заявку</v-btn>
                        </v-flex>

                        <v-flex xs12 v-if="(props.item.status_id === 4 || props.item.status_id === 2) && hasPermission('unallow_proposals')">
                          <v-btn @click="changeStatus(props.item.id, 9)">Подтвердить отмену заявки</v-btn>
                        </v-flex>

                         <!-- statuses list to change -->
                        <v-flex xs12>
                          <v-layout row wrap>
                            <v-flex v-for="s in statuses" :key="s.id" :data="s" v-if="s.id > props.item.status_id">
                              <v-btn @click="changeStatus(props.item.id, s.id)">{{ s.name }}</v-btn>
                            </v-flex>
                          </v-layout>
                        </v-flex>
                        <!-- end statuses list -->
                        <p class="subheading" v-if="props.item.object">Объект: {{ props.item.object.address }}</p>
                        <p class="subheading">Клиент: {{ props.item.client }}({{ props.item.client_phone }})</p>
                        <p>Должно быть готово в цеху: {{ props.item.workers_deadline }}</p>
                        <p>Должно быть у клиента: {{ props.item.client_deadline }}</p>
                        <p>Статус: {{ props.item.status.name }}</p>
                        <br>
                        <div v-if="props.item.status_id === 2">
                          <p class="subheading">Причина отклонения:</p>
                          <div>
                            {{ props.item.argument.argument }}
                          </div>
                        </div>
                        <div class="material-table">
                            <div class="material-table-header"></div>
                            <div class="material-table-body">
                            <table cellspacing="0" class="datatable table">
                              <thead>
                                <tr>
                                  <th>
                                    <div>Название товара</div>
                                  </th>
                                  <th>
                                    <div>Количество</div>
                                  </th>
                                  <th>
                                    <div>Цена за единицу</div>   
                                  </th>
                                  <th>
                                    <div>Цвет</div>   
                                  </th>
                                  <th>
                                    <div>Цена цвета</div>   
                                  </th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr v-for="ware in props.item.wares">
                                  <td >{{ ware.ware.name }}</td>
                                  <td class="text-xs-right">{{ ware.count }}</td>
                                  <td class="text-xs-right">{{ ware.price_per_count }}</td>
                                  <td>{{ ware.color }}</td>
                                  <td>{{ ware.color_price }}</td>
                                </tr>
                              </tbody>
                              </table>
                            </div>
                        </div>
                        <v-layout row wrap>
                          <v-flex xs12>
                            <v-text-field
                              label="Заметки"
                              v-model="notesToChange"
                              multi-line
                            ></v-text-field>
                          </v-flex>
                          <v-flex xs2>
                            <v-btn @click="changeProposalComment">Изменить заметки</v-btn>
                          </v-flex>
                        </v-layout>
                      </v-card-text>

                    </form>
                  </v-flex>
                </v-layout>
              </v-container>
            </v-card>
          </template>
        </v-data-table>
      </v-flex>
    </v-layout>
    
     <div class="text-xs-center pt-2">
      <v-pagination 
        v-model="pagination.page" 
        :length="pages"
        :total-visible="7"
        @next="getNextPage"
        @previous="getPrevPage"
        @input="getPage"
      ></v-pagination>
    </div>
  </v-card>
</template>

<script>
import axios from 'axios';
import {mapGetters} from 'vuex';

/**
 * Proposals in table view for board alternative
 */
export default {

  name: 'proposals-list',

  computed: mapGetters({
    user: 'authUser',
    message: 'responseMessage'
  }),

  data () {
    return {
      pages: 1,
      search: '',
      pagination: {},
      selected: [],
      tmp: '',
      nextPageUrl: '',
      prevPageUrl: '',
      headers: [
        {
          text: '#',
          align: 'left',
          value: ''
        },
        { text: 'Код', value: 'code' },
        { text: 'Клиент', value: 'client' },
        { text: 'Телефон клиента', value: 'client_phone' },
        { text: 'Объект', value: '' },
        { text: 'Дата создания', value: 'created_at' },
        { text: 'Ответственный', value: 'creator.name' },
        { text: 'Статус', value: 'status.name' },
        { text: 'Расчитана прибыль', value: '' },
      ],
      busy: false,
      items: [],
      expandedID: null, // row expanded. Usefull to change comment or status
      notesToChange: null, // notes by proposal to change
      statuses: [], // list of statuses

      sortables: [
        {
          slug: 'newest',
          name: 'Новые'
        },{
          slug: 'unallowed',
          name: 'Неподтвержденные'
        },{
          slug: 'hot',
          name: 'Срочные'
        },{
          slug: 'warranty_case',
          name: 'Гарантийные'
        }
      ]
    }
  },

  methods: {
    sort(slug) {
      this.$store.dispatch('setLoading', {
        loading: true
      });

      axios.get('/api/proposal/proposal/sort/' + slug)
        .then(response => {
          // save data
          this.items = response.data;

          // disable loading
          this.$store.dispatch('setLoading', {
            loading: false
          });
        })
    },

    /**
     * Gt initial data
     * @return {[type]} [description]
     */
    getData() {
      const url = '/api/proposal/proposal/paginate';

      axios.get(url).then(response => {
        this.items = response.data.data;
        this.pagination.page = response.data.current_page;
        this.pages = response.data.last_page;
        this.nextPageUrl = response.data.next_page_url;
        this.prevPageurl = response.data.prev_page_url;
      })
    },

    /**
     * Get stages
     * @return {[type]} [description]
     */
    getStages() {
      const getStages = '/api/proposal/data/stages';

      axios.get(getStages).then(res => {
        this.statuses = res.data;
      })
    },

    getNextPage() {
      axios.get(this.nextPageUrl)
        .then(response => {
          this.items = response.data.data;
          this.pagination.page = response.data.current_page;
          this.pages = response.data.last_page;
          this.nextPageUrl = response.data.next_page_url;
          this.prevPageUrl = response.data.prev_page_url;
        })
    },
    getPrevPage() {
      axios.get(this.prevPageUrl)
        .then(response => {
          this.items = response.data.data;
          this.pagination.page = response.data.current_page;
          this.pages = response.data.last_page;
          this.nextPageUrl = response.data.next_page_url;
          this.prevPageUrl = response.data.prev_page_url;
        })
    },
    selectRow(item, props) {
      props.expanded = !props.expanded
      this.expandedID = props.item.id
      this.notesToChange = props.item.notes
    },

    /**
     * Change comments\notes to proposal. Single field only
     * @return {[type]} [description]
     */
    changeProposalComment() {
      // change proposal comment
      axios.post('/api/proposal/proposal/notes-update/' + this.expandedID, {
        notes: this.notesToChange,
      }).then(response => {
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Заметки к заявке изменены',
          title: 'Заметки к заявке изменены',
          modal: false
        });
      });
    },

    getPage(id) {
      const url = '/api/proposal/proposal/paginate?page=' + id;

      axios.get(url).then(response => {
        this.items = response.data.data;
        this.pagination.page = response.data.current_page;
        this.pages = response.data.last_page;
        this.nextPageUrl = response.data.next_page_url;
        this.prevPageurl = response.data.prev_page_url;
      })
    },

    changeStatus(proposalID, statusID) {
      console.log(proposalID, statusID)
      axios.post('/api/proposal/proposal/change-status', {
        proposal_id: proposalID,
        status_id: statusID, 
      }).then(response => {
        this.getData()
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

    hasRole(role) {
      let rolesArray = [];

      this.user.data.roles.forEach(r => {
        rolesArray.push(r.name)
      });

      if (rolesArray.indexOf(role) !== -1) {
        return true;
      }

      return false;
    },

  },

  mounted() {
    this.getData();
    this.getStages();
    let that = this;
    window.addEventListener('proposal-created', function() {
      that.getData();
    }, false);

    // listen for proposal status change
    window.addEventListener('proposal-status-changed', function () {
      that.getData();
    }, false);
  }
}
</script>

<style lang="css" scoped>
</style>