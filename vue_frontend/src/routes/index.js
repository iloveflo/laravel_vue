import { createRouter, createWebHistory } from 'vue-router'
import Index from '../views/index.vue'
import Login from '../views/login.vue'
import Register from '../views/register.vue'
import Admin from '../views/admin.vue'
import authService from '../services/auth.js'

import QuanLyNguoiDung from '../views/admin/quanlynguoidung.vue'
import QuanLySanPham from '../views/admin/quanlysanpham.vue'
import QuanLyGioHang from '../views/admin/quanlygiohang.vue'
import ThongKeBaoCao from '../views/admin/thongkebaocao.vue'

const routes = [
  {
    path: '/',
    name: 'home',
    component: Index,
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { requiresGuest: true }
  },
  {
    path: '/admin',
    name: 'admin',
    component: Admin,
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      { path: 'quan-ly-nguoi-dung', component: QuanLyNguoiDung },
      { path: 'quan-ly-san-pham', component: QuanLySanPham },
      { path: 'quan-ly-gio-hang', component: QuanLyGioHang },
      { path: 'thong-ke-bao-cao', component: ThongKeBaoCao },
    ],
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Route guards
router.beforeEach(async (to, from, next) => {
  // Kiểm tra nếu route yêu cầu authentication
  if (to.meta.requiresAuth) {
    // Kiểm tra trong localStorage trước
    if (!authService.isAuthenticated()) {
      // Thử kiểm tra với server
      const authCheck = await authService.checkAuth()
      if (!authCheck.success) {
        next({ name: 'login', query: { redirect: to.fullPath } })
        return
      }
    }

    // Kiểm tra nếu route yêu cầu quyền admin
    if (to.meta.requiresAdmin) {
      if (!authService.isAdmin()) {
        // Thử kiểm tra lại với server
        const authCheck = await authService.checkAuth()
        if (!authCheck.success || authCheck.user?.role !== 'admin') {
          next({ name: 'login', query: { redirect: to.fullPath } })
          return
        }
      }
    }
  }

  // Kiểm tra nếu route yêu cầu guest (chưa đăng nhập)
  if (to.meta.requiresGuest) {
    if (authService.isAuthenticated()) {
      const user = authService.getCurrentUser()
      // Đã đăng nhập, redirect theo role
      if (user && user.role === 'admin') {
        next({ name: 'admin' })
      } else {
        next({ name: 'home' })
      }
      return
    }
  }

  next()
})

export default router