<template>
  <v-flex xs12>
    <v-flex xs12>
      <v-text-field
        label="Описание услуги"
        v-model="service"
        required
      ></v-text-field>
    </v-flex>

    <v-flex xs12>
      <v-text-field
        label="Цена"
        v-model="price"
        required
      ></v-text-field>
    </v-flex>

    <v-flex>
      <v-btn
      >Убрать</v-btn>
    </v-flex>
  </v-flex>
</template>

<script>
export default {

  name: 'proposal-additional-service',

  props: [
    'additionalServiceKey'
  ],

  data () {
    return {
      service: '',
      price: '',
    }
  },

  watch: {
    service(val) {
      this.$emit('additional-service-service-change', val, this.additionalServiceKey)
    },
    price(val) {
      this.$emit('additional-service-price-change', val, this.additionalServiceKey)
    }
  }
}
</script>

<style lang="css" scoped>
</style>