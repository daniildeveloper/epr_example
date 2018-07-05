<template>
  <v-container grid-list-md>
    <v-layout row wrap>
      <v-flex xs12 v-if="hasRole('owner')">
        <v-btn @click="changeStatus(id, 9)">Отменить заявку</v-btn>
      </v-flex>

      <v-flex xs12 v-if="(status === 4 || status === 2) && hasPermission('unallow_proposals')">
        <v-btn @click="changeStatus(id, 9)">Подтвердить отмену заявки</v-btn>
      </v-flex>

      <!-- statuses list to change -->
      <v-flex xs12>
        <v-layout row wrap>
          <v-flex v-for="s in statuses" v-if="s.id > status" :key="s.id" :data="s">
            <v-btn @click="changeStatus(id, s.id)">{{ s.name }}</v-btn>
          </v-flex>
        </v-layout>
      </v-flex>
      <!-- end statuses list -->
      <v-flex xs12>
        <h2 class="headline">Заявка # {{ id }} от {{ created_at }}</h2>
        <p class="subheading">Код заявки: {{ code }}</p>
        <p class="subheading" v-if="object">Объект: {{ object.address }}</p>
        <p class="subheading">Клиент: {{ client }}({{ client_phone }})</p>
        <p>Должно быть готово в цеху: {{ workers_deadline }}</p>
        <p>Должно быть у клиента: {{ client_deadline }}</p>
        <br>
        <div v-if="status === 2">
          <p class="subheading">Причина отклонения:</p>
          <div>
            {{ argument.argument }}
          </div>
        </div>
        <div v-if="status === 1 || status === 2">
          <v-btn @click.stop="openDeadlineChangeDialog">Изменить сроки</v-btn>
          <v-dialog v-model="proposalDeadlineChange" max-width="500px">
            <v-card>
              <v-card-title>
                Изменить сроки
              </v-card-title>
              <v-card-text>
                <v-layout>
                  <v-flex xs12>
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
                        v-model="workers_deadline_formatted"
                        prepend-icon="event"
                        @blur="workers_deadline = parseDate(workers_deadline_formatted)"
                      ></v-text-field>
                      <v-date-picker locale="ru" v-model="workers_deadline" @input="workers_deadline_formatted = formatDate($event)" no-title scrollable actions>
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
                      ></v-text-field>
                      <v-date-picker v-model="client_deadline" @input="client_deadline_formatted = formatDate($event)" no-title scrollable actions>
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
                </v-layout>
              </v-card-text>
              <v-card-actions>
                <v-btn color="primary" flat @click.stop="closeDeadlineChangeDialog">Закрыть</v-btn>
                <v-btn color="primary" flat @click.stop="deadlineChange">Сохранить изменения</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </div>
        <p class="headline">Товары:</p>
        <v-data-table
          :headers="headers"
          :items="wares"
          hide-actions
          item-key="name"
          v-if="wares.length > 0"
        >
          <template slot="items" slot-scope="props">
            <tr>
              <td >{{ props.item.ware.name }}</td>
              <td class="text-xs-right">{{ props.item.count }}</td>
              <td class="text-xs-right">{{ props.item.price_per_count }}</td>
              <td>{{ props.item.color }}</td>
              <td>{{ props.item.color_price }}</td>
            </tr>
          </template>
        </v-data-table>

      </v-flex>
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
    </v-layout>z
  </v-container>
</template>

<script>
import axios from 'axios';
import {mapGetters} from 'vuex';

export default {

  name: 'single-proposal',

  props: [
    'id',
    'object',
    'client',
    'client_phone',
    'wares',
    'color_doesnt_exist',
    'color_price',
    'color',
    'is_hot',
    'with_docs',
    'tax',
    'workers_deadline',
    'workers_deadline_formatted',
    'client_deadline',
    'client_deadline_formatted',
    'code',
    'created_at',
    'status',
    'argument',
    'statuses',
    'blocks',
    'notes',
  ],

  data () {
    return {
      headers: [
        {
          text: 'Название товара',
          align: 'left',
          value: 'ware.ware.name'
        },
        { text: 'Количество', value: 'ware.ware.count' },
        { text: 'Цена', value: 'ware.ware.price' },
        {
          text: 'Цвет',
          value: 'ware.ware.color',
        },
        {
          text: 'Цена цвета',
          value: 'ware.color_price'
        },
      ],
      workers_deadline_menu: false,
      client_deadline_menu: false,
      proposalDeadlineChange: false,

      // old data to backup deadline change
      workers_deadline_old: null,
      workers_deadline_formatted_old: null,
      client_deadline_old: null,
      client_deadline_formatted_old: null,

      statusToChangeId: null,
      notesToChange: null,

      permissedStages: [], // stages with permissions foreach
    }
  },

  computed: mapGetters({
    user: 'authUser',
    message: 'responseMessage'
  }),

  methods: {
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
    /**
     * Opens deadline change dialog.
     * Setup old values for deadlines.
     * @return {[type]} [description]
     */
    openDeadlineChangeDialog() {
      this.proposalDeadlineChange = true;
      this.workers_deadline_old = this.workers_deadline;
      this.client_deadline_old = this.client_deadline;
      this.workers_deadline_formatted_old = this.workers_deadline_formatted;
      this.client_deadline_formatted_old = this.client_deadline_formatted;
    },

    deadlineChange() {
      const url = '/api/proposal/proposal/change-deadline';

      axios.post(url, {
        proposal_id: this.id,
        client_deadline: this.client_deadline,
        workers_deadline: this.workers_deadline,
      }).then(response => {
        this.$emit('proposal-deadline-change', this.id)
        this.workers_deadline_old = null;
        this.client_deadline_old = null;
        this.workers_deadline_formatted_old = null;
        this.client_deadline_formatted_old = null;
        this.proposalDeadlineChange = false;

        // show snackbar
        this.color = 'success';
        this.text = 'Сроки для заявки ' + this.code + ' успешно изменены';
        this.snackbar = true;

        this.$store.dispatch('responseMessage', {
          type: 'error',
          text: 'Сроки для заявки ' + this.code + ' успешно изменены',
          modal: false
        })

      })
    },

    closeDeadlineChangeDialog() {
      this.workers_deadline = this.workers_deadline_old;
      this.client_deadline = this.client_deadline_old;
      this.workers_deadline_formatted = this.workers_deadline_formatted_old;
      this.client_deadline_formatted = this.client_deadline_formatted_old;
      this.proposalDeadlineChange = false;
    },

    updateBlock (id, status) {
      status = Number(status)
      this.blocks.find(b => b.id === Number(id)).status = status

      if (status === 2 || status === 3 || status === 4 || status === 5 || status === 6)  {
        if (this.hasPermission('confirm_proposals')) {
          this.changeStatus(id, status)
        } else {
          this.noRoots()
        }
      }

      if (status === 9) {
        if (this.hasPermission('unallow_proposals')) {
          // this.changeStatus(id, status)
          console.log('try to unallow proposal')
        } else {
          this.noRoots()
        }
      }
    },
    async updateUnallowedBlockSave() {
      const {data} = await this.unallowedForm.post('/api/proposal/proposal/argument')
      this.getProposalData()
      this.un_allowed_dialog = false
    },
    changeStatus(proposalID, statusID) {
      if ((statusID === 9) && !this.hasPermission('unallow_proposals')) {
        this.$store.dispatch('responseMessage', {
          type: 'error',
          text: 'Недостаточно прав',
          title: "Недостаточно прав",
          modal: false
        });
        return false;
      }
      axios.post('/api/proposal/proposal/change-status', {
        proposal_id: proposalID,
        status_id: statusID, 
      }).then(response => {
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Статус заявки изменен',
          title: "Статус заявки изменен",
          modal: false
        })
        this.$emit('proposal-status-change', this.id, statusID)
        this.$emit('proposal-single-window-close')
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
    getProposalData () {
      const getactiveProposalsData = '/api/proposal/proposal';
      const getStages = '/api/proposal/data/stages';

      axios.get(getactiveProposalsData).then(response => {
        this.blocks = response.data;
      });
      axios.get(getStages).then(res => {
        this.stages = res.data;
      })
    },
    noRoots() {
      this.$store.dispatch('responseMessage', {
        type: 'error',
        text: 'Нет прав',
        title: "Нет доступа",
        modal: true
      })
      this.getProposalData()
    },

    /**
     * Change comments\notes to proposal. Single field only
     * @return {[type]} [description]
     */
    changeProposalComment() {
      // change proposal comment
      axios.post('/api/proposal/proposal/notes-update/' + this.id, {
        notes: this.notesToChange,
      }).then(response => {
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Заметки к заявке ' + this.code + ' изменены',
          title: 'Заметки к заявке ' + this.code + ' изменены',
          modal: false
        });
      });
    },

    filterStages() {
      this.statuses.forEach(stage => {
        const stageID = stage.id
        if (stageID === 1 || stageID === 2 || stageID === 4 ) {
          this.permissedStages.push(stage);
        }

        if ((stageID === 3 || stageID === 5 || stageID === 6 || stageID === 7) && this.hasPermission('confirm_proposals') ) {
          this.permissedStages.push(stage);
        }

        if (stageID === 9 && this.hasPermission('unallow_proposals')) {
          this.permissedStages.push(stage);
        }
      })
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
    this.filterStages();
    this.statusToChangeId = this.status;
    this.notesToChange = this.notes;
  },

  watch: {
    statusToChangeId(val) {
      if (val != this.status) {
        console.log('proposal #' + this.id + ' is changed to status #' + val)
        console.log('Staus to change ' + val + 'status default ' + this.status )
        this.updateBlock(this.id, val)
      }
    }
  }
}
</script>

<style lang="css" scoped>
</style>