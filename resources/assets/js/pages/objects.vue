<template>
  <form>
    <v-text-field
      label="Имя Фамилия"
      v-model="form.name"
      :rules="nameRules"
      required
    ></v-text-field>

    <v-select
      label="Клиент"
      autocomplete
      :loading="loading"
      cache-items
      required
      :items="items"
      :rules="[() => select.length > 0 || 'Вы']"
      :search-input.sync="search"
      v-model="select"
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
          <p>Имя: {{ object.name }}</p>
          <p>Почта: {{ object.email }}</p>
          <p>Телефон: {{ object.phone }}</p>
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

export default {

  name: 'new-object',

  data () {
    return {
      objectCreatedDialog: false,
      object: {
        name: '',
        client_id: null,
      },
      form: new Form({
          name: '',
          phone: '',
          email: '',
          notes: '',
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
      
      this.object.name = data.object.name;
      this.object.client_id = data.object.name;

      this.objectCreatedDialog = true
    },
    closeCreatedClientDialog() {
      this.object.name = ''
      this.form.name = ''
      this.object.client_id = ''
      this.form.client_id = ''
    }
  }
}
</script>

<style lang="css" scoped>
</style>