import Vue from 'vue'
import store from '~/store'
import Meta from 'vue-meta'
import routes from './routes'
import Router from 'vue-router'
import { sync } from 'vuex-router-sync'

Vue.use(Meta)
Vue.use(Router)

const router = make(
  routes({ authGuard, guestGuard, userInviteGuard, revokeUserAccessGuard, delegateTaskGuard, makeInventoryGuard, stockCrudGuard, stockPrivatsGuard, supplyGuard, financesGuard, manufacturyGuard })
)

sync(store, router)

export default router

/**
 * Create a new router instance.
 *
 * @param  {Array} routes
 * @return {Router}
 */
function make (routes) {
  const router = new Router({
    hasbang: false,
    routes,
    scrollBehavior,
    mode: 'history'
  })

  // Register before guard.
  router.beforeEach(async (to, from, next) => {
    if (!store.getters.authCheck && store.getters.authToken) {
      try {
        await store.dispatch('fetchUser')
      } catch (e) { }
    }

    setLayout(router, to)
    next()
  })

  // Register after hook.
  router.afterEach((to, from) => {
    router.app.$nextTick(() => {
      router.app.$loading.finish()
    })
  })

  return router
}

/**
 * Set the application layout from the matched page component.
 *
 * @param {Router} router
 * @param {Route} to
 */
function setLayout (router, to) {
  // Get the first matched component.
  const [component] = router.getMatchedComponents({ ...to })

  if (component) {
    router.app.$nextTick(() => {
      // Start the page loading bar.
      if (component.loading !== false) {
        router.app.$loading.start()
      }

      // Set application layout.
      router.app.setLayout(component.layout || '')
    })
  }
}

/**
 * Redirect to login if guest.
 *
 * @param  {Array} routes
 * @return {Array}
 */
function authGuard (routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (!store.getters.authCheck) {
      next({
        name: 'login',
        query: { redirect: to.fullPath }
      })
    } else {
      next()
    }
  })
}

/**
 * Redirect home if authenticated.
 *
 * @param  {Array} routes
 * @return {Array}
 */
function guestGuard (routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (store.getters.authCheck) {
      next({ name: 'home' })
    } else {
      next()
    }
  })
}

function userInviteGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('invite_users')) {
      next();
    } else {
      next({name: 'home'});
    }
  })
}

function revokeUserAccessGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('revoke_user_access')) {
      next();
    } else {
      next({name: 'home'});
    }
  })
}

function delegateTaskGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('delegate_task')) {
      next();
    } else {
      next({name: 'home'});
    }
  })
}

function makeInventoryGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('make_inventory')) {
      next();
    } else {
      next({name: 'home'});
    }
  })
}

function stockCrudGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('crud_nomenclatures')) {
      next();
    } else {
      next({name: 'home'});
    }
  })
}

function moneyRequestGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('money_request')) {
      next();
    } else {
      next({name: 'home'});
    }
  })
}

function stockPrivatsGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('show_stock_privats')) {
      next();
    } else {
      next({name: 'home'});
    }
  })
}

function supplyGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('supply')) {
      next();
    } else {
      next({name: 'home'});
    }
  })
}

function financesGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('finances')) {
      next();
    } else {
      next({name: 'home'});
    }
  })
}

/**
 * Manufactory checker
 * @param  {[type]} routes [description]
 * @return {[type]}        [description]
 */
function manufacturyGuard(routes) {
  return beforeEnter(routes, (to, from, next) => {
    if (hasPermission('manufactory')) {
      next();
    } else {
      next({name: 'home'});
    }
  });
}

function hasPermission(permission) {
  let permissionsArray = [];
  store.getters.authUser.data.permissions.forEach(p => {
    permissionsArray.push(p.name);
  });

  if (permissionsArray.indexOf(permission) !== -1) {
    return true;
  }
  return false
}

/**
 * Apply beforeEnter guard to the routes.
 *
 * @param  {Array} routes
 * @param  {Function} beforeEnter
 * @return {Array}
 */
function beforeEnter (routes, beforeEnter) {
  return routes.map(route => {
    return { ...route, beforeEnter }
  })
}

/**
 * @param  {Route} to
 * @param  {Route} from
 * @param  {Object|undefined} savedPosition
 * @return {Object}
 */
function scrollBehavior (to, from, savedPosition) {
  if (savedPosition) {
    return savedPosition
  }

  const position = {}

  if (to.hash) {
    position.selector = to.hash
  }

  if (to.matched.some(m => m.meta.scrollToTop)) {
    position.x = 0
    position.y = 0
  }

  return position
}
