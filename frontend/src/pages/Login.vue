<template>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 p-4">
        <div class="w-full max-w-md">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 transition-colors">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Aksamedia</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">Masuk ke akun Anda</p>
                </div>

                <form @submit.prevent="handleLogin" class="space-y-5">
                    <div v-if="error" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username</label>
                        <input
                            v-model="form.username"
                            type="text"
                            required
                            placeholder="Masukkan username"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                        <input
                            v-model="form.password"
                            type="password"
                            required
                            placeholder="Masukkan password"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-semibold rounded-xl transition-colors flex items-center justify-center space-x-2"
                    >
                        <svg v-if="loading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span>{{ loading ? 'Memproses...' : 'Masuk' }}</span>
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    <p>Demo: username: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">admin</code> / password: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">pastibisa</code></p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { apiLogin } from '@/api'

const router = useRouter()
const { setAuth } = useAuth()

const loading = ref(false)
const error = ref('')
const form = ref({
    username: '',
    password: '',
})

async function handleLogin() {
    loading.value = true
    error.value = ''

    try {
        const response = await apiLogin(form.value.username, form.value.password)

        if (response.status === 'success') {
            setAuth(response.data.token, response.data.admin)
            router.push('/')
        } else {
            error.value = response.message || 'Login gagal'
        }
    } catch (e) {
        error.value = 'Terjadi kesalahan. Silakan coba lagi.'
    } finally {
        loading.value = false
    }
}
</script>
