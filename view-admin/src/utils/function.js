import defaultSettings from '@/settings'
const title = defaultSettings.title || 'Vue Admin Template'

export function getPageTitle(pageTitle) {
  if (pageTitle) {
    return `${pageTitle} - ${title}`
  }
  return `${title}`
}

import md5 from 'js-md5'

// The auxiliary key in the localStorage uses this key to handle exceptions when the value is undefined (not a string)
const localStorageKey = 'localStorageData'

/* Check whether it is a mobile access */
export function _isMobile() {
  return navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i)
}

/* set localStorage */
export function setLocal(key = '', value) {
  const vv = {}
  vv[localStorageKey] = typeof value === 'undefined' ? 'undefined' : value
  key = md5(key)
  return localStorage.setItem(key, JSON.stringify(vv))
}

/* delete localStorage */
export function deleteLocal(key = '') {
  key = md5(key)
  return localStorage.removeItem(key)
}

/* get localStorage */
export function getLocal(key = '') {
  // When the incoming key is incorrect, an empty string is returned directly
  if (typeof key !== 'string' || key === '') {
    return ''
  }

  key = md5(key)
  // When the obtained data is null or does not contain the specified string, null is returned directly
  const result = localStorage.getItem(key)
  if (result === null || result.indexOf(localStorageKey) === -1) {
    return null
  }

  // The json string is parsed into an object
  const data = json_to_obj(result)[localStorageKey]
  let undef
  return data === 'undefined' ? undef : data
}

/**
 *  Hiding string part
 *
 *  str         String to be processed
 *  frontLen    The first few reserved
 *  endLen      Reserved last few digit
 *  cha         Replaced string
 *  chaLen      How many characters to replace with the previously defined characters(Not transmitted or <=0, will be replaced with an equal number of characters)
 * */
export function hideStr(str = '', frontLen = 1, endLen = 1, cha = '*', chaLen = -1) {
  const len = chaLen <= 0 ? str.length - frontLen - endLen : chaLen
  let hideStr = ''
  for (let i = 0; i < len; i++) {
    hideStr += cha
  }
  return str.substring(0, frontLen) + hideStr + str.substring(str.length - endLen)
}

/* Json String to Object */
export function json_to_obj(_data_, type = 'local') {
  if (typeof _data_ === 'object') {
    return _data_
  }
  if (!_data_) {
    return {}
  }
  if (type === 'local') {
    // return eval('(' + _data_ + ')')
    return JSON.parse(_data_)
  } else {
    const json_str = _data_.replace(new RegExp('&quot', 'gm'), '"')
    return JSON.parse(json_str)
  }
}

/* set JWTToken  */
export function setJWTToken(value) {
  return setLocal('JWTToken', value)
}

/* get JWTToken */
export function getJWTToken() {
  return getLocal('JWTToken')
}

/* delete JWTToken */
export function delJWTToken() {
  return deleteLocal('JWTToken')
}

/* The list array is sorted according to the fields of the objects in it */
export function arrListSort(arrList = [], key = 'id') {
  const handle = (property) => {
    return function(a, b) {
      const val1 = a[property]
      const val2 = b[property]
      return val1 - val2
    }
  }
  return arrList.sort(handle(key))
}

/* Judge whether it is empty */
export function empty(value) {
  return typeof value === 'undefined' || value === null || value === false || value.length === 0
}
