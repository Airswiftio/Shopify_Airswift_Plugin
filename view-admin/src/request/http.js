import axios from 'axios'
import { getJWTToken } from '@/utils/function'
import { Message } from 'element-ui'

// Environment switching
axios.defaults.baseURL = process.env.VUE_APP_BASE_API
// Request timeout
axios.defaults.timeout = 10000

// Post request header
axios.defaults.headers['Content-Type'] = 'application/json;charset=UTF-8'

// request interceptor
axios.interceptors.request.use(
  config => {
    // Before each request is sent, judge whether there is a token. If there is, add a token to the header of the http request. Do not add a token manually every time the request is sent
    // Even if there is a token locally, it is possible that the token is expired, so the return status should be judged in the response interceptor
    const JWTToken = getJWTToken()
    JWTToken && (config.headers.Authorization = 'Bearer ' + JWTToken)
    // config.headers['Authorization'] = 'Bearer '+  JWTToken

    // Do something before sending a request
    if (config.method === 'post') {
      config.data = JSON.stringify(config.data)
    }

    return config
  },
  error => {
    return Promise.error(error)
  })

// Response interceptor
axios.interceptors.response.use(
  response => {
    if (response.status === 200) {
      return Promise.resolve(response)
    } else {
      return Promise.reject(response)
    }
  },
  // Server status code is not 200
  error => {
    if (error.response.status) {
      switch (error.response.status) {
        // 400: Go backend definition error code== one thousand
        case 400:
          // return error.response.data
          //
          break
        // // 401: 未登录
        // // 未登录则跳转登录页面，并携带当前页面的路径
        // // 在登录成功后返回当前页面，这一步需要在登录页操作。
        // case 401:
        //   router.replace({
        //     path: '/login',
        //     query: { redirect: router.currentRoute.fullPath }
        //   })
        //   break
        // // 403 token过期
        // // 登录过期对用户进行提示
        // // 清除本地token和清空vuex中token对象
        // // 跳转登录页面
        // case 403:
        //   Toast({
        //     message: '登录过期，请重新登录',
        //     duration: 1000,
        //     forbidClick: true
        //   })
        //   // 清除token
        //   localStorage.removeItem('APP_LOGIN_TOKEN')
        //   store.dispatch('setToken', null)
        //   // 跳转登录页面，并将要浏览的页面fullPath传过去，登录成功后跳转需要访问的页面
        //   setTimeout(() => {
        //     router.replace({
        //       path: '/login',
        //       query: {
        //         redirect: router.currentRoute.fullPath
        //       }
        //     })
        //   }, 1000)
        //   break
        // // 404请求不存在
        case 404:
          console.log('api 404')
          error.response.data.http_code = 404
          Message.error('404 not find')

          // return Promise.reject('404')

          // Toast({
          //   message: '网络请求不存在',
          //   duration: 1500,
          //   forbidClick: true
          // })
          break
        // 其他错误，直接抛出错误提示
        default:
        // Toast({
        //   message: error.response.data.message,
        //   duration: 1500,
        //   forbidClick: true
        // })
      }
      return Promise.reject(error.response)
    }
  }
)

/**
 * get方法，对应get请求
 * @param {String} url [请求的url地址]
 * @param {Object} params [请求时携带的参数]
 */
export function get(url, params) {
  return new Promise((resolve, reject) => {
    axios.get(url, {
      params: params
    })
      .then(res => {
        resolve(res.data)
      })
      .catch(err => {
        reject(err.data)
      })
  })
}
/**
 * post方法，对应post请求
 * @param {String} url [请求的url地址]
 * @param {Object} params [请求时携带的参数]
 */
export function post(url, params) {
  return new Promise((resolve, reject) => {
    axios.post(url, params)
      .then(res => {
        resolve(res.data)
      })
      .catch(err => {
        reject(err.data)
      })
  })
}

export function del(url, params) {
  return new Promise((resolve, reject) => {
    axios.delete(url, {
      params: params
    })
      .then(res => {
        resolve(res.data)
      })
      .catch(err => {
        reject(err.data)
      })
  })
}
