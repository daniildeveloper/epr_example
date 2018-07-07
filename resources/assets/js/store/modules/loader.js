import * as types from '../mutation-types';

export const state = {
    loading: false,
};

export const mutations = {
    [types.LOADING_STATUS_CHANGE] (state, loading) {
        state.loading = loading.loading
    }
};

export const actions = {
    setLoading({commit, dispatch}, payload) {
        commit(types.LOADING_STATUS_CHANGE, payload);
    }
};

export const getters = {
    loading: state => state.loading
};