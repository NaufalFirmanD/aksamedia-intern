import { ref, watch, onMounted } from 'vue'

const theme = ref(localStorage.getItem('theme') || 'system')

export function useTheme() {
    function applyTheme(newTheme) {
        const root = document.documentElement
        
        if (newTheme === 'dark') {
            root.classList.add('dark')
        } else if (newTheme === 'light') {
            root.classList.remove('dark')
        } else {
            // System preference
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                root.classList.add('dark')
            } else {
                root.classList.remove('dark')
            }
        }
    }

    function setTheme(newTheme) {
        theme.value = newTheme
        localStorage.setItem('theme', newTheme)
        applyTheme(newTheme)
    }

    function initTheme() {
        applyTheme(theme.value)
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (theme.value === 'system') {
                applyTheme('system')
            }
        })
    }

    return {
        theme,
        setTheme,
        initTheme,
    }
}
