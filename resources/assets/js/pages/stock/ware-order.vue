<template>
	<div class="ware-order">
        <v-flex xs12>
          <v-btn color="success" @click="change">Изменить</v-btn>
        </v-flex>
		<div class="column" >
	      <div class="item" 
            v-for="ware in wares"
            :data="ware"
            :key="ware.id"
            :data-block-id="ware.id"
            >
           <p>{{ware.name}}</p>   
          </div>
	      <!-- end single item item preview -->
	    </div>
	</div>
</template>

<script>
import axios from 'axios'
import dragula from 'dragula'

export default {

  name: 'ware-order',

  data () {
    return {
        wares: [],
    }
  },

  methods: {
  	getData() {
        axios.get('/api/order-change')
            .then(response => {
                this.wares = response.data;
            })
    },

  	setupDragAndDrop () {
  		let drake = dragula({
	        moves: function (el, cont, handle) {
	          // console.log('dragula drag', el, cont, handle)
	          return handle.className !== 'title';
	        },
	      })
	      // .on('drop', function (block, list) {
	      //   console.log('Block is updated')
	      // });

	      let cs = document.querySelectorAll('.column')
	      for (let i in cs) {
	        drake.containers.push(cs[i])
	      }
  	},

    change() {
        // let waresByOrder = document.querySelectorAll('.column')[0].children;
        let waresByOrder = document.getElementsByClassName('item');
        let wares = [];

        for (let key in waresByOrder) {
            if (typeof waresByOrder[key].attributes != "undefined") {
                // console.log(waresByOrder[key].attributes.getNamedItem('data-block-id'));
                console.log(waresByOrder[key].attributes[2].value);
                // wares.push(waresByOrder[key].getAttribute('data-block-id'));
                wares.push(waresByOrder[key].attributes[2].value)
            }
            
        }

        axios.post('/api/order-change', {
            data: wares
        }).then(response => {
            this.getData();
            this.setupDragAndDrop();

            this.$store.dispatch('responseMessage', {
                type: 'success',
                text: 'Порядок выдачи для создания заявки изменен',
                modal: false
              })
            this.$emit('updated')
        })
    }
  },

  mounted() {
    this.getData();
    this.setupDragAndDrop();
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