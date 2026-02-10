<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors">
        <Navbar v-if="isAuthenticated" />
        <main>
            <router-view />
        </main>
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import Navbar from '@/components/Navbar.vue'
import { useAuth } from '@/composables/useAuth'
import { useTheme } from '@/composables/useTheme'

const { isAuthenticated } = useAuth()
const { initTheme } = useTheme()

onMounted(() => {
    initTheme()
})
</script>
