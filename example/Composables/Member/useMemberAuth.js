import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

export function useMemberAuth() {
  const page = usePage()
  
  const member = computed(() => page.props.member)
  const isAuthenticated = computed(() => !!member.value)
  
  const logout = () => {
    router.post(route('vue.member.logout'))
  }
  
  const isInGoodStanding = computed(() => member.value?.is_good_standing ?? false)
  
  const hasProduct = (productType) => {
    if (!member.value?.products) return false
    return member.value.products.some(p => 
      p.subscribable_type.includes(productType)
    )
  }
  
  return {
    member,
    isAuthenticated,
    isInGoodStanding,
    hasProduct,
    logout
  }
}

