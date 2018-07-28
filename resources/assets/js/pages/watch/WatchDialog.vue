<template>
    <form @submit.prevent="save">
      <v-card-title primary-title>
        <h4 class="headline mb-0">Новая вахта</h4>
      </v-card-title>

      <v-card-text>
        <submit-button :block="true" :form="watchForm" label="Обновить"></submit-button>
      </v-card-text>

    </form>
</template>

<script>
import Form from 'vform';

export default {

  name: 'WatchDialog',

  data () {
    return {
        watch: new Form({
            
        })
    }
  }
}
</script>

<style lang="css" scoped>
</style>