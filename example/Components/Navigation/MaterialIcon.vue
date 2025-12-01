<template>
  <span class="material-icons-wrapper">
    <i :class="iconClass">{{ convertedIcon }}</i>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'base'
  }
})

// Convert MDI and Heroicon names to Material Icons names
const iconMap = {
  // Heroicons mappings (from Filament)
  'heroicon-o-user-group': 'groups',
  'heroicon-o-users': 'groups',
  'heroicon-o-user': 'person',
  'heroicon-o-document-text': 'description',
  'heroicon-o-document-check': 'task',
  'heroicon-o-cube': 'inventory_2',
  'heroicon-o-globe-europe-africa': 'public',
  'heroicon-o-globe': 'public',
  'heroicon-o-building-library': 'account_balance',
  'heroicon-o-building-office': 'business',
  'heroicon-o-building-office-2': 'apartment',
  'heroicon-o-map-pin': 'location_on',
  'heroicon-o-banknotes': 'payments',
  'heroicon-o-currency-dollar': 'attach_money',
  'heroicon-o-calculator': 'calculate',
  'heroicon-o-credit-card': 'credit_card',
  'heroicon-o-receipt-percent': 'receipt',
  'heroicon-o-receipt-refund': 'currency_exchange',
  'heroicon-o-document-duplicate': 'content_copy',
  'heroicon-o-academic-cap': 'school',
  'heroicon-o-briefcase': 'work',
  'heroicon-o-clipboard-document-list': 'assignment',
  'heroicon-o-clipboard-document': 'assignment',
  'heroicon-o-chart-bar': 'bar_chart',
  'heroicon-o-chart-pie': 'pie_chart',
  'heroicon-o-cog': 'settings',
  'heroicon-o-cog-6-tooth': 'settings',
  'heroicon-o-shield-check': 'verified_user',
  'heroicon-o-shield-exclamation': 'security',
  'heroicon-o-lock-closed': 'lock',
  'heroicon-o-lock-open': 'lock_open',
  'heroicon-o-key': 'vpn_key',
  'heroicon-o-envelope': 'mail',
  'heroicon-o-envelope-open': 'drafts',
  'heroicon-o-chat-bubble-left-right': 'chat',
  'heroicon-o-chat-bubble-bottom-center-text': 'message',
  'heroicon-o-bell': 'notifications',
  'heroicon-o-bell-alert': 'notifications_active',
  'heroicon-o-tag': 'label',
  'heroicon-o-folder': 'folder',
  'heroicon-o-folder-open': 'folder_open',
  'heroicon-o-rectangle-stack': 'layers',
  'heroicon-o-squares-2x2': 'dashboard',
  'heroicon-o-squares-plus': 'add_box',
  'heroicon-o-calendar': 'event',
  'heroicon-o-calendar-days': 'date_range',
  'heroicon-o-clock': 'schedule',
  'heroicon-o-arrow-path': 'sync',
  'heroicon-o-arrow-down-tray': 'download',
  'heroicon-o-arrow-up-tray': 'upload',
  'heroicon-o-arrow-uturn-left': 'undo',
  'heroicon-o-arrow-down-circle': 'arrow_circle_down',
  'heroicon-o-trash': 'delete',
  'heroicon-o-pencil': 'edit',
  'heroicon-o-plus': 'add',
  'heroicon-o-minus': 'remove',
  'heroicon-o-check': 'check',
  'heroicon-o-x-mark': 'close',
  'heroicon-o-magnifying-glass': 'search',
  'heroicon-o-funnel': 'filter_list',
  'heroicon-o-adjustments-horizontal': 'tune',
  'heroicon-o-eye': 'visibility',
  'heroicon-o-eye-slash': 'visibility_off',
  'heroicon-o-star': 'star',
  'heroicon-o-heart': 'favorite',
  'heroicon-o-bookmark': 'bookmark',
  'heroicon-o-share': 'share',
  'heroicon-o-link': 'link',
  'heroicon-o-paper-clip': 'attach_file',
  'heroicon-o-printer': 'print',
  'heroicon-o-arrow-trending-up': 'trending_up',
  'heroicon-o-arrow-trending-down': 'trending_down',
  'heroicon-o-presentation-chart-bar': 'assessment',
  'heroicon-o-table-cells': 'table_chart',
  'heroicon-o-list-bullet': 'list',
  'heroicon-o-queue-list': 'format_list_bulleted',
  'heroicon-o-phone': 'phone',
  'heroicon-o-device-phone-mobile': 'smartphone',
  'heroicon-o-computer-desktop': 'computer',
  'heroicon-o-server': 'dns',
  'heroicon-o-cloud': 'cloud',
  'heroicon-o-wifi': 'wifi',
  'heroicon-o-signal': 'signal_cellular_alt',
  'heroicon-o-home': 'home',
  'heroicon-o-home-modern': 'home_work',
  'heroicon-o-map': 'map',
  'heroicon-o-beaker': 'science',
  'heroicon-o-light-bulb': 'lightbulb',
  'heroicon-o-fire': 'whatshot',
  'heroicon-o-flag': 'flag',
  'heroicon-o-trophy': 'emoji_events',
  'heroicon-o-gift': 'card_giftcard',
  'heroicon-o-shopping-cart': 'shopping_cart',
  'heroicon-o-shopping-bag': 'shopping_bag',
  'heroicon-o-ticket': 'confirmation_number',
  'heroicon-o-finger-print': 'fingerprint',
  'heroicon-o-identification': 'badge',
  'heroicon-o-information-circle': 'info',
  'heroicon-o-question-mark-circle': 'help',
  'heroicon-o-exclamation-triangle': 'warning',
  'heroicon-o-exclamation-circle': 'error',
  'heroicon-o-check-circle': 'check_circle',
  'heroicon-o-x-circle': 'cancel',
  'heroicon-o-play': 'play_arrow',
  'heroicon-o-pause': 'pause',
  'heroicon-o-stop': 'stop',
  'heroicon-o-forward': 'forward',
  'heroicon-o-backward': 'replay',
  'heroicon-o-lifebuoy': 'support_agent',
  'heroicon-o-bars-3': 'menu',
  'heroicon-o-book-open': 'menu_book',
  'heroicon-o-chart-bar-square': 'bar_chart',
  'heroicon-o-check-badge': 'verified',
  'heroicon-o-clipboard-document-check': 'fact_check',
  'heroicon-o-document': 'description',
  'heroicon-o-document-arrow-down': 'download',
  'heroicon-o-document-arrow-up': 'upload',
  'heroicon-o-document-chart-bar': 'insert_chart',
  'heroicon-o-document-magnifying-glass': 'pageview',
  'heroicon-o-hashtag': 'tag',
  'heroicon-o-paper-airplane': 'send',
  'heroicon-o-pause-circle': 'pause_circle',
  'heroicon-o-photo': 'photo',
  'heroicon-o-puzzle-piece': 'extension',
  'heroicon-o-rectangle-group': 'dashboard',
  'heroicon-o-speaker-wave': 'volume_up',
  'heroicon-o-user-circle': 'account_circle',
  'heroicon-o-user-minus': 'person_remove',
  'heroicon-o-user-plus': 'person_add',
  'heroicon-o-wrench-screwdriver': 'build',
  
  // MDI mappings
  'mdi-home': 'home',
  'mdi-account-group': 'group',
  'mdi-account': 'person',
  'mdi-map-marker': 'place',
  'mdi-note-text': 'note',
  'mdi-file-document': 'description',
  'mdi-file-type': 'insert_drive_file',
  'mdi-package-variant': 'inventory_2',
  'mdi-package': 'package_2',
  'mdi-wifi': 'wifi',
  'mdi-server': 'dns',
  'mdi-ip': 'router',
  'mdi-layers': 'layers',
  'mdi-clock-outline': 'schedule',
  'mdi-account-plus': 'person_add',
  'mdi-wifi-settings': 'settings',
  'mdi-server-network': 'storage',
  'mdi-ip-network': 'device_hub',
  'mdi-layers-triple': 'layers',
  'mdi-clock-check': 'check_circle',
  'mdi-network': 'hub',
  'mdi-pool': 'water',
  'mdi-router': 'router',
  'mdi-router-network': 'router',
  'mdi-router-wireless': 'settings_input_antenna',
  'mdi-key': 'vpn_key',
  'mdi-backup-restore': 'backup',
  'mdi-wifi-cog': 'settings',
  'mdi-cog': 'settings',
  'mdi-check-circle': 'check_circle',
  'mdi-reply-all': 'reply_all',
  'mdi-reply': 'reply',
  'mdi-account-multiple': 'groups',
  'mdi-chart-line': 'show_chart',
  'mdi-lock-check': 'lock',
  'mdi-help-circle': 'help',
  'mdi-ticket': 'confirmation_number',
  'mdi-tag': 'label',
  'mdi-office-building': 'business',
  'mdi-comment': 'comment',
  'mdi-wifi-marker': 'wifi',
  'mdi-web': 'public',
  'mdi-currency-usd': 'attach_money',
  'mdi-file-document-outline': 'description',
  'mdi-cash-multiple': 'payments',
  'mdi-receipt': 'receipt',
  'mdi-cash-refund': 'currency_exchange',
  'mdi-format-list-bulleted': 'list',
  'mdi-monitor-dashboard': 'dashboard',
  'mdi-monitor': 'monitor',
  'mdi-domain': 'domain',
  'mdi-shield-account': 'admin_panel_settings',
  'mdi-shield-check': 'verified_user',
  'mdi-shield-link': 'link',
  'mdi-account-multiple-check': 'how_to_reg',
  'mdi-cog-outline': 'settings',
  'mdi-palette': 'palette',
  'mdi-credit-card': 'credit_card',
  'mdi-cloud': 'cloud',
  'mdi-bell-outline': 'notifications_none',
  'mdi-account-circle': 'account_circle',
  'mdi-view-dashboard': 'dashboard',
  'mdi-logout': 'logout',
  'mdi-menu-right': 'chevron_right',
  
  // Material Icons direct
  'chevron_right': 'chevron_right',
  'expand_more': 'expand_more',
}

const convertedIcon = computed(() => {
  const icon = props.icon || ''
  return iconMap[icon] || icon.replace('mdi-', '').replace(/-/g, '_')
})

const iconClass = computed(() => {
  const sizeClasses = {
    'xs': 'text-xs',
    'sm': 'text-sm',
    'base': 'text-base',
    'lg': 'text-lg',
    'xl': 'text-xl',
    '2xl': 'text-2xl',
    '3xl': 'text-3xl',
  }
  
  return ['material-icons', sizeClasses[props.size] || sizeClasses.base]
})
</script>

<style scoped>
.material-icons-wrapper {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.material-icons {
  font-family: 'Material Icons';
  font-weight: normal;
  font-style: normal;
  display: inline-block;
  line-height: 1;
  text-transform: none;
  letter-spacing: normal;
  word-wrap: normal;
  white-space: nowrap;
  direction: ltr;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
  font-feature-settings: 'liga';
}
</style>

