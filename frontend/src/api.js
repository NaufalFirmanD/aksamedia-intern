const API_BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'

function getHeaders() {
    const token = localStorage.getItem('token')
    const headers = {
        'Accept': 'application/json',
    }
    if (token) {
        headers['Authorization'] = `Bearer ${token}`
    }
    return headers
}

export async function apiLogin(username, password) {
    const res = await fetch(`${API_BASE}/login`, {
        method: 'POST',
        headers: {
            ...getHeaders(),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ username, password }),
    })
    return res.json()
}

export async function apiLogout() {
    const res = await fetch(`${API_BASE}/logout`, {
        method: 'POST',
        headers: getHeaders(),
    })
    return res.json()
}

export async function apiGetDivisions(params = {}) {
    const query = new URLSearchParams(params).toString()
    const res = await fetch(`${API_BASE}/divisions?${query}`, {
        headers: getHeaders(),
    })
    return res.json()
}

export async function apiGetEmployees(params = {}) {
    const query = new URLSearchParams(params).toString()
    const res = await fetch(`${API_BASE}/employees?${query}`, {
        headers: getHeaders(),
    })
    return res.json()
}

export async function apiCreateEmployee(formData) {
    const res = await fetch(`${API_BASE}/employees`, {
        method: 'POST',
        headers: {
            ...getHeaders(),
        },
        body: formData,
    })
    return res.json()
}

export async function apiUpdateEmployee(id, formData) {
    formData.append('_method', 'PUT')
    const res = await fetch(`${API_BASE}/employees/${id}`, {
        method: 'POST',
        headers: {
            ...getHeaders(),
        },
        body: formData,
    })
    return res.json()
}

export async function apiDeleteEmployee(id) {
    const res = await fetch(`${API_BASE}/employees/${id}`, {
        method: 'DELETE',
        headers: getHeaders(),
    })
    return res.json()
}

export async function apiUpdateProfile(data) {
    const res = await fetch(`${API_BASE}/profile`, {
        method: 'PUT',
        headers: {
            ...getHeaders(),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
    return res.json()
}
