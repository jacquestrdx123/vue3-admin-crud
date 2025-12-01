<template>
  <div class="member-layout min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Member Navigation -->
    <MemberNavigation 
      :member="member"
      @logout="handleLogout"
    />

    <!-- Impersonation Banner -->
    <ImpersonationBanner />

    <!-- Main Content -->
    <main class="member-content">
      <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Breadcrumbs -->
        <MemberBreadcrumbs v-if="showBreadcrumbs && breadcrumbs.length > 0" :items="breadcrumbs" class="mb-4" />

        <!-- Page Content -->
        <slot />
      </div>
    </main>

    <!-- Footer -->
    <MemberFooter />

    <!-- Toast Notifications -->
    <ToastContainer />

    <!-- Chatbot Container -->
    <div id="n8n-chat"></div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import MemberNavigation from '@/Components/Member/Navigation/MemberNavigation.vue'
import MemberBreadcrumbs from '@/Components/Member/Navigation/MemberBreadcrumbs.vue'
import MemberFooter from '@/Components/Member/UI/MemberFooter.vue'
import ImpersonationBanner from '@/Components/Member/UI/ImpersonationBanner.vue'
import ToastContainer from '@/Components/UI/ToastContainer.vue'

const props = defineProps({
  showBreadcrumbs: {
    type: Boolean,
    default: true
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const member = computed(() => page.props.member)

// Use breadcrumbs from props first, fall back to global shared breadcrumbs
const breadcrumbs = computed(() => {
  if (props.breadcrumbs && props.breadcrumbs.length > 0) {
    return props.breadcrumbs
  }
  return page.props.breadcrumbs || []
})

const handleLogout = () => {
  router.post(route('vue.member.logout'))
}

// Initialize chatbot
onMounted(() => {
  // Add stylesheet
  const link = document.createElement('link')
  link.href = 'https://media.myciba.org/saiba/site/webart/style.css'
  link.rel = 'stylesheet'
  document.head.appendChild(link)

  // Load and initialize n8n chat
  import('https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js').then((module) => {
    const { createChat } = module
    
    const memberData = member.value || page.props.auth?.member
    if (!memberData) return

    const balance = memberData.balance ? `R ${(memberData.balance / 100).toFixed(2)}` : 'R 0.00'
    const historicDebtOwed = memberData.balance ? `R ${(memberData.balance / 100).toFixed(2)}` : 'R 0.00'
    const designation = memberData.designation_name || memberData.membership_type || 'Member'
    const m_code = memberData.m_code || ''
    const full_name = `${memberData.first_name || ''} ${memberData.last_name || ''}`.trim()

    createChat({
      webhookUrl: 'https://ciba.app.n8n.cloud/webhook/2b7f69ef-2a43-4dc1-866b-ae36c9c1d98a/chat',
      webhookConfig: {
        method: 'POST',
        headers: {}
      },
      target: '#n8n-chat',
      mode: 'window',
      chatInputKey: 'chatInput',
      chatSessionKey: 'sessionId',
      metadata: {
        chatMemberId: m_code,
        chatMemberFullName: full_name,
        chatMembershipType: designation,
        chatMemberAccountBalance: balance,
        chatHistoricDebtOwed: historicDebtOwed
      },
      showWelcomeScreen: false,
      defaultLanguage: 'en',
      initialMessages: [
        `Start a chat. Hi ${full_name} (${m_code}), we are here to assist you.`
      ],
      i18n: {
        en: {
          title: 'Membership Support',
          subtitle: '',
          footer: '',
          getStarted: 'New Conversation',
          inputPlaceholder: 'Type your question..'
        }
      }
    })
  }).catch((error) => {
    console.error('Failed to load chatbot:', error)
  })
})
</script>

<style scoped>
.member-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.member-content {
  flex: 1;
}
</style>

