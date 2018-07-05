import * as types from '../mutation-types';

/**
 * state
 * @type {Object}
 */
export const state = {
  client: {},
  object: {},
  proposal: {}
};

export const mutations = {
  [types.NEW_CLIENT_CREATED] (state, client) {
    state.client = client
  },

  [types.LAST_CREATED_CLIENT_NULL] (state, client)  {
    state.client = {}
  },

  [types.NEW_OBJECT_CREATED] (state, object) {
    state.object = object
  },

  [types.LAST_CREATED_OBJECT_NULL] (state, object) {
    state.object = {}
  }
};

export const actions = {

  /**
   * works when new client is created
   * @param  {[type]} options.commit   [description]
   * @param  {[type]} options.dispatch [description]
   * @param  {[type]} payload          [description]
   * @return {[type]}                  [description]
   */
  newClientCreated({commit, dispatch}, payload) {
    commit(types.NEW_CLIENT_CREATED, payload)
  },

  /**
   * Works on last client null
   * @param  {[type]} options.commit   [description]
   * @param  {[type]} options.dispatch [description]
   * @param  {[type]} payload          [description]
   * @return {[type]}                  [description]
   */
  lastClientCreatedNull({commit, dispatch}, payload) {
    commit(types.LAST_CREATED_CLIENT_NULL, payload)
  },

  newObjectCreated({commit, dispatch}, payload) {
    commit(types.NEW_OBJECT_CREATED, payload)
  },

  lastObjectCreatedNull({commit, dispatch}, payload) {
    commit(types.LAST_CREATED_OBJECT_NULL, payload)
  },
}

export const getters = {
  lastCreatedClient: state => state.client,
  lastCreatedObject: state => state.object,
}