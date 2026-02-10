import { ref, computed } from 'vue'

const token = ref(localStorage.getItem('token') || null)
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))

export function useAuth() {
    const isAuthenticated = computed(() => !!token.value)

    function setAuth(newToken, newUser) {
        token.value = newToken
        user.value = newUser
        localStorage.setItem('token', newToken)
        localStorage.setItem('user', JSON.stringify(newUser))
    }

    function clearAuth() {
        token.value = null
        user.value = null
        localStorage.removeItem('token')
        localStorage.removeItem('user')
    }

    function updateUser(newUserData) {
        user.value = { ...user.value, ...newUserData }
        localStorage.setItem('user', JSON.stringify(user.value))
    }

    return {
        token,
        user,
        isAuthenticated,
        setAuth,
        clearAuth,
        updateUser,
    }
}
