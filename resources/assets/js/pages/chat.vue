<template>
  <v-layout row justify-center>
    <v-dialog v-model="dialog" fullscreen transition="dialog-bottom-transition" :overlay=false>
      <v-btn color="primary" dark slot="activator">Чат</v-btn>
      <v-card>
        <v-toolbar dark color="primary">
          <v-btn icon @click.native="dialog = false" dark>
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title>Чат</v-toolbar-title>
          <v-spacer></v-spacer>
          <v-toolbar-items>
            <v-btn dark flat @click.native="dialog = false">Закрыть</v-btn>
          </v-toolbar-items>
        </v-toolbar>
        <div>
          <chat-messages-view :messages="messages"></chat-messages-view>
          <chat-form-view @messagesent="addMessage" :user="user"></chat-form-view>
        </div>
        <v-divider></v-divider>
      </v-card>
    </v-dialog>
  </v-layout>
</template>

<script>
import axios from 'axios'
import {mapGetters} from 'vuex'
import ChatMessages from '~/components/ChatMessages'
import ChatForm from '~/components/ChatForm'

export default {

  name: 'chat',

  components: {
    'chat-messages-view': ChatMessages,
    'chat-form-view': ChatForm
  },

  computed:{
    ...mapGetters({
      user: 'authUser',
      authenticated: 'authCheck'
    }),
  },

  data () {
    return {
      dialog: false,
      messages: [], // array of messages
    }
  },

  methods: {
    fetchMessages() {
      axios.get('/api/user/messages')
        .then(response => {
          this.messages = response.data;
        })
    },

    addMessage(message) {
      axios.post('/api/user/messages', {
        message: message.message
      })
        .then(response => {
          this.fetchMessages()
        })
    }
  },

  mounted() {
    this.fetchMessages();

    // listen for new messages
    window.addEventListener('new-message', function () {
      this.fetchMessages();
    });
  }
}
</script>

<style lang="css" scoped>
</style>