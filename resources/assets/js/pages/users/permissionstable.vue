<template>
<v-container grid-list-xl>
    <v-layout column>
      <v-flex>
        <v-data-table
          :headers="headers"
          :items="items"
          hide-actions
          item-key="name"
        >
        <template slot="items" slot-scope="props">
          <tr @click="selectRow(props.item, props)">
            <td >{{ props.item.name }}</td>
            <td >{{ props.item.email }}</td>
          </tr>
        </template>
        <template slot="expand" slot-scope="props">
          <v-container grid-list-md text-xs-center>
            <v-card flat v-if="permissions.length > 0" >
              <progress-bar :show="busy"></progress-bar>
              {{ editUser.name }}
              <p>
                <v-btn
                  @click="revokeAccess"
                  >Уволить {{ editUser.name }}
                </v-btn>
                <v-dialog v-model="changePasswordDialog" persistent max-width="700px" >
                  <v-btn slot="activator" dark>Изменить пароль</v-btn>
                  <v-card>
                    <v-card-title>
                      <span class="headline">Изменить пароль для пользователя {{ editUser.name }}</span>
                    </v-card-title>
                    <v-card-text>
                      <v-text-field
                        label="Новый пароль"
                        v-model="newPassword"
                        prepend-icon="lock"
                      ></v-text-field>
                    </v-card-text>
                    <v-card-actions>
                      <v-btn @click="changePassword">Сменить пароль</v-btn>
                      <v-spacer></v-spacer>
                      <v-btn color="blue darken-1" flat @click="changePasswordDialog = false">Закрыть</v-btn>
                    </v-card-actions>
                  </v-card>
                </v-dialog>
              </p>
              <v-container grid-list-md text-xs-center>
                <v-layout row wrap>
                  <v-flex xs12 v-for="permission in permissions" :data="permission" :key="permission.id">
                    <v-layout row wrap>
                      <v-flex xs8>
                        <v-checkbox
                          :label="permission.description"
                          v-model="permission.can"
                        ></v-checkbox>
                      </v-flex>
                      <v-flex xs6 sm4>
                        <v-btn
                          @click="grantPermission(permission)"
                          >Изменить доступы</v-btn>
                      </v-flex>
                    </v-layout>
                  </v-flex>
                </v-layout>
              </v-container>
            </v-card>
          </v-container>
        </template>
      </v-data-table>
    </v-flex>
  </v-layout>
</v-container>
</template>

<script>
import axios from 'axios';

/**
 * Vuestify based data table
 * On user click, we can see list of all permissions and can concrete this user do it
 */
export default {

  name: 'permissions-table',

  data () {
    return {
      checkbox: true,
      headers: [
          {
            text: 'Имя',
            align: 'left',
            value: 'name'
          },
          {
            text: 'Email',
            value: 'email',
          },

        ],
      busy: false,
      items: [],
      permissions: [],
      editUser: {},
      changePasswordDialog: false,
      newPassword: null,
    }
  },

  methods: {
    getUsers() {
      axios.get('/api/owner/user/data')
        .then(response => {
          let users = response.data; // get all users
          users.forEach(user => {
            user.permissions = user.permissionsArray; // replace permissions
          })
          this.items = users;
        })
    },
    selectRow(item, props) {
      props.expanded = !props.expanded
      this.editUser = item
      this.permissions.forEach((permission) => {
        // console.log('can_use', this.canUsePermission(permission))
        permission.can = this.canUsePermission(permission.id)
      })
    },
    getPermissions() {
      axios.get('/api/owner/user/permissions')
        .then(response => {
          this.permissions = response.data
        });
    },
    canUsePermission(permission) {
      const editUserPermissions = [];
      // console.log('editUserPermissions', editUserPermissions)
      const canUse = false;
      this.editUser.permissions.forEach( (editUserPermission) => {
        // console.log('Pushed to array ep', editUserPermission.id)
        editUserPermissions.push(editUserPermission.id)
      })
      // console.log(editUserPermission.id === permission.id);
      if (editUserPermissions.indexOf(permission) !== -1) {
        return true;
      } else {
        return false;
      }
    },
    grantPermission(permission) {
      console.log('permission_to_grant', permission)

      const url = '/api/owner/user/grant-permission';
      axios.post(url, {
        user_id: this.editUser.id,
        permission: permission.name
      }).then(response => {
        this.getData();
        console.log(response)
      })
    },

    getData() {
      this.getUsers();
      this.getPermissions();
      console.log('getting data')
    },

    revokeAccess() {
      const userToDeleteID = this.editUser.id;
      const url = '/api/owner/user/revoke-access';

      axios.post(url, {
        user_id: userToDeleteID
      }).then(response => {
        this.getData();
        this.$store.dispatch('responseMessage', {
          type: 'error',
          text: 'Пользователь уволен',
          title: 'Пользователь уволен',
          modal: false
        })
      })
    },

    changePassword() {
      const url = '/api/owner/user/change-password';
      const editUser = this.editUser;

      axios.post(url, {
        user_id: editUser.id,
        password: this.newPassword,
      }).then(response => {
        this.newPassword = null
        this.changePasswordDialog = false

        this.$store.dispatch('responseMessage', {
          type: 'error',
          text: 'Пароль пользователя ' + editUser.name + ' успешно изменен',
          title: 'Пароль пользователя ' + editUser.name + ' успешно изменен',
          modal: false
        })
      })
    }
  },

  mounted() {
    this.getData()
  }
}
</script>

<style lang="css" scoped>
</style>