<template>
  <div id="kanban">
    <div class="column" v-for="stage in stages" :id="stage.id" :data-status="stage.id" >
      <div  v-bind:style="{'background-color': getColorForStage(stage), position: 'absolute', top: '0px', 'margin-bottom': '35px', width: '250px'}" class="title">
        <h2 v-bind:style="{color: getColor(stage)}" >{{ stage.name }}</h2>
      </div>

      <!-- single item preview -->
      <div class="item" 
        :id="block.id"
        :class="{hot: block.is_hot === 1, warrantyCase: block.warranty_case === 1}"
        v-for="block in getBlocks(stage.id)"
        :data-block-id="block.id"
        :data-block-status-id="block.status_id"
        :key="block.id"
        @click="showDialogWithSingleProposal(block)">
          <h4>{{ block.code }} (#{{ block.id }})</h4>
          <p>{{ block.workers_deadline }}</p>
          <ul class="ware-data">
            <li  v-for="ware in block.wares">
              <span>{{ ware.ware.name }} x {{ ware.count }}</span>
            </li>
          </ul>
      </div>
      <!-- end single item item preview -->
    </div>

        <!-- single proposal view dialog -->
    <v-layout row justify-center>
      <v-dialog v-model="singleProposalDialog" persistent>
        <v-card>
          <v-card-title>
            <span class="headline">Заявка</span>
            <v-spacer></v-spacer>
            <v-menu>
              <v-btn @click="singleProposalDialog = false" icon slot="activator">
                <v-icon>close</v-icon>
              </v-btn>
            </v-menu>
          </v-card-title>
          <v-card-text>
            <v-container grid-list-md>
              <v-layout wrap v-if="singleProposalDialog">
                <single-proposal-view
                  :id="singleProposal.id"
                  :status="singleProposal.status_id"
                  :code="singleProposal.code"
                  :workers_deadline="singleProposal.workers_deadline"
                  :client_deadline="singleProposal.client_deadline"
                  :created_at="singleProposal.created_at"
                  :wares="singleProposal.wares"
                  :argument="singleProposal.argument"
                  :object="singleProposal.object"
                  :client="singleProposal.client"
                  :client_phone="singleProposal.client_phone"
                  :statuses="stages"
                  :blocks="blocks"
                  :notes="singleProposal.notes"
                  @proposal-status-change="fromKanboardProposalStatusChange"
                  @proposal-single-window-clos="singleProposalDialog = false"
                ></single-proposal-view>
              </v-layout>
            </v-container>
          </v-card-text>
          <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn color="blue darken-1" flat @click.native="singleProposalDialog = false">Закрыть</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-layout>
    <!-- end sungle proposal view dialog -->
  </div>
</template>

<script>
import axios from 'axios'
import dragula from 'dragula'
import autoscroll from 'dom-autoscroller'
import SingleProposal from '~/pages/proposal/single'

export default {

  name: 'DragulaBoard',

  props: [
    'stages',
    'blocks',
  ],

  components: {
    'single-proposal-view': SingleProposal,
  },

  data() {
    return {
      kanban: null,
      d: null,

      singleProposalDialog: false,
      singleProposalID: {},
      singleProposal: {},

      // styling for single proposal
      singleProposalCardStylingObject: {
        background: '#3486D7',
      },
    }
    
  },

  computed: {
    localBlocks () {
      return this.blocks
    }
  },

  methods: {

    setup() {
      let that = this;
      let drake = dragula({
        moves: function (el, cont, handle) {
          // console.log('dragula drag', el, cont, handle)
          return handle.className !== 'title';
        },
      })
      .on('drop', function (block, list) {
        console.log('Block is updated')
        // disable drag and drop to unlogical status
        that.$emit('update-block', block.dataset.blockId, list.dataset.status)
      });

      let cs = document.querySelectorAll('.column')
      for (let i in cs) {
        drake.containers.push(cs[i])
      }
    },

    getBlocks (status_id) {
      return this.localBlocks.filter(block => block.status_id === status_id)
    },

    /**
       * Calculate optimal color for stage
       * @param  {[type]} stage [description]
       * @return {[type]}       [description]
       */
    getColor(stage) {
      if (stage.color === '#fff') {
        return 'black';
      } else {
        return '#ffffff';
      }
    },

    getColorForStage(stage) {
      return stage.color;
    },

    showDialogWithSingleProposal(proposal) {
      this.singleProposalID = proposal.code;
      this.singleProposalDialog = true;
      this.singleProposal = proposal;
    },

    fromKanboardProposalStatusChange(val, id) {
      this.$emit('from-single-proposal-status-change', val, id);
    }
  },

  updated() {
    this.setup();
  },

  mounted() {
    let that = this; // contexts fix
    window.addEventListener('proposal-created', function() {
      that.setup();
    }, false);

    // listen for proposal status change
    window.addEventListener('proposal-status-changed', function () {
      that.setup();
    }, false);
  },

  watch: {
    'blocks': {
      handler: function (val, oldVal) {
        console.log('updated from watcher')
      },
      deep: true,
    }
  }
}
</script>

<style lang="css" scoped>
  #kanban {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: flex-start;
    align-content: flex-start;
    align-items: flex-start;
    overflow-x: scroll;
    width: 100%;
    margin: 20px auto;
    overflow-x: scroll;
    min-height: 500px;
    max-height: 500px;
    overflow-y: auto;
  }
  
  /*For proposal block if is warranty cased*/
  .warrantyCase {
    background-color: #E53935;
  }
  
  /*for proposal block if is_hot === true*/
  .hot {
    background-color: #3486D7;
  }

  ul {
    list-style-type: none;
  }

  .column {
    display: flex;
    flex-direction: column;
    flex-wrap: nowrap;
    padding: 15px;
    border-radius: 4px;
    margin: 0 5px;
    width: 250px;
    min-width: 250px;
    min-height: 480px;
    height: 480px;
    overflow-y: scroll;
    overflow-x: hidden;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.19), 0 6px 6px rgba(0, 0, 0, 0.23);
    position: relative;
    padding-top: 35px;
  }
  .column .title {
    font-size: 20px;
    font-weight: 700;
    line-height: 2.3rem;
    padding: 1rem;
  }

  .title h2 {
    font-size: 0.8rem;
    margin: 0;
    text-transform: uppercase;
    font-weight: 600;
    min-width: 400px;
  }

  .item {
    margin-top: 5px;
    padding: 10px;
    /*background-color: #fff;*/
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    border-radius: 4px;
  }

  .gu-transit {
    color: #fff !important;
  }

  .gu-mirror {
      position: fixed!important;
      margin: 0!important;
      z-index: 9999!important;
      opacity: .8;
      -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
      filter: alpha(opacity=80);
      color: #fff !important;
    }

    .gu-hide {
      display: none!important;
    }

    .gu-unselectable {
      -webkit-user-select: none!important;
      -moz-user-select: none!important;
      -ms-user-select: none!important;
      user-select: none!important;
    }

    .gu-transit {
      opacity: .2;
      -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=20)";
      filter: alpha(opacity=20);
      color: #fff !important;
    }

</style>