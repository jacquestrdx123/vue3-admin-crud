import { defineStore } from 'pinia'
import axios from 'axios'

const STORAGE_KEY = 'navigation_journey'
const MAX_ENTRIES = 100
const POST_THRESHOLD = 10
const SENSITIVE_KEYS = new Set([
  'password',
  'password_confirmation',
  'current_password',
  'token',
  'secret',
  'otp',
  'passcode',
])

const createVisitId = () => {
  if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
    return crypto.randomUUID()
  }

  return `${Date.now()}-${Math.random().toString(16).slice(2)}`
}

const truncate = (value) => {
  if (typeof value !== 'string') {
    return value
  }

  if (value.length <= 500) {
    return value
  }

  return `${value.slice(0, 500)}…`
}

const sanitizeValue = (value) => {
  if (value instanceof File || value instanceof Blob) {
    return { type: value.constructor.name, name: value.name, size: value.size }
  }

  if (value instanceof Date) {
    return value.toISOString()
  }

  if (typeof value === 'string') {
    return truncate(value)
  }

  if (Array.isArray(value)) {
    return value.slice(0, 50).map((item) => sanitizeValue(item))
  }

  if (value && typeof value === 'object') {
    return sanitizePayload(value)
  }

  if (typeof value === 'number') {
    return Number.isFinite(value) ? value : null
  }

  if (typeof value === 'boolean' || value === null) {
    return value
  }

  if (typeof value === 'undefined') {
    return null
  }

  if (typeof value === 'function') {
    return '[function]'
  }

  if (typeof value === 'symbol') {
    return value.toString()
  }

  return null
}

const sanitizePayload = (payload) => {
  if (!payload || typeof payload !== 'object') {
    return payload ?? null
  }

  if (payload instanceof FormData) {
    const entries = {}

    payload.forEach((value, key) => {
      if (SENSITIVE_KEYS.has(key)) {
        entries[key] = '[redacted]'
        return
      }

      entries[key] = sanitizeValue(value)
    })

    return entries
  }

  if (Array.isArray(payload)) {
    return payload.slice(0, 50).map((item) => sanitizePayload(item))
  }

  return Object.entries(payload).reduce((carry, [key, value]) => {
    if (SENSITIVE_KEYS.has(key)) {
      carry[key] = '[redacted]'
    } else {
      carry[key] = sanitizeValue(value)
    }

    return carry
  }, {})
}

export const useNavigationStore = defineStore('navigation', {
  state: () => ({
    journey: [],
    sessionId: null,
    isRecording: true,
    lastVisitId: null,
    unpostedEntries: [],
  }),

  persist: {
    key: STORAGE_KEY,
    paths: ['journey', 'sessionId', 'isRecording', 'lastVisitId', 'unpostedEntries'],
  },

  getters: {
    latest: (state) => {
      return state.journey[0] ?? null
    },

    journeyCount: (state) => state.journey.length,

    byStatus: (state) => (status) => {
      return state.journey.filter((entry) => entry.status === status)
    },
  },

  actions: {
    initialize() {
      if (!this.sessionId && typeof window !== 'undefined') {
        const legacySessionId = window.localStorage?.getItem('navigation_session_id') ?? null

        if (legacySessionId) {
          this.sessionId = legacySessionId
          window.localStorage.removeItem('navigation_session_id')
        }
      }

      if (!this.sessionId) {
        this.sessionId = createVisitId()
      }
    },

    toggleRecording(value) {
      this.isRecording = typeof value === 'boolean' ? value : !this.isRecording
    },

    clearHistory() {
      this.journey = []
      this.lastVisitId = null
    },

    async postJourneyEntries() {
      if (this.unpostedEntries.length === 0) {
        return
      }

      try {
        await axios.post('/api/browser/log', {
          entries: this.unpostedEntries
        })
        
        this.unpostedEntries = []
      } catch (err) {
        console.error('Error posting journey entries:', err)
      }
    },

    async checkAndPostEntries() {
      if (this.unpostedEntries.length >= POST_THRESHOLD) {
        await this.postJourneyEntries()
      }
    },

    recordInitialLoad({ url, component, props }) {
      if (!this.isRecording) {
        return
      }

      const visitId = createVisitId()

      const entry = {
        id: visitId,
        type: 'initial_load',
        status: 'success',
        sessionId: this.sessionId,
        startedAt: new Date().toISOString(),
        duration: 0,
        origin: document.referrer || null,
        destination: url,
        method: 'GET',
        component,
        props: sanitizePayload(props),
      }

      this.journey.unshift(entry)
      this.journey = this.journey.slice(0, MAX_ENTRIES)

      this.unpostedEntries.push(entry)
      this.checkAndPostEntries()
    },

    recordVisitStart({ visitId, url, method, data, component }) {
      if (!this.isRecording) {
        return null
      }

      const id = visitId ?? createVisitId()

      this.lastVisitId = id

      const entry = {
        id,
        type: 'visit',
        status: 'pending',
        sessionId: this.sessionId,
        startedAt: new Date().toISOString(),
        duration: null,
        origin: window.location.href,
        destination: url,
        method,
        component,
        payload: sanitizePayload(data),
      }

      this.journey.unshift(entry)
      this.journey = this.journey.slice(0, MAX_ENTRIES)

      return id
    },

    recordVisitOutcome({ visitId, status, component, props, completedAt }) {
      if (!this.isRecording) {
        return
      }

      const id = visitId ?? this.lastVisitId
      const entry = this.journey.find((item) => item.id === id)

      if (!entry) {
        return
      }

      const finishedAt = completedAt ?? new Date()
      const startedAt = entry.startedAt ? new Date(entry.startedAt) : null

      entry.status = status
      entry.component = component ?? entry.component
      entry.props = props ? sanitizePayload(props) : entry.props ?? null
      entry.completedAt = finishedAt.toISOString()
      entry.duration = startedAt ? Math.max(0, finishedAt.getTime() - startedAt.getTime()) : 0

      this.unpostedEntries.push({ ...entry })
      this.checkAndPostEntries()
    },
  },
})


