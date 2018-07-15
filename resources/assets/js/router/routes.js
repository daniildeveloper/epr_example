export default ({
  authGuard,
  guestGuard,
  userInviteGuard,
  revokeUserAccessGuard,
  delegateTaskGuard,
  makeInventoryGuard,
  stockCrudGuard,
  stockPrivatsGuard,
  supplyGuard,
  financesGuard,
  manufacturyGuard
}) => [
  ...userInviteGuard([{
    path: '/user/invite',
    name: 'user.invite',
    component: require('~/pages/director/user-invite.vue'),
  }, {
    path: '/user/list',
    name: 'user.list',
    component: require('~/pages/users/list.vue'),
  }, {
    path: '/user/permissions',
    name: 'user.permissions',
    component: require('~/pages/users/permissionstable.vue'),
  }, ]),

  // finances pages
  ...financesGuard([{
    name: 'finances',
    path: '/finances',
    component: require('~/pages/finances.vue'),
  }, {
    name: 'transactions',
    path: '/transactions',
    component: require('~/pages/purses/money-transactons-list.vue'),
  }, ]),
  // finances pages

  // Authenticated routes.
  ...authGuard([{
      path: '/database',
      name: 'database',
      component: require('~/pages/database.vue'),
    }, {
      path: '/test/test',
      name: 'test',
      component: require('~/pages/test.vue')
    }, {
      path: '/purse/new',
      component: require('~/pages/purses/new-purse.vue'),
      name: 'new.purse'
    }, {
      path: '/',
      name: 'welcome',
      component: require('~/pages/home.vue')
    }, {
      path: '/home',
      name: 'home',
      component: require('~/pages/home.vue')
    }, {
      path: '/settings',
      component: require('~/pages/settings/index.vue'),
      children: [{
        path: '',
        redirect: {
          name: 'settings.profile'
        }
      }, {
        path: 'profile',
        name: 'settings.profile',
        component: require('~/pages/settings/profile.vue')
      }, {
        path: 'password',
        name: 'settings.password',
        component: require('~/pages/settings/password.vue')
      }]
    },

    // stock routes
    {
      path: '/stock/frameworks',
      name: 'stock.frameworks',
      component: require('~/pages/stock/framework.vue')
    },
    // end stock routes

    // task routes
    {
      path: '/todos',
      name: 'todo.list',
      component: require('~/pages/users/task/tasklist.vue'),
    },
    // end task routes

    // proposal
    {
      path: '/proposal/new',
      name: 'proposal.new',
      component: require('~/pages/proposal/new-proposal.vue')
    },
    // clients and objects components
    // end clients and objuects components
    // 
    {
      path: '/user/table',
      name: 'user.table',
      component: require('~/pages/users/permissionstable.vue'),
    },

    // {
    //   path: '/stock/new-rest2real',
    //   name: 'stock.new-rest2real',
    //   component: require('~/pages/stock/create/rest-to-real.vue'),
    // },

    // archive
    {
      path: '/archive/list',
      name: 'archive.list',
      component: require('~/pages/actions/action.page.vue'),
    },
  ]),

  // Guest routes.
  ...guestGuard([{
    path: '/login',
    name: 'login',
    component: require('~/pages/auth/login.vue')
  }, {
    path: '/password/reset',
    name: 'password.request',
    component: require('~/pages/auth/password/email.vue')
  }, {
    path: '/password/reset/:token',
    name: 'password.reset',
    component: require('~/pages/auth/password/reset.vue')
  }]),

  ...manufacturyGuard(([
      {
        path: '/manufactory',
        name: 'manufactory',
        component: require('~/pages/manufactory.vue'),
      }
    ])),

  {
    path: '*',
    component: require('~/pages/errors/404.vue')
  }
]
