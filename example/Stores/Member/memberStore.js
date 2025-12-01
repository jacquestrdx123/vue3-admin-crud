import { defineStore } from 'pinia'
import { usePage } from '@inertiajs/vue3'

export const useMemberStore = defineStore('member', {
  state: () => ({
    member: null,
    products: [],
    outstandingBalance: 0,
    cpdProgress: {
      total: 0,
      required: 0,
      percentage: 0
    }
  }),
  
  getters: {
    isInGoodStanding: (state) => state.member?.is_good_standing ?? false,
    
    memberCode: (state) => state.member?.m_code,
    
    fullName: (state) => {
      if (!state.member) return ''
      return `${state.member.first_name} ${state.member.last_name}`
    },
    
    hasProducts: (state) => state.products.length > 0,
    
    hasOutstanding: (state) => state.outstandingBalance > 0,
    
    cpdCompliant: (state) => state.cpdProgress.percentage >= 100
  },
  
  actions: {
    setMember(member) {
      this.member = member
      this.products = member.products || []
    },
    
    setOutstandingBalance(balance) {
      this.outstandingBalance = balance
    },
    
    setCPDProgress(progress) {
      this.cpdProgress = progress
    },
    
    updateProfile(data) {
      if (this.member) {
        this.member = { ...this.member, ...data }
      }
    },
    
    initializeFromPage() {
      const page = usePage()
      if (page.props.member) {
        this.setMember(page.props.member)
      }
      if (page.props.outstandingBalance !== undefined) {
        this.setOutstandingBalance(page.props.outstandingBalance)
      }
      if (page.props.cpdProgress) {
        this.setCPDProgress(page.props.cpdProgress)
      }
    }
  }
})

