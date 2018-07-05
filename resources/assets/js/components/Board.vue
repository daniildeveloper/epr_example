<template>
  <div>
    <div class="modal-overlay"></div>
    <div class="wrap" id="multi">
        <div class="dragBox" v-bind:id="stage.slug" v-for="stage in stages">
          <h4 class="headline">{{ stage.name }}</h4>
            <div v-bind:id="getTaskId(block.id)" class="task" v-for="block in getBlocks(stage.id)">
                <div class="cardMini ">
                    <div class="header color-green"></div>
                    <div class="content">{{ block.code }}</div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {

  name: 'Board',

  props: {
    
  },

  computed: {
    localBlocks () {
      return this.blocks
    }
  },

  data () {
    return {
      originalPosition: null,
      dragEl: {},
      nextEl: {},
      args: [],
      thisTask: {},

      // last dragged element
      currentDrag: 0,

      // stages and boards
      stages: [{"id":1,"slug":"created","name":"Создана","color":"#455a64","created_at":"2018-03-21 17:42:00","updated_at":"2018-03-21 17:42:00"},{"id":2,"slug":"not_allowed","name":"Непринято","color":"#e53935","created_at":"2018-03-21 17:42:00","updated_at":"2018-03-21 17:42:00"},{"id":3,"slug":"accepted_workers","name":"Принято в цеху","color":"#3f51b5","created_at":"2018-03-21 17:42:00","updated_at":"2018-03-21 17:42:00"},{"id":4,"slug":"decline_candidat","name":"На отмену","color":"#8bc34a","created_at":"2018-03-21 17:42:00","updated_at":"2018-03-21 17:42:00"},{"id":5,"slug":"in_process","name":"В процессе","color":"#ffca28","created_at":"2018-03-21 17:42:00","updated_at":"2018-03-21 17:42:00"},{"id":6,"slug":"ready","name":"Готово в цеху","color":"#388e3c","created_at":"2018-03-21 17:42:00","updated_at":"2018-03-21 17:42:00"},{"id":7,"slug":"sended","name":"Отправленно","color":"#4e342e","created_at":"2018-03-21 17:42:00","updated_at":"2018-03-21 17:42:00"},{"id":8,"slug":"acepted_by_client","name":"Принято клиентом","color":"#c2185b","created_at":"2018-03-21 17:42:00","updated_at":"2018-03-21 17:42:00"},{"id":9,"slug":"declined","name":"Отмененный","color":"#757575","created_at":"2018-03-21 17:42:00","updated_at":"2018-03-21 17:42:00"}],
      blocks: [],
    }
  },

  methods: {
    getTaskId(id) {
      return 'task' + id;
    },
    getBlocks (status_id) {
        return this.blocks.filter(block => block.status_id === status_id)
    },
    getProposalData () {
      const getactiveProposalsData = '/api/proposal/proposal';
      const getStages = '/api/proposal/data/stages';

      axios.get(getactiveProposalsData).then(response => {
        this.blocks = response.data;
        this.args = [].slice.call(document.getElementsByClassName('dragBox'));
        console.log('typeof args', typeof args);
        let that = this;

        [].slice.call(document.getElementsByClassName('dragBox')).forEach(function (itemEl) {
            console.log('itemEl', itemEl);
            itemEl.ondrop = function () {
                that.drop(this, event);
            };
            itemEl.ondragover = function () {
                that.allowDrop(this, event);
            };
            let dragBoxContext = this;
            [].slice.call(itemEl.children).forEach(function (taskEl) {
                taskEl.draggable = true;

                taskEl.ondragstart = function () {
                    that.drag(this, event);
                };

                taskEl.ondragend = function () {
                  console.log('end of drag', that.currentDrag);
                }
            });
        });
      })
    },
    /**
     * Setup kanboard
     * @return {[type]} [description]
     */
    galPicker() {

      //  "use strict";
      let el;
      let inputEl;
      let colors = ["#f44336", "#e91e63", "#9c27b0", "#673ab7", "#3f51b5", "#2196f3", "#03a9f4", "#00bcd4", "#009688", "#4caf50", "#8bc34a", "#cddc39", "#ffeb3b", "#ffc107", "#ff9800", "#ff5722", "#795548", "#607d8b", "#00c097", "#FF5E8F"];
      document.getElementsByTagName('body')[0].innerHTML += '<div class="color-picker" id="gal-picker"></div>';
      let picker = document.getElementById('gal-picker');
      let i = 0;
      let text = "";

      /**
       * Usage for Example  <input type="hidden" class="gal-color" value="#9c27b0">
       *
       */
      function glaColorInit() {

          let init = document.querySelectorAll('.gal-color');
          let i = 0;

          while (init[i]) {
              let initColor = init[i].getAttribute('value');
              el = document.createElement("div");
              insertAfter(init[i], el);
              el.classList.add('color-input');
              el.setAttribute('style', 'background-color: ' + initColor + ';');
              i++;
          }
      }

      function insertAfter(referenceNode, newNode) {
          referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
      }

      glaColorInit();

      while (colors[i]) {
          text += '<div   class="color item" style="background-color:' + colors[i] + ';"></div>';
          i++;
      }

      document.getElementById('gal-picker').innerHTML = text;

      document.addEventListener('click', pickerOpen, false);

      function pickerOpen(event) {

          let theTarget = event.target || event.srcElement;
          pickerClose(theTarget);

          if (theTarget.className === 'color-input') {
              inputEl = theTarget;
              let callElement = theTarget.getBoundingClientRect();
              picker.classList.add("active");
              let b = callElement.bottom;
              let l = callElement.left;
              picker.style.top = b + 10 + 'px';
              picker.style.left = l + 'px';

          }
          //console.log(inputEl);
          if (inputEl) {
              pickerOnClick(event, inputEl);
          }

      }

      function pickerOnClick(event, theTarget) {
          let target = event.target;
          if (target.className === 'color item') {
              console.log(theTarget);
              theTarget.style.backgroundColor = target.style.backgroundColor;
              theTarget.previousElementSibling.value = target.style.backgroundColor;
              console.log(theTarget.style.backgroundColor);
              console.log(theTarget.closest('.cardFull').firstElementChild.style);
              theTarget.closest('.cardFull').firstElementChild.style.backgroundColor = target.style.backgroundColor;
              console.log(thisTask);
              thisTask.querySelector('.cardMini').firstElementChild.style.backgroundColor = target.style.backgroundColor;
              return;
          }
      }

      function pickerClose(theTarget) {
          if (theTarget.className !== 'color-picker active' && theTarget.className !== 'color-input') {
              picker.classList.remove('active');
              // if use drug and drop need to delete position inside the window in view
              picker.removeAttribute("style");
          }
      }
    },

    insertAfter(elem, refElem) {
        return refElem.parentNode.insertBefore(elem, refElem.nextSibling);
    },

    swipeInfo(event) {
        var x = event.clientX,
            y = event.clientY,
            dx, dy;

        dx = ( x > this.originalPosition.x ) ? "right" : "left";
        dy = ( y > this.originalPosition.y ) ? "down" : "up";
        this.originalPosition = {
            x: event.clientX,
            y: event.clientY
        };
        return {
            direction: {
                x: dx,
                y: dy
            },
            offset: {
                x: x - this.originalPosition.x,
                y: this.originalPosition.y - y
            }
        };
    },

    allowDrop(thisTarget, ev) {
      ev.preventDefault();
      ev.dataTransfer.dropEffect = 'move';
      let target = ev.target;
      let info = this.swipeInfo(ev);
      let superTarget = (target.parentNode).parentNode;
      if (target && superTarget !== this.dragEl && superTarget.className == 'task') {
          // Сортируем

          // Сортируем
          if (info.direction.y === "down") {
              console.log("-after");
              this.insertAfter(this.dragEl, superTarget);
          }
          if (info.direction.y === "up") {
              console.log(" -before");
              thisTarget.insertBefore(this.dragEl, superTarget);
          }


        //  thisTarget.insertBefore(dragEl, thisTarget.children[0] !== superTarget && superTarget.nextSibling || superTarget);
      }

      this.currentDrag = target.id;
    },

    drag(target, event) {
        this.dragEl = event.target;
        this.originalPosition = { // И его первоночальную позицию
            x: event.clientX,
            y: event.clientY
        };
        this.nextEl = this.dragEl.nextSibling;
        event.dataTransfer.setData("text", target.id);
    },

    drop(target, event) {
        var data = event.dataTransfer.getData("text");
        if (event.target !== this.dragEl && event.target.className == 'dragBox') {
            target.appendChild(document.getElementById(data));
        }
        event.preventDefault();
    },

    expandCard(thisCard) {
      this.thisTask = thisCard;
      document.querySelector('.modal-overlay').classList.add('active');
      var cardMini = thisCard.querySelector('.cardMini');
      var cardFull = thisCard.querySelector('.cardFull');
      if (thisCard.className !== 'active') {
          thisCard.classList.add("active");
          cardLoc = cardMini.getBoundingClientRect();
          // cardFull.log(cardLoc.left);
          cardFull.style.left = cardLoc.left + 'px';
          cardFull.style.top = cardLoc.top + 'px';
          cardFull.style.width = cardLoc.width + 'px';
          cardFull.style.height = cardLoc.height + 'px';
          //     cardFull.style.backgroundColor = '#cccccc';

          //thisCard.classList.add("active");
          setTimeout(function () {
              var w = window.innerWidth
                  || document.documentElement.clientWidth
                  || document.body.clientWidth;

              var h = window.innerHeight
                  || document.documentElement.clientHeight
                  || document.body.clientHeight;


              //  console.log(h + ' ' + w);
              cardFull.style.width = w * .6 + 'px';
              cardFull.style.height = h * .6 + 'px';
              cardFull.style.left = w * .2 + 'px';
              cardFull.style.top = h * .2 + 'px';


          }, 25);
      }
    },
  },

  mounted() {
    this.getProposalData();
  },
}
</script>

<style lang="css" scoped>
  [draggable] {
      -moz-user-select: none;
      -khtml-user-select: none;
      -webkit-user-select: none;
      user-select: none;
      /* Required to make elements draggable in old WebKit */
      -khtml-user-drag: element;
      -webkit-user-drag: element;
  }

  .wrap {
      display: table;
      table-layout: fixed;
      width: 100%;
      height: 100%;
  }

  .dragBox {
      display: table-cell;
      width: 100%;
      height: 100%;
      border: 1px dotted #bdbdbd;
      /*padding: 10px;*/
  }

  .task {
    position: relative;
    color: #576366;
    margin: 10px 20px;
    -webkit-animation: panel-fade-up 0.4s ease;
    -moz-animation: panel-fade-up 0.4s ease;
    animation: panel-fade-up 0.4s ease;
    -webkit-box-shadow: 0 2px 0 rgba(0, 0, 0, 0.1);
    -moz-box-shadow: 0 2px 0 rgba(0, 0, 0, 0.1);
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.1);
  }

  .modal-overlay {
      background-color: #424242;
      background-color: rgba(0, 0, 0, 0.3);
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      -webkit-transition: opacity 0.4s;
      transition: opacity 0.4s;
      content: '';
      /*pointer-events: none;*/
      z-index: -1;

  }

  .modal-overlay.active {
      opacity: 1;
      z-index: 2000;
  }

  .task.active {

  }

  .cardMini {
      position: relative;
      opacity: 1;
  }

  .cardMini .header {
      height: 10px;
      width: 100%;
  }

  .cardMini .content {
      background-color: #ffffff;
      padding: 10px;
  }

  .cardFull {
      display: none;
      position: fixed;
      background-color: #ffffff;
      opacity: 0;
      overflow: hidden;
      transition: width 0.4s 0.1s, height 0.4s 0.1s, top 0.4s 0.1s, left 0.4s 0.1s, margin 0.4s 0.1s;
  }

  .task.active .cardFull {
      opacity: 1;
      display: block;
      z-index: 2100;
  }

  .header {
      height: 10px;
      width: 100%;
  }

  .color-green {
      background-color: #00c097;
  }

  .color-red {
      background-color: #ff5e8f;
  }

  .color-yellow {
      background-color: #ffd300;
  }

  .color-grass {
      background-color: #84cd1b;
  }

  /*.color-red {*/
  /*background-color: #F6402C;*/
  /*}*/

  .color-pink {
      background-color: #EB1460;
  }

  .color-purple {
      background-color: #9C1AB1;
  }

  .color-deep_purple {
      background-color: #6633B9;
  }

  .color-indigo {
      background-color: #3D4DB7;
  }

  .color-blue {
      background-color: #1093F5;
  }

  .color-light_blue {
      background-color: #00A6F6;
  }

  .color-cyan {
      background-color: #00BBD5;
  }

  .color-teal {
      background-color: #009687;
  }

  .color-light_green {
      background-color: #88C440;
  }

  .color-lime {
      background-color: #CCDD1E;
  }

  .color-amber {
      background-color: #FFC100;
  }

  .color-orange {
      background-color: #FF9800;
  }

  .color-deep_orange {
      background-color: #FF5505;
  }

  .color-brown {
      background-color: #7A5547;
  }

  .color-grey {
      background-color: #9D9D9D;
  }

  .color-blue_grey {
      background-color: #5E7C8B;
  }

  .color-picker {
    padding: 15px;
    margin: 30px auto;
    max-width: 150px;
    /*height: auto;*/
    -webkit-box-shadow: 0 6px 20px 0 rgba(0, 0, 0, 0.19);;
    -moz-box-shadow: 0 6px 20px 0 rgba(0, 0, 0, 0.19);;
    box-shadow: 0 6px 20px 0 rgba(0, 0, 0, 0.19);;
  }

  .color {
      float: left;
      width: 20px;
      height: 20px;
      margin: 5px;
      -webkit-border-radius: 4px;
      -moz-border-radius: 4px;
      border-radius: 4px;
      cursor: pointer;
  }

  .color-input {
      float: left;
      width: 30px;
      height: 30px;
      margin: 5px;
      -webkit-border-radius: 4px;
      -moz-border-radius: 4px;
      border-radius: 4px;
      cursor: pointer;
  }

  #gal-picker {
      top: -1000px;
      lert: -1000px;
      opacity: 0;
      position: fixed;
      overflow: hidden;
      background-color: #ffffff;
      z-index: 0;
  }

  #gal-picker.active {
      opacity: 1;
      margin: auto auto;
      top: 0;
      lert: 0;
      z-index: 2200;

  }

  .color::after {
      content: '';
      width: 3px;
      height: 6px;
      border: solid #fff;
      border-width: 0 2px 2px 0;
      transform: rotate(45deg);
      margin: 5px 7px;
      display: none;
  }

  .color:hover {
      -webkit-transform: scale(1.2);
      -moz-transform: scale(1.2);
      -ms-transform: scale(1.2);
      -o-transform: scale(1.2);
      transform: scale(1.2);;
  }
</style>