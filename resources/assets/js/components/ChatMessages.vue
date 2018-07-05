<template>
    <blockquote class="chat">
        <div class="message" v-bind:class="[ checkForAlign(message) ? 'me' : 'them' ]" v-for="message in messages">
            <div class="header">
                <strong class="primary-font">
                    {{ message.user.name }}
                </strong>
            </div>
            <p>
                {{ message.message }}
            </p>
        </div>
    </blockquote>
</template>

<script>
import {mapGetters} from 'vuex';

export default {
    props: ['messages'],

    computed: mapGetters({
        user: 'authUser'
    }),
    methods: {
        checkForAlign(message) {
            if (Number(this.user.data.id) === Number(message.user.id)) {
                // return 'me';
                return true;
            } else {
                // return 'them';
                return false;
            }
        }
    }
};
</script>

<style>
    .chat {
        width: 80%;
        margin: 1rem auto;
    }
    .message {
      margin: 0 0 0.5em;
      border-radius: 1em;
      padding: 0.5em 1em;
      background: #3486D7;
      max-width: 75%;
      clear: both;
      position: relative;
    }
    .message.them {
      float: left;
    }
    .message.them::after {
      content: "";
      position: absolute;
      left: -0.5em;
      bottom: 0;
      width: 0.5em;
      height: 1em;
      border-right: 0.5em solid #3486D7;
      border-bottom-right-radius: 1em 0.5em;
    }
    .message.me {
      float: right;
      background-color: #61B865;
      color: white;
    }
    .message.me::after {
      content: "";
      position: absolute;
      right: -0.5em;
      bottom: 0;
      width: 0.5em;
      height: 1em;
      border-left: 0.5em solid #60B864;
      border-bottom-left-radius: 1em 0.5em;
    }
</style>