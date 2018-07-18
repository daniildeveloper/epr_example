<template>
<v-card>
  <v-list three-line subheader>
    <h3 class="headline">&nbsp;&nbsp;&nbsp;Оборотные деньги</h3>
    <v-list-tile v-for="c in capital" :key="c.name" :data="c">
      <v-list-tile-content>
        <v-list-tile-title>{{ c.in_ware }}/{{ c.total }}</v-list-tile-title>
        <headline>в товаре/ в обороте</headline>
      </v-list-tile-content>

    </v-list-tile>
  </v-list>
</v-card>
</template>

<script>
export default {

  name: 'working-capital',

  data () {
    return {
        capital: [
             {
                'name': 'упаковки',
                'total': 0,
                'in_ware': 0,
            },
            {
                'name': 'наклейки',
                'total': 0,
                'in_ware': 0,
            },
            {
                'name': 'основы',
                'total': 0,
                'in_ware': 0,
            }
        ]

    }
  }
}
</script>

<style lang="css" scoped>
</style>