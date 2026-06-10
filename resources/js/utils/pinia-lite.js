import { reactive, toRef, computed } from 'vue'

const storeActionKeys = new WeakMap()

export const createPinia = () => ({
  install() {}
})

export const defineStore = (_id, options) => {
  let storeInstance = null

  return function useStore() {
    if (storeInstance) return storeInstance

    const state = reactive(options.state ? options.state() : {})
    const getters = options.getters || {}
    const actions = options.actions || {}
    const actionKeys = new Set(Object.keys(actions))
    const getterComputeds = {}
    const store = state

    const getterProxy = new Proxy(
      {},
      {
        get(_target, prop) {
          return getterComputeds[prop]?.value
        }
      }
    )

    Object.keys(getters).forEach((key) => {
      getterComputeds[key] = computed(() => getters[key](state, getterProxy))
      Object.defineProperty(store, key, {
        enumerable: true,
        configurable: true,
        get: () => getterComputeds[key].value
      })
    })

    Object.keys(actions).forEach((key) => {
      store[key] = actions[key].bind(store)
    })

    storeActionKeys.set(store, actionKeys)
    storeInstance = store
    return storeInstance
  }
}

export const storeToRefs = (store) => {
  const refs = {}
  const actionKeys = storeActionKeys.get(store) || new Set()

  for (const key of Object.keys(store)) {
    if (actionKeys.has(key) || typeof store[key] === 'function') {
      continue
    }
    refs[key] = toRef(store, key)
  }

  return refs
}
