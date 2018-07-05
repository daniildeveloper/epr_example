<template>
  <div class="declined-proposal-rest">
    <v-btn
      @click="saveDeclinedProposalsRests">Сохранить</v-btn>
    <v-card>
      <v-card-title>
        Издержки при отмене заявки {{ proposal.code }}
        <v-spacer></v-spacer>
      </v-card-title>
      <v-data-table
          v-bind:headers="headers"
          v-bind:items="items"
        >
        <template slot="items" slot-scope="props">
          <td class="text-xs-right">{{ props.item.id }}</td>
          <td class="text-xs-right">{{ props.item.name }}</td>
          <td class="text-xs-right">{{ props.item.count }}</td>
          <td>
            <v-edit-dialog
              lazy
            > {{ props.item.rests }}
              <v-text-field
                slot="input"
                label="Изменить издержки"
                v-model="props.item.rests"
                single-line
                counter
              ></v-text-field>
            </v-edit-dialog>
          </td>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>

<script>

/**
 * This component is used to show declined proposals rests.
 * When we decline proposal and pass so, that we have already producted some rests
 */
export default {

  name: 'declined-proposal-rest',

  props: {
    wares: [], // array of wares in proposal
    proposal: {}, // proposal to decline (with all data, not id only)
  },

  data () {
    return {
      rests: [], // array bined with this.wares via key. Rests with wares
      items: [],
      headers: [
          {
            text: '#',
            align: 'left',
            sortable: false,
            value: 'id'
          },
          { text: 'Наименование', value: 'name' },
          { text: 'Количество', value: 'count' },
          { text: 'Издержки', value: 'rests' },
        ],
    }
  },

  methods: {
    /**
     * Save declined proposal rests
     * @return {[type]} [description]
     */
    saveDeclinedProposalsRests() {
      this.$emit('declined-proposal-rests-save', this.proposal.id)
    },

    /**
     * Merges arrays with proposal wares and rests
     * @return {[type]} [description]
     */
    setupData() {
      let res = []; // result array to return
      this.wares.forEach(ware => {
        let item = {}; // item to push to array
        item.id = ware.id;
        item.ware_name = ware.name;
        item.count = ware.count;
        item.rests = 0;
        res.push(item);
      })
      this.items = res;
    }
  },

  mounted() {
    // this.setupData();
  },

  watch: {
    wares: function() {
      this.setupData()
    }
  }
}
</script>

<style lang="css" scoped>
</style>