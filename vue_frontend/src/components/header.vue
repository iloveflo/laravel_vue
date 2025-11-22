<template>
<header class="header">
<div class="left">My Website</div>
<div class="right">
  <template v-if="isAuthenticated">
    <span class="user-name">{{ currentUser?.full_name || currentUser?.email }}</span>
    <button @click="handleLogout" class="logout-btn" :disabled="loading">
      <span v-if="loading">Đang đăng xuất...</span>
      <span v-else>Đăng xuất</span>
    </button>
  </template>
  <template v-else>
    <router-link to="/login">Đăng nhập</router-link>
  </template>
</div>
</header>
</template>


<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/auth.js'

const router = useRouter()
const currentUser = ref(null)
const loading = ref(false)

const isAuthenticated = computed(() => {
  return authService.isAuthenticated()
})

onMounted(() => {
  currentUser.value = authService.getCurrentUser()
})

const handleLogout = async () => {
  if (!confirm('Bạn có chắc chắn muốn đăng xuất?')) {
    return
  }

  loading.value = true
  
  try {
    await authService.logout()
    currentUser.value = null
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
    currentUser.value = null
    router.push('/login')
  } finally {
    loading.value = false
  }
}
</script>


<style scoped>
.header {
display: flex;
justify-content: space-between;
align-items: center;
padding: 10px;
background: #eee;
}

.right {
display: flex;
align-items: center;
gap: 15px;
}

.user-name {
font-size: 14px;
color: #333;
}

.logout-btn {
padding: 5px 15px;
background: #333;
color: white;
border: 1px solid #555;
cursor: pointer;
font-size: 14px;
}

.logout-btn:hover:not(:disabled) {
background: #555;
}

.logout-btn:disabled {
background: #999;
cursor: not-allowed;
}
</style>