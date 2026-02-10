<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Data Karyawan</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Kelola data karyawan perusahaan</p>
            </div>
            <button 
                @click="openCreateModal"
                class="mt-4 sm:mt-0 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors flex items-center space-x-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Tambah Karyawan</span>
            </button>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6 transition-colors">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cari Nama</label>
                    <input
                        v-model="filters.name"
                        type="text"
                        placeholder="Ketik nama karyawan..."
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        @input="debouncedSearch"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter Divisi</label>
                    <select
                        v-model="filters.division_id"
                        class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        @change="loadEmployees"
                    >
                        <option value="">Semua Divisi</option>
                        <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button 
                        @click="resetFilters"
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg">
            <p class="text-sm text-green-600 dark:text-green-400">{{ successMessage }}</p>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden transition-colors">
            <div v-if="loading" class="p-8 text-center">
                <svg class="animate-spin w-8 h-8 mx-auto text-indigo-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Memuat data...</p>
            </div>

            <div v-else-if="employees.length === 0" class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Tidak ada data karyawan</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Karyawan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Telepon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Divisi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jabatan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="employee in employees" :key="employee.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <img 
                                        :src="employee.image || `https://api.dicebear.com/7.x/avataaars/svg?seed=${employee.name}`" 
                                        :alt="employee.name"
                                        class="w-10 h-10 rounded-full bg-gray-200"
                                    />
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ employee.name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ employee.phone }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 rounded-full">
                                    {{ employee.division?.name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ employee.position }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <button @click="openEditModal(employee)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 mr-3">Edit</button>
                                <button @click="confirmDelete(employee)" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4 sm:mb-0">
                    Menampilkan {{ pagination.from }} - {{ pagination.to }} dari {{ pagination.total }} karyawan
                </p>
                <div class="flex space-x-2">
                    <button 
                        @click="goToPage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-gray-700 dark:text-gray-300"
                    >
                        Sebelumnya
                    </button>
                    <span class="px-3 py-1 text-sm text-gray-700 dark:text-gray-300">
                        {{ pagination.current_page }} / {{ pagination.last_page }}
                    </span>
                    <button 
                        @click="goToPage(pagination.current_page + 1)"
                        :disabled="pagination.current_page === pagination.last_page"
                        class="px-3 py-1 rounded border border-gray-300 dark:border-gray-600 text-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-gray-700 dark:text-gray-300"
                    >
                        Selanjutnya
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="closeModal">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6 transition-colors">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    {{ editingEmployee ? 'Edit Karyawan' : 'Tambah Karyawan' }}
                </h2>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telepon</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            required
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Divisi</label>
                        <select
                            v-model="form.division"
                            required
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        >
                            <option value="">Pilih Divisi</option>
                            <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jabatan</label>
                        <input
                            v-model="form.position"
                            type="text"
                            required
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto (opsional)</label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleFileChange"
                            class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        />
                    </div>

                    <div v-if="formError" class="p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-sm text-red-600 dark:text-red-400">{{ formError }}</p>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button 
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            :disabled="submitting"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-medium rounded-lg transition-colors"
                        >
                            {{ submitting ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" @click.self="showDeleteModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6 transition-colors">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Hapus Karyawan</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-6">
                    Apakah Anda yakin ingin menghapus <strong>{{ deletingEmployee?.name }}</strong>?
                </p>
                <div class="flex justify-end space-x-3">
                    <button 
                        @click="showDeleteModal = false"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
                    >
                        Batal
                    </button>
                    <button 
                        @click="deleteEmployee"
                        :disabled="deleting"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white font-medium rounded-lg transition-colors"
                    >
                        {{ deleting ? 'Menghapus...' : 'Hapus' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiGetEmployees, apiGetDivisions, apiCreateEmployee, apiUpdateEmployee, apiDeleteEmployee } from '@/api'

const route = useRoute()
const router = useRouter()

const employees = ref([])
const divisions = ref([])
const loading = ref(true)
const pagination = ref({})
const currentPage = ref(parseInt(route.query.page) || 1)
const successMessage = ref('')

const filters = ref({
    name: route.query.name || '',
    division_id: route.query.division_id || '',
})

function updateQueryString() {
    const query = {}
    if (filters.value.name) query.name = filters.value.name
    if (filters.value.division_id) query.division_id = filters.value.division_id
    if (currentPage.value > 1) query.page = currentPage.value.toString()
    router.replace({ query })
}

const showModal = ref(false)
const editingEmployee = ref(null)
const form = ref({
    name: '',
    phone: '',
    division: '',
    position: '',
})
const formFile = ref(null)
const formError = ref('')
const submitting = ref(false)

const showDeleteModal = ref(false)
const deletingEmployee = ref(null)
const deleting = ref(false)

let searchTimeout = null

async function loadEmployees() {
    loading.value = true
    updateQueryString()
    try {
        const params = { page: currentPage.value }
        if (filters.value.name) params.name = filters.value.name
        if (filters.value.division_id) params.division_id = filters.value.division_id

        const response = await apiGetEmployees(params)
        employees.value = response.data?.employees || []
        pagination.value = response.pagination || {}
    } catch (e) {
        console.error('Failed to load employees:', e)
    } finally {
        loading.value = false
    }
}

async function loadDivisions() {
    try {
        const response = await apiGetDivisions()
        divisions.value = response.data?.divisions || []
    } catch (e) {
        console.error('Failed to load divisions:', e)
    }
}

function debouncedSearch() {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        currentPage.value = 1
        loadEmployees()
    }, 300)
}

function resetFilters() {
    filters.value = { name: '', division_id: '' }
    currentPage.value = 1
    router.replace({ query: {} })
    loadEmployees()
}

function goToPage(page) {
    if (page < 1 || page > pagination.value.last_page) return
    currentPage.value = page
    loadEmployees()
}

function openCreateModal() {
    editingEmployee.value = null
    form.value = { name: '', phone: '', division: '', position: '' }
    formFile.value = null
    formError.value = ''
    showModal.value = true
}

function openEditModal(employee) {
    editingEmployee.value = employee
    form.value = {
        name: employee.name,
        phone: employee.phone,
        division: employee.division?.id || '',
        position: employee.position,
    }
    formFile.value = null
    formError.value = ''
    showModal.value = true
}

function closeModal() {
    showModal.value = false
    editingEmployee.value = null
}

function handleFileChange(e) {
    formFile.value = e.target.files[0] || null
}

async function handleSubmit() {
    submitting.value = true
    formError.value = ''

    try {
        const formData = new FormData()
        formData.append('name', form.value.name)
        formData.append('phone', form.value.phone)
        formData.append('division', form.value.division)
        formData.append('position', form.value.position)
        if (formFile.value) {
            formData.append('image', formFile.value)
        }

        let response
        if (editingEmployee.value) {
            response = await apiUpdateEmployee(editingEmployee.value.id, formData)
        } else {
            response = await apiCreateEmployee(formData)
        }

        if (response.status === 'success') {
            successMessage.value = response.message
            setTimeout(() => successMessage.value = '', 3000)
            closeModal()
            loadEmployees()
        } else {
            formError.value = response.message || 'Gagal menyimpan data'
        }
    } catch (e) {
        formError.value = 'Terjadi kesalahan'
    } finally {
        submitting.value = false
    }
}

function confirmDelete(employee) {
    deletingEmployee.value = employee
    showDeleteModal.value = true
}

async function deleteEmployee() {
    if (!deletingEmployee.value) return
    deleting.value = true

    try {
        const response = await apiDeleteEmployee(deletingEmployee.value.id)
        if (response.status === 'success') {
            successMessage.value = response.message
            setTimeout(() => successMessage.value = '', 3000)
            showDeleteModal.value = false
            loadEmployees()
        }
    } catch (e) {
        console.error('Failed to delete:', e)
    } finally {
        deleting.value = false
    }
}

onMounted(() => {
    loadDivisions()
    loadEmployees()
})
</script>
