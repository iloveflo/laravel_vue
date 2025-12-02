<template>
<div class="admin-container">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div v-if="currentUser" class="mt-6 user-info">
      <img v-if="currentUser.avatar" :src="avatarPath(currentUser.avatar)" class="avatar"/>
      <div class="font-semibold">{{ currentUser.full_name || currentUser.username }}</div>
      <div class="text-sm text-gray-700">{{ currentUser.email }}</div>
    </div>

    <router-link to="/admin/quan-ly-nguoi-dung">Quản lý người dùng</router-link>
    <router-link to="/admin/quan-ly-san-pham">Quản lý sản phẩm</router-link>
    <router-link to="/admin/quan-ly-gio-hang">Quản lý đơn hàng</router-link>
    <router-link to="/admin/thong-ke-bao-cao">Thống kê báo cáo</router-link>

    <div class="logout-wrap">
      <button @click="logout" class="bg-red-600 text-white">Đăng xuất</button>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="main-content">
    <router-view v-slot="{ Component }">
      <component :is="Component" v-if="Component"/>
      
      <!-- WELCOME / INFO TEXT -->
      <div v-else class="dashboard-welcome">
        <h1>Chào mừng đến với FLORENTIC Admin</h1>
        <p>Đây là trung tâm quản lý toàn bộ hệ thống bán hàng FLORENTIC. Trước khi bắt đầu, vui lòng lưu ý các thông tin sau:</p>
        
        <ul>
          <li>Chọn một mục bên trái để quản lý dữ liệu tương ứng.</li>
          <li class="warning">⚠ Không xóa dữ liệu quan trọng nếu không chắc chắn.</li>
          <li class="warning">⚠ Chỉ tạo admin mới nếu bạn có quyền Super Admin.</li>
          <li>Kiểm tra kỹ thông tin người dùng, sản phẩm và đơn hàng trước khi cập nhật.</li>
          <li>Đăng xuất sau khi hoàn thành công việc để bảo mật.</li>
        </ul>

        <p>Mọi thao tác đều được ghi lại và có thể xem lại trong báo cáo hoạt động.</p>
      </div>
    </router-view>
  </main>

</div>
</template>


<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const currentUser = ref(null)
const router = useRouter()

const fetchCurrentUser = async () => {
	try {
		// First try using cookie (Sanctum) if available
		let res = null
		try {
			res = await axios.get('/me', { withCredentials: true })
		} catch (err) {
			// fallback to token from localStorage if provided
			const token = localStorage.getItem('token')
			if (token) {
				res = await axios.get('/me', { headers: { Authorization: `Bearer ${token}` } })
			} else {
				throw err
			}
		}
		currentUser.value = res.data
	} catch (err) {
		currentUser.value = null
	}
}

onMounted(() => {
	fetchCurrentUser()
	// listen for auth changes (login/logout) from other components
	const onAuthChanged = () => fetchCurrentUser()
	window.addEventListener('auth-changed', onAuthChanged)
	// store listener so we can remove it later
	window.__onAuthChanged = onAuthChanged
})
onBeforeUnmount(() => {
	const handler = window.__onAuthChanged
	if (handler) window.removeEventListener('auth-changed', handler)
})

const logout = async () => {
  try {
    const token = localStorage.getItem('token')
    if (token) {
      // Gửi token qua header Bearer
      await axios.post(
        '/logout',
        {},
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      )
    }
  } catch (e) {
    // Ignore lỗi nếu logout trên server thất bại
    console.error('Logout error:', e)
  }

  // Xóa token local
  localStorage.removeItem('token')
  // Xóa remembered email nếu muốn (user bình thường)
  localStorage.removeItem('rememberedEmail')

  // Xóa header Authorization mặc định
  if (axios.defaults.headers.common['Authorization']) {
    delete axios.defaults.headers.common['Authorization']
  }

  // Thông báo các component khác user đã logout
  window.dispatchEvent(new Event('auth-changed'))

  // Chuyển về trang chủ
  router.push('/')
}


const backendOrigin = import.meta.env.VITE_BACKEND_URL || window.location.origin;

const avatarPath = (path) => {
	if (!path) return null
	const p = String(path)
	if (p.startsWith('http')) return p
	if (p.startsWith('/')) return backendOrigin + p
	if (p.startsWith('public/uploads/')) return backendOrigin + '/' + p.replace(/^public\//, '')
	if (p.startsWith('uploads/')) return backendOrigin + '/' + p
	if (p.startsWith('storage/')) return backendOrigin + '/' + p
	return backendOrigin + '/storage/' + p
}
</script>


<style scoped>
.admin-container {
    display: flex;
    min-height: 100vh;
    background: #f8f9fa; /* trắng xám nhẹ */
    color: #111; /* đen hơi nhạt */
    font-family: "Inter", sans-serif;
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 260px;
    background: #fff;
    border-right: 1px solid #e5e7eb;
    padding: 25px 20px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    position: sticky;
    top: 0;
    height: 100vh;
}

.user-info {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid #e5e7eb;
}

.user-info img {
    margin: 0 auto;
    border: 2px solid #ddd;
}

.user-info .font-semibold {
    margin-top: 8px;
    font-size: 16px;
    font-weight: 600;
}

.user-info .text-sm {
    color: #6b7280;
    margin-top: 2px;
}

/* ===== LINKS ===== */
.sidebar a {
    display: block;
    padding: 12px 16px;
    border-radius: 8px;
    color: #111;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    transition: 0.25s;
}

.sidebar a:hover {
    background: #f3f4f6; /* xám nhạt hover */
}

.sidebar a.router-link-active {
    background: #111;
    color: #fff;
}

/* ===== LOGOUT BUTTON ===== */
.logout-wrap {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.logout-wrap button {
    width: 100%;
    border-radius: 8px;
    padding: 10px;
    font-size: 15px;
    transition: 0.25s;
}

.logout-wrap button:hover {
    opacity: 0.8;
}

/* ===== MAIN CONTENT ===== */
.main-content {
    flex: 1;
    padding: 28px 34px;
}

</style>