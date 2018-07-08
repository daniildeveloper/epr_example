<template>
  <div>
    <v-layout row justify-start wrap>

      <v-flex>
        <v-dialog v-model="userInviteDialog" persistent max-width="400px" v-if="hasPermission('invite_users')" >
          <v-btn color="primary" slot="activator" dark>Пригласить пользователя</v-btn>
          <v-card>
            <v-card-title>
              <span class="headline">Пригласить пользователя</span>
              <v-spacer></v-spacer>
              <v-menu bottom left>
                <v-btn @click="userInviteDialog = false" icon slot="activator">
                  <v-icon>close</v-icon>
                </v-btn>
              </v-menu>
            </v-card-title>
            <v-card-text>
              <v-container grid-list-md>
                <v-layout wrap>
                    <user-invite-view 
                      @user-invite-completed="userInviteCompleted()"
                    ></user-invite-view>
                </v-layout>
              </v-container>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click.native="userInviteDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-flex>
    
      <v-flex>
        <v-dialog v-model="newProposalDialog" persistent>
          <v-btn color="primary" slot="activator" dark>Новая заявка</v-btn>
          <v-card>
            <v-card-title>
              <span class="headline">Новая заявка</span>
              <v-spacer></v-spacer>
              <v-menu bottom left>
                <v-btn @click="newProposalDialog = false" icon slot="activator">
                  <v-icon>close</v-icon>
                </v-btn>
              </v-menu>
            </v-card-title>
            <v-card-text>
              <v-container grid-list-md>
                <v-layout wrap>
                    <new-proposal-view @new-proposal="proposalCreated" ></new-proposal-view>
                </v-layout>
              </v-container>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click.native="newProposalDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-flex>

      <!-- calendar -->
      <v-flex>
        <v-dialog v-model="workersCalendarDialog" persistent>
          <v-btn color="primary" slot="activator" dark>Календарь производства</v-btn>
          <v-card>
            <v-card-title>
              <span class="headline">Календарь работы производства</span>
              <v-spacer></v-spacer>
              <v-menu bottom left>
                <v-btn @click="workersCalendarDialog = false" icon slot="activator">
                  <v-icon>close</v-icon>
                </v-btn>
              </v-menu>
            </v-card-title>
            <v-card-text>
              <v-container grid-list-md>
                <v-layout wrap>
                  <workers-calendar-view></workers-calendar-view>
                </v-layout>
              </v-container>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click.native="workersCalendarDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-flex>
      <!-- calendar -->

      <!-- suppliesPlanDialog -->
      <v-flex v-if="hasPermission('crud_nomenclatures')">
        <v-dialog v-model="suppliesPlanDialog" persistent>
          <v-btn color="primary" slot="activator" dark>Поставки</v-btn>
          <v-card>
            <v-card-title>
              <span class="headline">Поставки</span>
              <v-spacer></v-spacer>
              <v-menu bottom left>
                <v-btn @click="suppliesPlanDialog = false" icon slot="activator">
                  <v-icon>close</v-icon>
                </v-btn>
              </v-menu>
            </v-card-title>
            <v-card-text>
              <v-container grid-list-md>
                <v-layout wrap>
                  <supply-plan-list-view></supply-plan-list-view>
                </v-layout>
              </v-container>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click.native="suppliesPlanDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-flex>
      <!-- suppliesPlanDialog -->

      <v-flex>
        <v-dialog v-model="stockNomenclaturesDialog" v-if="hasPermission('crud_nomenclatures')" persistent max-width="800px">
          <v-btn color="primary" dark slot="activator">Товары и химии</v-btn>
          <v-card>
            <v-card-title>
              <span class="headline">Товары и химии</span>
              <v-spacer></v-spacer>
              <v-menu bottom left>
                <v-btn @click="stockNomenclaturesDialog = false" icon slot="activator">
                  <v-icon>close</v-icon>
                </v-btn>
              </v-menu>
            </v-card-title>
            <v-card-text>
              <v-container grid-list-md>
                <v-layout wrap>
                    <!-- Товары -->
                    <v-layout row justify-center>
                      <v-dialog v-model="wareDialog" persistent>
                        <v-btn color="primary" dark slot="activator">Товары</v-btn>
                        <v-card>
                          <v-card-title>
                            <span class="headline">Товары</span>
                            <v-spacer></v-spacer>
                            <v-menu bottom left>
                              <v-btn @click="wareDialog = false" icon slot="activator">
                                <v-icon>close</v-icon>
                              </v-btn>
                            </v-menu>
                          </v-card-title>
                          <v-card-text>
                            <v-container grid-list-md>
                              <v-layout wrap>
                                <ware-view></ware-view>
                              </v-layout>
                            </v-container>
                          </v-card-text>
                          <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="blue darken-1" flat @click.native="wareDialog = false">Закрыть</v-btn>
                          </v-card-actions>
                        </v-card>
                      </v-dialog>
                    </v-layout>
                    <!-- Товары -->

                    <!-- Для остатков Основа -->
                    <v-layout row justify-center>
                      <v-dialog v-model="restFramework" persistent>
                        <v-btn color="primary" dark slot="activator">Основы для остатков</v-btn>
                        <v-card>
                          <v-card-title>
                            <span class="headline">Основы для остатков</span>
                            <v-spacer></v-spacer>
                            <v-menu bottom left>
                              <v-btn @click="restFramework = false" icon slot="activator">
                                <v-icon>close</v-icon>
                              </v-btn>
                            </v-menu>
                          </v-card-title>
                          <v-card-text>
                            <v-container grid-list-md>
                              <v-layout wrap>
                                <rest-framework-view></rest-framework-view>
                              </v-layout>
                            </v-container>
                          </v-card-text>
                          <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn color="blue darken-1" flat @click.native="restFramework = false">Закрыть</v-btn>
                          </v-card-actions>
                        </v-card>
                      </v-dialog>
                    </v-layout>
                    <!-- Для остатков Основа -->
                </v-layout>
              </v-container>
            </v-card-text>
            <v-card-actions>
              <v-spacer></v-spacer>
              <v-btn color="blue darken-1" flat @click.native="stockNomenclaturesDialog = false">Закрыть</v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>
      </v-flex>
    </v-layout>
    <proposal-board-view></proposal-board-view>
    <ware-rests-view></ware-rests-view>
    <!-- <charts-view></charts-view> -->
  </div>
</template>

<script>
import { mapGetters } from 'vuex';
import axios from 'axios';
import { Form } from 'vform';

import RestFramework from '~/pages/stock/rest-framework';
import Ware from '~/pages/stock/ware';
import UserInvite from '~/pages/director/user-invite';
import NewProposal from '~/pages/proposal/new-proposal';
import SingleProposal from '~/pages/proposal/single';
import ProposalBoard from '~/pages/proposal/board'
import WorkersCalendar from '~/pages/workers/calendar'
import WareRests from '~/pages/stock/ware-rests'

/**
 * Компонент для главной страницы с отображением необходимых данных
 */
export default {
  name: 'home-view',

  components: {
    'rest-framework-view': RestFramework,
    'ware-view': Ware,
    'user-invite-view': UserInvite,
    'new-proposal-view': NewProposal,
    'proposal-board-view': ProposalBoard,
    'workers-calendar-view': WorkersCalendar,
    'ware-rests-view': WareRests,

  },

  computed: mapGetters({
    user: 'authUser'
  }),

  metaInfo () {
    return { title: this.$t('home') }
  },

  data() {
    return {
      // dialogs
      stockNomenclaturesDialog: false,
      wareDialog: false,
      framework: false,
      restFramework: false,
      userInviteDialog: false,
      newProposalDialog:false,
      workersCalendarDialog: false,
      suppliesPlanDialog: false,
    }
  },

  methods: {
    proposalCreated(){
      const getactiveProposalsData = '/api/proposal/proposal';
      this.newProposalDialog = false;
      setTimeout(() => {
        axios.get(getactiveProposalsData).then(response => {
          this.blocks = response.data;
        });
      }, 2 * 1000)
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Заявка успешно создана',
        modal: false
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

    userInviteCompleted() {
      this.userInviteDialog = false
      this.$store.dispatch('responseMessage', {
        type: 'success',
        text: 'Пользователь приглашен',
        modal: false
      })
    }
  },
}
</script>

<style>

</style>
