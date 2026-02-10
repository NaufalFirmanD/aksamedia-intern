<template>
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Profile</h1>

        <!-- Success Message -->
        <div v-if="successMessage"
            class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 rounded-lg text-green-700 dark:text-green-300 flex items-center justify-between">
            <span>{{ successMessage }}</span>
            <button @click="successMessage = ''" class="text-green-700 dark:text-green-300 hover:opacity-70">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage"
            class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg text-red-700 dark:text-red-300 flex items-center justify-between">
            <span>{{ errorMessage }}</span>
            <button @click="errorMessage = ''" class="text-red-700 dark:text-red-300 hover:opacity-70">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Profile Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 px-6 py-8">
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 bg-white/20 backdrop-blur rounded-full flex items-center justify-center">
                        <span class="text-3xl font-bold text-white">{{ user?.name?.charAt(0)?.toUpperCase() || 'A' }}</span>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ user?.name || 'Admin' }}</h2>
                        <p class="text-blue-100">@{{ user?.username || 'admin' }}</p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="updateProfile" class="p-6 space-y-5">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
                    <input v-model="form.name" type="text" required
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                </div>

                <!-- Username (readonly) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                    <input :value="user?.username" type="text" readonly
                        class="w-full px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed" />
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input v-model="form.email" type="email" required
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Telepon</label>
                    <input v-model="form.phone" type="text" required
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" />
                </div>

                <!-- Divider -->
                <hr class="border-gray-200 dark:border-gray-700">

                <!-- Change Password Section -->
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ubah Password</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password</p>

                <!-- New Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
                    <input v-model="form.password" type="password" minlength="6"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="Minimal 6 karakter" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password</label>
                    <input v-model="form.password_confirmation" type="password"
                        class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="Ulangi password baru" />
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-2">
                    <button type="submit" :disabled="loading"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-medium rounded-lg transition flex items-center gap-2">
                        <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                        </svg>
                        {{ loading ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '@/composables/useAuth'
import { apiUpdateProfile } from '@/api'

const { user, updateUser } = useAuth()

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const form = ref({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
})

onMounted(() => {
    if (user.value) {
        form.value.name = user.value.name || ''
        form.value.email = user.value.email || ''
        form.value.phone = user.value.phone || ''
    }
})

async function updateProfile() {
    loading.value = true
    successMessage.value = ''
    errorMessage.value = ''

    try {
        const payload = {
            name: form.value.name,
            email: form.value.email,
            phone: form.value.phone,
        }

        if (form.value.password) {
            if (form.value.password !== form.value.password_confirmation) {
                errorMessage.value = 'Password dan konfirmasi password tidak cocok'
                loading.value = false
                return
            }
            payload.password = form.value.password
            payload.password_confirmation = form.value.password_confirmation
        }

        const result = await apiUpdateProfile(payload)

        if (result.status === 'success') {
            successMessage.value = 'Profile berhasil diperbarui'
            updateUser(result.data.admin)
            form.value.password = ''
            form.value.password_confirmation = ''
        } else {
            errorMessage.value = result.message || 'Gagal memperbarui profile'
        }
    } catch (err) {
        errorMessage.value = 'Terjadi kesalahan pada server'
    } finally {
        loading.value = false
    }
}
</script>
