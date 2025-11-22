<template>
<div class="admin-container">
<aside class="sidebar">
<div class="sidebar-header">
<h2>Admin Panel</h2>
<div class="user-info" v-if="currentUser">
<p class="user-name">{{ currentUser.full_name }}</p>
<p class="user-email">{{ currentUser.email }}</p>
</div>
</div>

<nav class="sidebar-nav">
<router-link to="/admin/quan-ly-nguoi-dung">Quản lý người dùng</router-link>
<router-link to="/admin/quan-ly-san-pham">Quản lý sản phẩm</router-link>
<router-link to="/admin/quan-ly-gio-hang">Quản lý giỏ hàng</router-link>
<router-link to="/admin/thong-ke-bao-cao">Thống kê báo cáo</router-link>
</nav>

<div class="sidebar-footer">
<button @click="handleLogout" class="logout-btn" :disabled="loading">
<span v-if="loading">Đang đăng xuất...</span>
<span v-else>Đăng xuất</span>
</button>
</div>
</aside>


<main class="main-content">
<router-view />
</main>
</div>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/auth.js'

const router = useRouter()
const currentUser = ref(null)
const loading = ref(false)

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
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
    // Vẫn chuyển đến trang login dù có lỗi
    router.push('/login')
  } finally {
    loading.value = false
  }
}
</script>


<style scoped>
.admin-container {
display: flex;
height: 100vh;
}

.sidebar {
width: 200px;
background: #333;
padding: 0;
display: flex;
flex-direction: column;
color: white;
border-right: 1px solid #555;
}

.sidebar-header {
padding: 15px;
border-bottom: 1px solid #555;
}

.sidebar-header h2 {
margin: 0 0 10px 0;
font-size: 18px;
color: white;
}

.user-info {
margin-top: 10px;
padding-top: 10px;
border-top: 1px solid #555;
}

.user-name {
margin: 0 0 3px 0;
font-size: 13px;
}

.user-email {
margin: 0;
font-size: 11px;
color: #ccc;
}

.sidebar-nav {
flex: 1;
padding: 15px;
display: flex;
flex-direction: column;
gap: 5px;
overflow-y: auto;
}

.sidebar-nav a {
color: white;
text-decoration: none;
padding: 10px;
font-size: 14px;
background: #444;
border: 1px solid #555;
}

.sidebar-nav a:hover {
background: #555;
}

.sidebar-nav a.router-link-active {
background: #666;
}

.sidebar-footer {
padding: 15px;
border-top: 1px solid #555;
}

.logout-btn {
width: 100%;
padding: 10px;
background: #666;
color: white;
border: 1px solid #777;
cursor: pointer;
font-size: 14px;
}

.logout-btn:hover:not(:disabled) {
background: #777;
}

.logout-btn:disabled {
background: #999;
cursor: not-allowed;
}

.main-content {
flex: 1;
padding: 20px;
background: #fff;
overflow-y: auto;
}
</style>