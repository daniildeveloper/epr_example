<template>
  <div>
    <div class="drag-container stages">
      <ul class="drag-list">
        <li v-for="stage in stages" class="drag-column" :class="{['drag-column-' + stage.slug]: true}" :key="stage.slug" :value="stage.id">
          <span class="drag-column-header" v-bind:style="{'background-color': getColorForStage(stage)}">
            <h2 v-bind:style="{color: getColor(stage)}" >{{ stage.name }}</h2>
          </span>
          <div class="drag-options"></div>
          <ul class="drag-inner-list" ref="list" :data-status="stage.id">
            <li v-bind:class="{hotProposal : block.is_hot}" class="drag-item" v-for="block in getBlocks(stage.id)" :data-block-id="block.id" :key="block.id" @click="showDialogWithSingleProposal(block)">
                <slot :name="block.id" >
                  <p>{{ block.code }} (#{{ block.id }} )</p>
                  <p>{{ block.workers_deadline }}</p>
                  <ul class="ware-data">
                    <li  v-for="ware in block.wares">
                      <span>{{ ware.ware.name }} x {{ ware.count }}</span>
                    </li>
                  </ul>
                </slot>
            </li>
          </ul>
        </li>
      </ul>
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
  import dragula from 'dragula'

  import SingleProposal from '~/pages/proposal/single'

  export default {
    name: 'VueKanban',

    components: {
      'single-proposal-view': SingleProposal,
    },

    props: {
      stages: {},
      blocks: {},
    },
    data () {
      return {
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
      getProposalData () {
        const getactiveProposalsData = '/api/proposal/proposal';
        const getStages = '/api/proposal/data/stages';

        axios.get(getactiveProposalsData).then(response => {
          this.blocks = response.data;
        });
        axios.get(getStages).then(res => {
          this.stages = res.data;
        });
      },
      getBlocks (status_id) {
        return this.localBlocks.filter(block => block.status_id === status_id)
      },
      dragulaSetup() {
        window.setTimeout(() => {
          dragula(this.$refs.list)
          .on('drag', (el) => {
            el.classList.add('is-moving')
          })
          .on('drop', (block, list) => {
            let index = 0
            for (index = 0; index < list.children.length; index += 1) {
              if (list.children[index].classList.contains('is-moving')) break
            }
            this.$emit('update-block', block.dataset.blockId, list.dataset.status, index)
          })
          .on('dragend', (el) => {
            el.classList.remove('is-moving')

            window.setTimeout(() => {
              el.classList.add('is-moved')
              window.setTimeout(() => {
                el.classList.remove('is-moved')
              }, 600)
            }, 100)
          })
          // .on('over', (el) => {
          //   // hide now draggend element
          //   el.style.display = 'none'
          // })
          // .on('out', (el) => {
          //   el.style.display = 'block'
          // })
        }, 2 * 1000)
      },
      getColorForStage(stage) {
        return stage.color;
      },
      showDialogWithSingleProposal(proposal) {
        this.singleProposalID = proposal.code;
        this.singleProposalDialog = true;
        this.singleProposal = proposal;
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

      fromKanboardProposalStatusChange(val, id) {
        this.$emit('from-single-proposal-status-change', val, id);
      }
    },

    updated () {
      this.dragulaSetup();
    },

    watch: {
      stages(val) {
        this.dragulaSetup()
      },
      blocks(val) {
        this.dragulaSetup()
      }
    }
  }
</script>

<style>
.board-container {
}
ul.drag-list, ul.drag-inner-list {
  list-style-type: none;
  margin: 0;
  padding: 0;
}

.drag-container {
  width: 100%;
  margin: 20px auto;
  overflow-x: scroll;
  min-height: 420px;
  max-height: 420px;
  overflow-y: hidden;
}

.drag-list {
  display: flex;
  align-items: flex-start;
  flex-wrap: nowrap;
  /*overflow-y: scroll;*/
}
@media (max-width: 690px) {
/*  .drag-container {
    overflow-y: scroll;
  }*/
}

.drag-column {
  flex: 1;
  margin: 0 10px;
  position: relative;
  background: rgba(0, 0, 0, 0.2);
  overflow: hidden;
  min-width: 200px;
  /*min-height: 390px;
  max-height: 390px;*/
}
@media (max-width: 690px) {
  .drag-column {
    margin-bottom: 30px;
  }
}
.drag-column h2 {
  font-size: 0.8rem;
  margin: 0;
  text-transform: uppercase;
  font-weight: 600;
  min-width: 400px;
}

.drag-column-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px;
  border-bottom: 2px solid #fff;
}

.drag-inner-list {
  min-height: 50px;
  color: white;
  max-height: 380px;
  overflow-y: scroll;
}

.drag-item {
  padding: 10px;
  margin: 10px;
  min-height: 100px;
  background: rgba(0, 0, 0, 0.4);
  transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}

.drag-item.is-moving {
  transform: scale(1.5);
  /*background: rgba(0, 0, 0, 0.8);*/
}

.drag-header-more {
  cursor: pointer;
}

.drag-options {
  position: absolute;
  top: 44px;
  left: 0;
  width: 100%;
  height: 100%;
  padding: 10px;
  transform: translateX(100%);
  opacity: 0;
  transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
}
.drag-options.active {
  transform: translateX(0);
  opacity: 1;
}
.drag-options-label {
  display: block;
  margin: 0 0 5px 0;
}
.drag-options-label input {
  opacity: 0.6;
}
.drag-options-label span {
  display: inline-block;
  font-size: 0.9rem;
  font-weight: 400;
  margin-left: 5px;
}

/* Dragula CSS  */
.gu-mirror {
  position: fixed !important;
  margin: 0 !important;
  z-index: 9999 !important;
  opacity: 0.8;
  list-style-type: none;
}

.gu-hide {
  display: none !important;
}

.gu-unselectable {
  -webkit-user-select: none !important;
  -moz-user-select: none !important;
  -ms-user-select: none !important;
  user-select: none !important;
}

.gu-transit {
  /*opacity: 0.2;*/
  color: #fff !important;
}

/*custom styles*/
.ware-data {
  list-style-type: none;
}
.hotProposal {
  background: #3486D7 !important;
}
</style>