import { createRouter, createWebHistory } from 'vue-router'
import Index from '../views/index.vue'
import Login from '../views/login.vue'
import Admin from '../views/admin.vue'


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
  },
  {
    path: '/admin',
    name: 'admin',
    component: Admin,
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

export default router