<template>
    <div class="chat-form">
        <v-container grid-list-md text-xs-center>
            <v-layout row wrap>
                <v-flex xs10>
                    <v-text-field
                        label="Сообщение..."
                        v-model="newMessage"
                        @keyup.enter="sendMessage"
                    ></v-text-field>
                </v-flex>

                <v-flex xs2>
                    <button class="btn btn-primary btn-sm" id="btn-chat" @click="sendMessage">
                        Отправить
                    </button>
                </v-flex>
            </v-layout>
        </v-container>
    </div>
</template>

<script>
    export default {
        props: ['user'],

        data() {
            return {
                newMessage: ''
            }
        },

        methods: {
            sendMessage() {
                this.$emit('messagesent', {
                    user: this.user,
                    message: this.newMessage
                });

                this.newMessage = ''
            }
        }    
    }
</script>

<style>
    .chat-form {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
    }
</style>