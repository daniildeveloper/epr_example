<template>
  <v-layout align-content-start row wrap>
    <!-- wrap each ware config -->
    <v-layout xs10 row wrap >
      <v-flex xs10>
        <v-select
          autocomplete
          label="Товар"
          v-model="ware_id"
          placeholder="Выбрать товар"
          :items="wares"
          item-text="name"
          item-value="id"
          :rules="nameRules"
          :error="typeof validationErrors != 'undefined' && typeof validationErrors['wares.' + keyWare + '.ware_id'] != 'undefined' && validationErrors['wares.' + keyWare + '.ware_id'].length > 0"
          :error-messages="validationErrors.length > 0 ? validationErrors['wares.' + keyWare + '.ware_id'] : []"
          required
        ></v-select>
      </v-flex>
      
      <v-flex xs10>
        <v-text-field
          label="Цена за единицу товара"
          v-model="price_per_count"
          :rules="nameRules"
          :error="typeof validationErrors != 'undefined' && typeof validationErrors['wares.' + keyWare + '.price_per_count'] != 'undefined' && validationErrors['wares.' + keyWare + '.price_per_count'].length > 0"
          :error-messages="validationErrors.length > 0 ? validationErrors['wares.' + keyWare + '.price_per_count'] : []"
          required
        ></v-text-field>
      </v-flex>

      <v-flex xs10>
        <v-text-field
          label="Количество товара"
          v-model="count"
          :rules="nameRules"
          :error="typeof validationErrors != 'undefined' && typeof validationErrors['wares.' + keyWare + '.count'] != 'undefined' && validationErrors['wares.' + keyWare + '.count'].length > 0"
          :error-messages="validationErrors.length > 0 ? validationErrors['wares.' + keyWare + '.count'] : []"
          required
        ></v-text-field>
      </v-flex>

      <v-flex xs10>
        <v-checkbox
          label="Без цвета"
          v-model="color_doesnt_exist"
        ></v-checkbox>
      </v-flex>

      <v-flex xs10 v-if="color_doesnt_exist === false">
        <v-text-field
          label="Цвет"
          v-model="color"
          :rules="nameRules"
          required
          :error="typeof validationErrors != 'undefined' && typeof validationErrors['wares.' + keyWare + '.color'] != 'undefined' && validationErrors['wares.' + keyWare + '.color'].length > 0"
          :error-messages="validationErrors.length > 0 ? validationErrors['wares.' + keyWare + '.color'] : []"
        ></v-text-field>
      </v-flex>

      <v-flex xs10 v-if="color_doesnt_exist === false">
        <v-text-field
          label="Стоимость цвета"
          v-model="color_price"
          required
          :rules="nameRules"
          :error="typeof validationErrors != 'undefined' &&  typeof validationErrors['wares.' + keyWare + '.color_price'] != 'undefined' && validationErrors['wares.' + keyWare + '.color_price'].length > 0"
          :error-messages="validationErrors.length > 0 ? validationErrors['wares.' + keyWare + '.color_price'] : []"
        ></v-text-field>
      </v-flex>

      <v-btn
        @click="deleteWare">
        Убрать товар
      </v-btn>
      <div class="space"></div>
    </v-layout>
  </v-layout>
</template>

<script>

export default {

  name: 'proposal-ware',

  props: [
    'wares',
    'keyWare',
    'warranty',
    'validationErrors',
  ],

  data () {
    return {
      ware_id: 0,
      price_per_count: null,
      count: 1,
      color_doesnt_exist: true,
      color_price: 0,
      color: '',

      partner_free: false,
      partner: 0,
      partner_notes: '',

      nameRules: [
        (v) => !!v || 'Данное поле необходимо для заполнения!',
      ],
    }
  },

  watch: {
    ware_id(val) {
      this.$emit('proposal-ware-id-change', val, this.keyWare)
    },
    price_per_count(val) {
      this.$emit('proposal-ware-price-per-count-change', val, this.keyWare)
    },
    count(val) {
      this.$emit('proposal-ware-count-change', val, this.keyWare)
    },
    color_doesnt_exist(val) {
      this.$emit('proposal-ware-color-doesnt-exist-change', val, this.keyWare)
    },
    color_price(val) {
      this.$emit('proposal-ware-color-price-change', val, this.keyWare)
    },
    color(val) {
      this.$emit('proposal-ware-color-change', val, this.keyWare)
    },
    partner_free(val) {
      this.$emit('proposal-ware-partner-free-change', val, this.keyWare)
    },
    partner(val) {
      this.$emit('proposal-ware-partner-change', val, this.keyWare)
    },
    partner_notes(val) {
      this.$emit('proposal-ware-partner-notes-change', val, this.keyWare)
    }
  },

  methods: {
    deleteWare(){
      this.$emit('proposal-ware-delete', this.keyWare)
    }
  }
}
</script>

<style lang="css" scoped>
.space {
  width: 100%;
  height: 35px;
}
</style>