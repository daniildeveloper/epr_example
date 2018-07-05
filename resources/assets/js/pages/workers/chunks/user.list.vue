<template>
  <div class="user-list">
    <v-radio-group v-model="user">
      <v-radio v-for="user in users" :label="user.name" :value="user.id" ></v-radio>
    </v-radio-group>
  </div>
</template>

<script>
export default {

  name: 'user-list',

  data () {
    return {
      user: null
    }
  },
  props: [
    'users'
  ]
}
</script>

<style lang="css" scoped>
</style>