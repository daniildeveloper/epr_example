import axios from 'axios'
import store from '~/store'
import router from '~/router'
import i18n from './vue-i18n'

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

axios.interceptors.request.use(request => {
  if (store.getters.authToken) {
    request.headers.common['Authorization'] = `${store.getters.authToken}`
  }
  return request
})

axios.interceptors.response.use(response => response, error => {
  const {
    status, data
  } = error.response

  const errorResponse = error.response
  console.log('error.response', errorResponse)

  if (status >= 500) {
    store.dispatch('responseMessage', {
      type: 'error',
      text: i18n.t('error_alert_text'),
      title: i18n.t('error_alert_title'),
      modal: true
    })
  }

  if (status === 402) {
    store.dispatch('responseMessage', {
      type: 'error',
      text: 'Недостаточно средств',
      title: 'Не хватает денежных средств на совершение данного действия',
      modal: true
    })
  }

  if (status === 403) {
    store.dispatch('responseMessage', {
      type: 'error',
      text: 'Недостаточно прав',
      title: 'Нет прав на совершение данного действия',
      modal: true
    })

    store.dispatch('logout')

    router.push({
      name: 'login'
    })
  }

  if (status === 401 && store.getters.authCheck) {
    store.dispatch('responseMessage', {
        type: 'warning',
        text: i18n.t('token_expired_alert_text'),
        title: i18n.t('token_expired_alert_title'),
        modal: true
      })
      .then(async () => {
        await store.dispatch('logout')

        router.push({
          name: 'login'
        })
      })
  }

  if (status === 400) {
    store.dispatch('logout')

    router.push({
      name: 'login'
    })
  }

  // ошибка валидации
  if (status === 422 && store.getters.authCheck) {
    store.dispatch('responseMessage', {
        type: 'warning',
        // text: error.data.message,
        title: "Ошибка валидации",
        modal: true
      })
    console.log('error.data', error.data)
  }

  return Promise.reject(error)
})
