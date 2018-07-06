<template>
  <form>
    <v-text-field
      label="E-mail"
      v-model="form.email"
      :error-messages="emailErrors"
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
import { validationMixin } from 'vuelidate'
import { required, maxLength, email } from 'vuelidate/lib/validators'
import Form from 'vform'
import axios from 'axios'

export default {

  name: 'user-invite',

  mixins: [validationMixin],

  validations: {
    email: { required, email }
  },

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
    async submit () {
      this.$v.$touch()
      const {data} = await this.form.post('/api/user-invite')
      this.$emit('user-invite-completed');
    },
    clear () {
      this.$v.$reset()
      this.form.email = ''
      this.form.role = ''
    },
    getUserRoles() {
      const url = '/api/user/roles';
      axios.get(url).then(response => {
        this.roles = response.data;
      })
    }
  },

   computed: {
    emailErrors () {
      const errors = []
      if (!this.$v.email.$dirty) return errors
      !this.$v.email.email && errors.push('E-mail должен быть валидным')
      !this.$v.email.required && errors.push('E-mail необходим')
      return errors
    }
  },
  mounted() {
    this.getUserRoles()
  }
}
</script>

<style lang="css" scoped>
</style>