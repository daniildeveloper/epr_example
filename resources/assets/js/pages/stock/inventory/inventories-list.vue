<template>
    <div>
        <new-inventory/>
    </div>
</template>

<script>
import axios from 'axios';

import NewInventory from '~/pages/stock/inventory/new-inventory';

/**
 * Component to show history inventories data
 */
export default {

  name: 'inventories-list',

  components: {
    NewInventory
  },

  data () {
    return {

    }
  }
}
</script>

<style lang="css" scoped>
</style>