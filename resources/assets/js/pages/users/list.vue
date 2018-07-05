<template>
  <v-expansion-panel expand>
    <v-expansion-panel-content v-for="(item,i) in 2" :key="i" v-bind:value="item === 2">
      <div slot="header">Иван Иваныч (продажник)</div>
      <v-card>
        <v-card-text class="grey lighten-3">
          <p>Телефон: +77718521479</p>
          <p>В компании: 1 месяц</p>
          <p>Сделок закрыл: 234</p>
          <v-btn
          >
            Уволить
          </v-btn>
          <v-btn
          >
            Отправить в отпуск
          </v-btn>
        </v-card-text>
      </v-card>
    </v-expansion-panel-content>
  </v-expansion-panel>
</template>

<script>
export default {

  name: 'userList',

  data () {
    return {

    }
  }
}
</script>

<style lang="css" scoped>
</style>