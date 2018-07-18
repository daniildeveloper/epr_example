<template>
  <form>
    <v-text-field
      label="E-mail"
      v-model="form.email"
      required
    ></v-text-field>
    <v-select
        label="Выберите уровень доступа"
        v-model="form.role"
        :items="roles"
        :rules="[v => !!v || 'Необходим']"
        item-text="description"
        item-value="id"
        required
      ></v-select>

    <v-btn @click="submit">Пригласить</v-btn>
    <v-btn @click="clear">Очистить</v-btn>
  </form>
</template>

<script>
import Form from 'vform'
import axios from 'axios'

export default {

  name: 'user-invite',

  data () {
    return {
      roles: [],
      form: new Form({
        email: '',
        role: '',
      })
    }
  },

  methods: {
    submit () {
      this.$store.dispatch('setLoading', {
        loading: true
      });
      this.form.post('/api/user-invite')
        .then(data => {
          this.$store.dispatch('setLoading', {
            loading: true
          });
          this.$emit('user-invite-completed');
          this.clear()
        })
      
    },
    clear () {
      this.form.email = '';
      this.form.role = '';
    },
    getUserRoles() {
      const url = '/api/user/roles';
      axios.get(url).then(response => {
        this.roles = response.data;
      })
    }
  },
  mounted() {
    this.getUserRoles()
  }
}
</script>

<style lang="css" scoped>
</style>