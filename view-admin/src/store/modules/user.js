import { resetRouter } from '@/router'
import { getUserInfo, logIn, signUp } from '@/request/api'
import { delJWTToken, getJWTToken, setJWTToken } from '@/utils/function'
import avatar_img from '@/assets/avatar.gif'

const getDefaultState = () => {
  return {
    token: getJWTToken(),
    name: '',
    avatar: ''
  }
}

const state = getDefaultState()

const mutations = {
  RESET_STATE: (state) => {
    Object.assign(state, getDefaultState())
  },
  SET_TOKEN: (state, token) => {
    state.token = token
  },
  SET_NAME: (state, name) => {
    state.name = name
  },
  SET_AVATAR: (state, avatar) => {
    state.avatar = avatar
  }
}

const actions = {
  // user login
  login({ commit }, userInfo) {
    const { username, password } = userInfo
    return new Promise((resolve, reject) => {
      logIn({ username: username.trim(), password: password }).then(response => {
        const { data } = response
        commit('SET_TOKEN', data)
        setJWTToken(data)
        resolve(response)
      }).catch(error => {
        reject(error)
      })
    })
  },
  sign_up({ commit }, userInfo) {
    const { username, password } = userInfo
    return new Promise((resolve, reject) => {
      signUp({ username: username.trim(), password: password }).then(response => {
        const { data } = response
        commit('SET_TOKEN', data)
        setJWTToken(data)
        resolve(response)
      }).catch(error => {
        reject(error)
      })
    })
  },

  // get user info
  getInfo({ commit, state }) {
    return new Promise((resolve, reject) => {
      getUserInfo().then(response => {
        const { code, data } = response
        if (code === 1) {
          commit('SET_NAME', data.username)
          commit('SET_AVATAR', avatar_img)
        }
        resolve(response)
      }).catch(error => {
        reject(error)
      })
    })
  },

  // user logout
  logout({ commit, state }) {
    return new Promise((resolve, reject) => {
      delJWTToken() // must remove  token  first
      resetRouter()
      commit('RESET_STATE')
      resolve()
    })
  },

  // remove token
  resetToken({ commit }) {
    return new Promise(resolve => {
      delJWTToken() // must remove  token  first
      commit('RESET_STATE')
      resolve()
    })
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
}

