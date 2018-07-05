<template>
    <div>
      <v-expansion-panel expand>
        <!-- My tasks -->
        <v-expansion-panel-content>
          <div slot="header">Мои задачи</div>
          <v-card>
            <div class="field has-addons">
                <div class="control is-expanded">
                    <v-text-field
                      label="Новая задача"
                      v-model="task.body"
                      required
                    >
                    </v-text-field>
                    <v-btn @click="createTask()" >Добавить задачу</v-btn>
                </div>
            </div>
            <div class="card" v-for="task in list">
                <v-container grid-list-md text-xs-center>
                    <v-layout row wrap>
                        <v-flex xs4>
                            <v-btn @click.prevent="archiveTask(task.id)">Выполнить</v-btn>
                        </v-flex>
                        <v-flex xs8>
                            <p v-if="task !== editingTask" @dblclick="editTask(task)" v-bind:title="message">
                                {{ task.body }}
                            </p>
                            <v-text-field
                              v-if="task === editingTask" v-autofocus @keyup.enter="endEditing(task)" @blur="endEditing(task)" type="text" placeholder="Редактировать задачу" v-model="task.body"
                            >
                            </v-text-field>
                        </v-flex>
                    </v-layout>
                </v-container>
            </div>
          </v-card>
        </v-expansion-panel-content>
        <!-- end my tasks -->

        <!-- delegated tasks -->
        <v-expansion-panel-content>
          <div slot="header">Порученные задачи</div>
          <v-card>
            <div class="field has-addons">
                <div class="control is-expanded">
                    <v-text-field
                      label="Новая задача"
                      v-model="delegateTask.body"
                      required
                    >
                    </v-text-field>
                    <v-select
                      autocomplete
                      label="Пользователю"
                      v-model="delegateTask.user_id"
                      placeholder="Выбрать пользователя"
                      :items="users"
                      item-text="name"
                      item-value="id"
                      required
                    ></v-select>
                    <v-btn @click="delegateTask()">Поручить</v-btn>
                </div>
            </div>
            <div class="card" v-for="task in list">
                <v-container grid-list-md text-xs-center>
                    <v-layout row wrap center>
                        <v-flex xs4>
                            <v-btn @click.prevent="archiveTask(task.id)">Выполнить</v-btn>
                        </v-flex>
                        <v-flex xs8>
                            <p v-if="task !== editingTask" @dblclick="editTask(task)" v-bind:title="message">
                                {{ task.body }}
                            </p>
                            <v-text-field
                              v-if="task === editingTask" v-autofocus @keyup.enter="endEditing(task)" @blur="endEditing(task)" type="text" placeholder="Редактировать задачу" v-model="task.body"
                            >
                            </v-text-field>
                        </v-flex>
                    </v-layout>
                </v-container>
            </div>
          </v-card>
        </v-expansion-panel-content>
        <!-- delegated tasks -->
      </v-expansion-panel>
    </div>
</template>

<script>
import axios from 'axios'

export default {

  name: 'tasklist',

  directives: {
      'autofocus': {
          inserted(el) {
              el.focus();
          }
      }
  },

  data () {
    return {
      message: 'Двойной клик для редактирования.',
      list: [],
      task: {
          id: '',
          body: '',
          archive: ''
      },
      editingTask: {},
      activeItem: 'current',

      /**
       * Task to delegate to another user
       * @type {Object}
       */
      delegateTask: {
        body: null,
        user_id: null,
      },

      /**
       * Array of user who can get tasks
       * @type {Array}
       */
      users: [],
    }
  },

  created() {
      this.fetchTaskList();
  },

  methods: {
    fetchTaskList(archive = null) {
        if (archive === null) {
            var url = '/api/user/task/current_tasks';
            this.setActive('current');
        } else {
            var url = '/api/user/task/archived_tasks';
            this.setActive('archive');
        }
        axios.get(url).then(result => {
            this.list = result.data
        });
    },
    isActive(menuItem) {
        return this.activeItem === menuItem;
    },
    setActive(menuItem) {
        this.activeItem = menuItem;
    },
    createTask() {
        axios.post('/api/user/task/create_task', this.task).then(result => {
            this.task.body = '';
            this.fetchTaskList();
            this.$store.dispatch('responseMessage', {
              type: 'success',
              text: 'Задача создана',
              modal: false
            })
        }).catch(err => {
            console.log(err);
        });
    },
    editTask(task) {
        this.editingTask = task;
        this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Задача изменена',
          modal: false
        })
    },
    endEditing(task) {
        this.editingTask = {};
        if (task.body.trim() === '') {
            this.deleteTask(task.id);
        } else {
            axios.post('/api/user/task/edit_task', task).then(result => {
                console.log('access!')
            }).catch(err => {
                console.log(err);
            });
        }
    },
    archiveTask(id) {
        axios.post('/api/user/task/archive_task/' + id).then(result => {
            this.fetchTaskList();
            this.$store.dispatch('responseMessage', {
              type: 'success',
              text: 'Задача архивирована',
              modal: false
            })
        }).catch(err => {
            console.log(err);
        });
    },

    /**
     * Deegate task
     * @return {[type]} [description]
     */
    delegateTask() {
      axios.post('/api/user/task/delegate', this.delegateTask)
        .then(response => {
          this.delegateTask.body = null;
          this.delegateTask.user_id = null;
          this.$store.dispatch('responseMessage', {
              type: 'success',
              text: 'Задача делегирована',
              modal: false
            });
        });
    },

    /**
     * Get initial setup data
     * @return {[type]} [description]
     */
    getData() {
      axios.get('/api/user/task/users')
        .then(response => {
          this.users = response.data;
        });
    }
  },

  mounted() {
    this.getData();
  }
}
</script>

<style lang="css" scoped>

</style>