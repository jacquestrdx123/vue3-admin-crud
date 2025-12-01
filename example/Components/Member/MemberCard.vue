<template>
    <div class="member-card">
        <div class="card-header">
            <div class="card-header-content">
                <div>
                    <h3 class="card-title">{{ title }}</h3>
                    <p v-if="subtitle" class="card-subtitle">{{ subtitle }}</p>
                </div>
                <div v-if="$slots.actions" class="card-actions">
                    <slot name="actions" />
                </div>
            </div>
        </div>
        <div class="card-body">
            <slot />
        </div>
        <div class="card-footer">
            <slot name="footer" />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: null
        },
        subtitle: {
            type: String,
            default: null
        }
    }
}
</script>

<style scoped>
.card-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.card-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
</style>
