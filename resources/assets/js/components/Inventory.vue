<template>
  <v-card color="grey lighten-4" flat>
    <v-card-text>
      <v-container fluid>
        <v-layout row>
          <v-flex xs4>
            <v-subheader>{{ component.name }}</v-subheader>
          </v-flex>
          <v-flex xs4>
            <v-subheader>{{ last_inventory_rests }}</v-subheader>
          </v-flex>
          <v-flex xs4>
            <v-text-field
              label="Текущий остаток"
              v-model="realRest"
            ></v-text-field>
          </v-flex>
        </v-layout>
      </v-container>
    </v-card-text>
  </v-card>
</template>

<script>
/**
 * Компонент-чушка как основа для инвенторизации.
 */
export default {

  name: 'Inventory',

  props: [
    'component',
    'last_inventory_rests',
  ],

  data () {
    return {
      realRest: null,
    }
  },

  methods: {
  },

  watch: {
    realRest(val) {
      this.$emit('inventory-rest-change', this.component.id)
    }
  }
}
</script>

<style lang="css" scoped>
</style>