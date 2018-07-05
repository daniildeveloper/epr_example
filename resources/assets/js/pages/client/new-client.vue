<template>
  <form>
    <v-text-field
      label="Имя Фамилия"
      v-model="form.name"
      :rules="nameRules"
      required
    ></v-text-field>

    <v-text-field
      label="Почта"
      v-model="form.email"
      :rules="emailRules"
    ></v-text-field>

    <v-text-field
      v-for="(key, phone) in phones"
      label="Телефон"
      v-model="phones[phone]"
      required
    ></v-text-field>

    <v-btn
      @click="addPhone"
      >Добавить телефон
    </v-btn>

    <v-text-field
      label="Заметки"
      v-model="form.notes"
      multi-line
    ></v-text-field>

    <v-btn @click="submit">Создать</v-btn>

    <v-dialog v-model="clientCreatedDialog" max-width="500px">
      <v-card>
        <v-card-title>
          Клиент создан
        </v-card-title>
        <v-card-text>
          <p>Имя: {{ client.name }}</p>
          <p>Почта: {{ client.email }}</p>
          <p>Телефоны: {{ client.phone }}</p>
          <p>Заметки:</p>
          <p>{{ client.notes }}</p>
        </v-card-text>
      <v-card-actions>
        <v-btn color="primary" flat @click.stop="clientCreatedDialog=false">Закрыть</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </form>
</template>

<script>
import {mapGetters} from 'vuex';
import {Form} from 'vform'

export default {

  name: 'new-client',

  data () {
    return {
      clientCreatedDialog: false,
      phones: [],
      client: {
        name: '',
        phone: '',
        email: '',
        notes: '',
      },
      form: new Form({
          name: '',
          email: '',
          notes: '',
        }),
      nameRules: [
          (v) => !!v || 'Валар моргулис',
        ],
      emailRules: [
        (v) => /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(v) || 'E-mail должен быть чуточку правильней'
      ],
    }
  },

  methods: {
    async submit() {
      const url = '/api/proposal/client/'

      this.form.phone = this.phones.join(',')
      const {data} = await this.form.post(url)
      
      this.client.name = this.form.name;
      this.client.email = this.form.email;
      this.client.phone = this.phones.join(',')
      this.client.notes = this.form.notes;

      this.clientCreatedDialog = true

      this.$store.dispatch('newClientCreated', data);
      this.$emit('new-client-created');
      this.$store.dispatch('responseMessage', {
          type: 'success',
          text: 'Новый клиент ' + this.form.name + ' создан',
          modal: false
        })
    },
    closeCreatedClientDialog() {
      this.client.name = ''
      this.form.name = ''
      this.client.email = ''
      this.form.email = ''
      this.client.phone = ''
      this.form.phone = ''
      this.client.notes = ''
      this.form.notes = ''
    },
    addPhone() {
      this.phones.push('')
    },
  },

  mounted() {
    this.addPhone();
  }
}
</script>

<style lang="css" scoped>
</style>