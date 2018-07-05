<template>
  <div class="mark">
    <h1>Отметиться на рабочем месте</h1>
  
    <v-btn
        color="info"
        :loading="loading"
        @click.native="loader = 'loading'"
        :disabled="loading"
    >
      Начать работать
    </v-btn>

    <v-btn
        color="default"
        :loading="loading"
        @click.native="loader = 'loading'"
        :disabled="loading"
    >
      Закончить рабочий день
    </v-btn>
  </div>
</template>

<script>
import {mapGetters} from 'vuex';

/**
 * При клике на кнопку пользовтаель отмечает, что он прибыл на рабочее место.
 */
export default {
  name: 'worker-mark',
  layout: 'deafult',

  computed: mapGetters({
    user: 'authUser'
  }),

  data () {
    return {
      loader: null,
      loading: false,
    }
  },

  methods: {

  }
}
</script>

<style lang="css">
.custom-loader {
    animation: loader 1s infinite;
    display: flex;
  }
  @-moz-keyframes loader {
    from {
      transform: rotate(0);
    }
    to {
      transform: rotate(360deg);
    }
  }
  @-webkit-keyframes loader {
    from {
      transform: rotate(0);
    }
    to {
      transform: rotate(360deg);
    }
  }
  @-o-keyframes loader {
    from {
      transform: rotate(0);
    }
    to {
      transform: rotate(360deg);
    }
  }
  @keyframes loader {
    from {
      transform: rotate(0);
    }
    to {
      transform: rotate(360deg);
    }
  }
</style>