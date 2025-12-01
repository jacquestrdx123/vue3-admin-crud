<template>
    <div ref="cardWrapper" class="stat-card-wrapper">
        <div 
            class="stat-card" 
            :class="[`stat-card--${color}`, { 'active': isActive }]"
        >
            <!-- Icon or decorative element -->
            <div class="stat-icon" :class="`stat-icon--${color}`">
                <component :is="iconComponent" v-if="iconComponent" class="w-12 h-12" />
                <div v-else class="stat-icon-fallback">
                    {{ iconFallback }}
                </div>
  </div>

            <!-- Main stat display -->
            <div class="stat-main">
                <div class="stat-value">
                    <span v-if="format === 'currency'" class="stat-currency">{{ currency }}</span>
                    {{ displayValue }}
                </div>
                <div class="stat-label">{{ label }}</div>
      </div>

            <!-- View details indicator -->
            <div class="stat-view-icon">
                <svg 
                    class="w-5 h-5"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'

const cardWrapper = ref(null)

const props = defineProps({
  isActive: {
    type: Boolean,
    default: false
  },
    label: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    color: {
        type: String,
        default: 'indigo',
        validator: (value) => ['indigo', 'green', 'blue', 'red', 'purple', 'yellow', 'pink'].includes(value)
    },
    description: {
        type: String,
        default: ''
    },
    format: {
        type: String,
        default: 'number',
        validator: (value) => ['number', 'currency', 'percentage'].includes(value)
    },
    currency: {
        type: String,
        default: 'R'
    },
    change: {
        type: Number,
        default: null
    },
    details: {
        type: Array,
        default: () => []
    },
    icon: {
        type: String,
        default: null
    },
    statusText: {
        type: String,
        default: 'Active'
    }
})

const displayValue = computed(() => {
    if (typeof props.value === 'string') {
        return props.value
    }
    return props.value.toLocaleString()
})

const iconFallback = computed(() => {
    return props.label.charAt(0).toUpperCase()
})

const iconComponent = computed(() => {
    // You can extend this to map icon names to actual icon components
    return null
})

const changeClass = computed(() => {
    if (props.change === null) return ''
    return props.change >= 0 ? 'stat-change--positive' : 'stat-change--negative'
})
</script>

<style scoped>
.stat-card-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    height: auto;
}

.stat-card {
    position: relative;
    width: 100%;
    min-height: 140px;
    border-radius: 20px;
    padding: 1.5rem;
    background: white;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    overflow: hidden;
    border: 2px solid transparent;
}

.stat-card:hover {
    box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
    transform: translateY(-2px);
}

/* Active state with colored borders */
.stat-card.active {
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 0 0 3px currentColor;
    transform: translateY(-2px);
}

.stat-card--indigo.active { 
    border-color: #6366f1; 
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 0 0 3px rgba(99, 102, 241, 0.2);
}
.stat-card--green.active { 
    border-color: #22c55e; 
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 0 0 3px rgba(34, 197, 94, 0.2);
}
.stat-card--blue.active { 
    border-color: #3b82f6; 
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 0 0 3px rgba(59, 130, 246, 0.2);
}
.stat-card--red.active { 
    border-color: #ef4444; 
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 0 0 3px rgba(239, 68, 68, 0.2);
}
.stat-card--purple.active { 
    border-color: #a855f7; 
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 0 0 3px rgba(168, 85, 247, 0.2);
}
.stat-card--yellow.active { 
    border-color: #eab308; 
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 0 0 3px rgba(234, 179, 8, 0.2);
}
.stat-card--pink.active { 
    border-color: #ec4899; 
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 0 0 3px rgba(236, 72, 153, 0.2);
}

.stat-icon {
  position: absolute;
    top: 1.5rem;
    right: 1.5rem;
    width: 3rem;
    height: 3rem;
    border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
    transition: all 0.4s ease;
}

.stat-icon--indigo { background: #eef2ff; color: #6366f1; }
.stat-icon--green { background: #f0fdf4; color: #22c55e; }
.stat-icon--blue { background: #eff6ff; color: #3b82f6; }
.stat-icon--red { background: #fef2f2; color: #ef4444; }
.stat-icon--purple { background: #faf5ff; color: #a855f7; }
.stat-icon--yellow { background: #fefce8; color: #eab308; }
.stat-icon--pink { background: #fdf2f8; color: #ec4899; }

.stat-icon-fallback {
    font-size: 1.25rem;
    font-weight: 700;
}

.stat-main {
    margin-top: 0.5rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    color: #1f2937;
    margin-bottom: 0.5rem;
    transition: color 0.4s ease;
}

.stat-currency {
    font-size: 1.25rem;
    font-weight: 600;
    margin-right: 0.25rem;
}

.stat-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: opacity 0.4s ease;
}

.stat-view-icon {
    position: absolute;
    bottom: 1rem;
    right: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    color: #9ca3af;
    transition: all 0.3s ease;
}

.stat-card:hover .stat-view-icon {
    color: #6b7280;
}

.stat-card.active .stat-view-icon {
    color: currentColor;
}

.stat-change {
  position: absolute;
    bottom: 1rem;
    right: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    transition: all 0.4s ease;
}

.stat-change--positive {
    background: #dcfce7;
    color: #16a34a;
}

.stat-change--negative {
    background: #fee2e2;
    color: #dc2626;
}

.stat-change-icon {
    font-size: 1rem;
}

</style>
