export const createPersistedStatePlugin = ({ storageKey = null, storage = null } = {}) => {
  const isPlainObject = (value) => {
    if (value === null || typeof value !== 'object') {
      return false
    }

    return Object.getPrototypeOf(value) === Object.prototype || Object.getPrototypeOf(value) === null
  }

  const getStorage = () => {
    if (storage) {
      return storage
    }

    if (typeof window === 'undefined') {
      return null
    }

    return window.localStorage ?? null
  }

  const resolveKey = (store, persistOption) => {
    if (persistOption && typeof persistOption === 'object' && typeof persistOption.key === 'string') {
      return persistOption.key
    }

    if (typeof storageKey === 'function') {
      return storageKey(store)
    }

    if (typeof storageKey === 'string') {
      return storageKey
    }

    return persistOption === true ? store.$id : `${store.$id}`
  }

  const resolvePaths = (persistOption) => {
    if (persistOption && typeof persistOption === 'object' && Array.isArray(persistOption.paths)) {
      return persistOption.paths
    }

    return null
  }

  const pickFromState = (state, paths) => {
    if (!paths || paths.length === 0) {
      return state
    }

    return paths.reduce((carry, path) => {
      const segments = path.split('.')
      let current = state
      let target = carry

      segments.forEach((segment, index) => {
        if (current == null || typeof current !== 'object') {
          return
        }

        current = current[segment]

        if (index === segments.length - 1) {
          target[segment] = current
          return
        }

        if (!target[segment] || typeof target[segment] !== 'object') {
          target[segment] = {}
        }

        target = target[segment]
      })

      return carry
    }, {})
  }

  return ({ store, options }) => {
    const persistOption = options.persist

    if (!persistOption) {
      return
    }

    const resolvedStorage = getStorage()

    if (!resolvedStorage) {
      return
    }

    const key = resolveKey(store, persistOption)
    const paths = resolvePaths(persistOption)

    try {
      const raw = resolvedStorage.getItem(key)

      if (raw) {
        const parsed = JSON.parse(raw)
        let normalized = {}

        if (Array.isArray(parsed)) {
          normalized = { journey: parsed }
        } else if (isPlainObject(parsed)) {
          normalized = parsed
        }

        store.$patch(normalized)
      }
    } catch (error) {
      console.warn(`[pinia:persistedState] Failed to hydrate store "${store.$id}"`, error)
    }

    store.$subscribe(
      (_mutation, state) => {
        try {
          const data = pickFromState(state, paths)
          resolvedStorage.setItem(key, JSON.stringify(data))
        } catch (error) {
          console.warn(`[pinia:persistedState] Failed to persist store "${store.$id}"`, error)
        }
      },
      { detached: true }
    )
  }
}


