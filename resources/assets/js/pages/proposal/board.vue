<template>
  <div>
    <proposal-list-view></proposal-list-view>
    <v-dialog v-model="un_allowed_dialog" persistent>
      <v-card>
        <v-card-title>
          <span class="headline">Причина отклонения заявки № {{ unallowedForm.proposal_id }}</span>
        </v-card-title>
        <v-card-text>
          <form>
            <input type="hidden" v-model="unallowedForm.proposal_id">
            <v-text-field
              label="Причина"
              v-model="unallowedForm.argument"
              multi-line
            ></v-text-field>
          </form>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" flat @click.native="un_allowed_dialog = false">Закрыть</v-btn>
          <v-btn color="blue darken-1" flat @click="updateUnallowedBlockSave">Сохранить</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-dialog v-model="declinedProposalRestDialog" persistent>
      <v-card>
        <declined-proposal-rest-view 
          :wares="proposalToDecline.wares"
          :proposal="proposalToDecline"
          @declined-proposal-rests-save="emitDeclinedProposalRests"
          ></declined-proposal-rest-view>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue darken-1" flat @click.native="declinedProposalRestDialog = false">Нет издержек</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import store from '~/store'
import axios from 'axios';
import Form from 'vform';
import ProposalList from '~/pages/proposal/proposals-list';
import DeclinedProposalRest from '~/pages/proposal/declined-proposal-rest';

export default {

  name: 'proposal-board',

  components: {
    'proposal-list-view': ProposalList,
    'declined-proposal-rest-view': DeclinedProposalRest,
  },

  computed: mapGetters({
    user: 'authUser',
    message: 'responseMessage'
  }),

  data () {
    return {
      newProposalDialog: false,
      declinedProposalRestDialog: false,

      // data
      stages: [],
      blocks: [],

      // unallowed proposal
      un_allowed_dialog: false,
      unallowedForm: new Form({
        proposal_id: 0,
        argument: ''
      }),

      activeTab: null,

      proposalToDecline: {},
    }
  },
  methods: {
    updateBlock (id, status) {
      status = Number(status)

      // disable return one step later
      if (this.blocks.find(b => b.id === Number(id)).status < status) {
        return;
      }

      this.blocks.find(b => b.id === Number(id)).status = status
      if (status === 2 ) {
        this.unallowedForm.proposal_id = id
        this.un_allowed_dialog = true
        this.changeStatus(id, status)
        return;
      }

      if (status === 3 || status === 5 || status === 6)  {
        if (this.hasPermission('confirm_proposals')) {
          this.changeStatus(id, status)
        } else {
          this.noRoots()
        }
        return;
      }

      if (status === 9) {
        if (this.hasPermission('unallow_proposals')) {
          // this.proposalToDecline = this.blocks.filter(block => {
          //   if (block.id === id) {
          //     return block;
          //   }
          // });
          this.changeStatus(id, status);
        } else {
          this.noRoots()
        }
        return;
      }
      this.changeStatus(id, status)
      // update dragged block
      this.getProposalData();
    },
    async updateUnallowedBlockSave() {
      const {data} = await this.unallowedForm.post('/api/proposal/proposal/argument')
      this.getProposalData()
      this.un_allowed_dialog = false
    },
    changeStatus(proposalID, statusID) {
      axios.post('/api/proposal/proposal/change-status', {
        proposal_id: proposalID,
        status_id: statusID, 
      }).then(response => {
        this.getProposalData()
      })
    },
    hasPermission(permission) {
      let permissionsArray = [];
      this.user.data.permissions.forEach(p => {
        permissionsArray.push(p.name);
      });

      if (permissionsArray.indexOf(permission) !== -1) {
        return true;
      }
      return false
    },
    getProposalData () {
      const getactiveProposalsData = '/api/proposal/proposal';
      const getStages = '/api/proposal/data/stages';

      axios.get(getactiveProposalsData).then(response => {
        this.blocks = response.data;
      });
      axios.get(getStages).then(res => {
        this.stages = res.data;
      })
    },
    noRoots() {
      store.dispatch('responseMessage', {
        type: 'error',
        text: 'Нет прав',
        title: "Нет доступа",
        modal: true
      })
      this.getProposalData()
    },

    emitDeclinedProposalRests(proposalID) {
      this.changeStatus(proposalID, 8)
    }
  },

  mounted() {
    this.getProposalData()

    let that = this; // contexts fix
    window.addEventListener('proposal-created', function() {
      that.getProposalData();
      that.$store.dispatch('responseMessage', {
        type: 'error',
        text: 'Была создана новая заявка',
        modal: false
      })
    }, false);

    // listen for proposal status change
    window.addEventListener('proposal-status-changed', function () {
      that.getProposalData();
      that.$store.dispatch('responseMessage', {
        type: 'error',
        text: 'Изменения в статусе заявки',
        modal: false
      })
    }, false);
  },
}
</script>

<style lang="css" scoped>
</style>