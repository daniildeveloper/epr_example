<template>
  <form>
    <v-text-field
      label="Адрес"
      v-model="form.address"
      :rules="nameRules"
      required
    ></v-text-field>

    <v-select
      label="Клиент"
      autocomplete
      :loading="loadingClientSearch"
      cache-items
      required
      :items="clients"
      :search-input.sync="search"
      item-text="name"
      item-value="id"
      v-model="form.client_id"
    ></v-select>

    <v-text-field
      label="Заметки"
      v-model="form.notes"
      multi-line
    ></v-text-field>

    <v-btn @click="submit">Создать</v-btn>

    <v-dialog v-model="objectCreatedDialog" max-width="500px">
      <v-card>
        <v-card-title>
          Объект создан
        </v-card-title>
        <v-card-text>
          <p>Адрес: {{ object.address }}</p>
          <p>Клиент: {{ object.client.name }}</p>
          <p>Заметки:</p>
          <p>{{ object.notes }}</p>
        </v-card-text>
      <v-card-actions>
        <v-btn color="primary" flat @click.stop="objectCreatedDialog=false">Закрыть</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </form>
</template>

<script>
import {Form} from 'vform'
import axios from 'axios'

export default {

  name: 'new-object',

  data () {
    return {
      search: null,
      objectCreatedDialog: false,
      object: {
        address: '',
        notes: '',
        client: {}
      },
      clients: [],
      clientSearch: null,
      loadingClientSearch: false,
      form: new Form({
          address: '',
          notes: '',
          client_id: null,
        }),
      nameRules: [
          (v) => !!v || 'Валар моргулис',
        ],
    }
  },

  methods: {
    async submit() {
      const url = '/api/proposal/object/'

      const {data} = await this.form.post(url)

      this.$store.dispatch('newObjectCreated', data);

      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Новый объект создан',
        modal: false
      })
    },
    closeCreatedClientDialog() {
      this.object.address = ''
      this.form.address = ''
      this.object.client_id = ''
      this.form.client_id = ''
      this.form.notes = ''
      this.object.notes = ''
    },
    querySelection(v) {
      this.loadingClientSearch = true;

      const url = '/api/proposal/data/client/search?query=' + v

      axios.get(url).then(result => {
        this.clients = result.data
        this.loading = false
      });
    }
  },
  watch: {
    search(val) {
      this.querySelection(val);
    }
  }
}
</script>

<style lang="css" scoped>
</style>