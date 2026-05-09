import { reactive, toRefs } from 'vue'

export const createPinia = () => ({
  install() {}
})

export const defineStore = (_id, options) => {
  let storeInstance = null

  return function useStore() {
    if (storeInstance) return storeInstance

    const state = reactive(options.state ? options.state() : {})
    const store = state

    const actions = options.actions || {}
    Object.keys(actions).forEach((key) => {
      store[key] = actions[key].bind(store)
    })

    storeInstance = store
    return storeInstance
  }
}

export const storeToRefs = (store) => toRefs(store)
