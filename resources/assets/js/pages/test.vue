<template>
  <div>
    <ware-order-view></ware-order-view>
  </div>
</template>

<script>
import axios from 'axios'
import WareOrder from '~/pages/stock/ware-order'

export default {

  name: 'test',

  components: {
    'ware-order-view': WareOrder
  },

  data() {
    return {
      stages: [],
      blocks: [],
    }
    
  },

  methods: {
    getProposalData () {
      const getactiveProposalsData = '/api/proposal/proposal';
      const getStages = '/api/proposal/data/stages';

      axios.get(getactiveProposalsData).then(response => {
        this.blocks = response.data;
        axios.get(getStages).then(res => {
          this.stages = res.data;
        })
      });
    },
  },

  mounted() {
    this.getProposalData()
  },
}
</script>

<style lang="css" scoped>
</style>