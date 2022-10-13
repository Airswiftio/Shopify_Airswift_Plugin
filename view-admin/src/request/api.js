import { del, get, post } from './http'

export const logIn = p => post('logIn', p).then((res) => { return res }).catch((ee) => { return ee })
export const signUp = p => post('signUp', p)
export const getUserInfo = p => get('getUserInfo', p).then((res) => { return res }).catch((ee) => { return ee })
export const appList = p => get('appList', p)
export const createApp = p => post('createApp', p)
export const editApp = p => post('editApp', p)
export const delApp = p => del('delApp', p)
export const getApp = p => get('getApp', p)

