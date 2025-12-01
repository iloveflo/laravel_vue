import { createRouter, createWebHistory } from 'vue-router'

// Layout chính
import MainLayout from '../views/index.vue'

// Pages
import Home from '../views/home.vue'
import Login from '../views/login.vue'
import Register from '../views/register.vue'
import ResetPassword from '../views/resetpassword.vue'
import ForgotPassword from '../views/forgotpassword.vue'
import About from '../views/about.vue'
import Contact from '../views/contact.vue'
import Search from '../views/search.vue'

// User
import Cart from '../views/user/cart.vue'
import Profile from '../views/user/profile.vue'
import Orders from '../views/user/orders.vue'

// Products
import Products from '../views/products/index.vue'

// Áo
import tshirts from '../views/products/tshirts.vue'
import shirts from '../views/products/shirts.vue'
import polo from '../views/products/polo.vue'
import jackets from '../views/products/jackets.vue'
import hoodies from '../views/products/hoodies.vue'

// Quần
import trousers from '../views/products/trousers.vue'
import jeans from '../views/products/jeans.vue'
import shorts from '../views/products/shorts.vue'
import kaki from '../views/products/kaki.vue'
import joggers from '../views/products/joggers.vue'

// Phụ kiện
import bags from '../views/products/bags.vue'
import hats from '../views/products/hats.vue'
import belts from '../views/products/belts.vue'
import wallets from '../views/products/wallets.vue'
import socks from '../views/products/socks.vue'

// Bộ sưu tập
import newarrivals from '../views/products/newarrivals.vue'
import essentials from '../views/products/essentials.vue'
import monochrome from '../views/products/monochrome.vue'
import sale from '../views/products/sale.vue'


// Help
import shipping from '../views/help/shipping.vue'
import returns from '../views/help/returns.vue'
import size_guide from '../views/help/size_guide.vue'

// Admin
import Admin from '../views/admin.vue'
import QuanLyNguoiDung from '../views/admin/quanlynguoidung.vue'
import QuanLySanPham from '../views/admin/quanlysanpham.vue'
import QuanLyGioHang from '../views/admin/quanlygiohang.vue'
import ThongKeBaoCao from '../views/admin/thongkebaocao.vue'

const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: Home,
      },
      {
        path: 'about',
        name: 'about',
        component: About,
      },
      {
        path: 'contact',
        name: 'contact',
        component: Contact,
      },
      {
        path: 'cart',
        name: 'cart',
        component: Cart,
      },
      {
        path: 'login',
        name: 'login',
        component: Login,
      },
      {
        path: 'register',
        name: 'register',
        component: Register,
      },
      {
        path: 'forgot-password',
        name: 'forgot-password',
        component: ForgotPassword,
      },
      {
        path: 'reset-password',
        name: 'reset-password',
        component: ResetPassword,
      },
      {
        path: 'search',
        name: 'search',
        component: Search,
      },
      
      // User
      {
        path: 'user/profile',
        name: 'profile',
        component: Profile,
      },
      {
        path: 'user/orders',
        name: 'orders',
        component: Orders,
      },
      {
        path: 'user/cart',
        name: 'cart',
        component: Cart,
      },

      // Products
      {
        path: 'products',
        name: 'products',
        component: Products,
      },
      // Áo
      { path: 'products/t-shirts', name: 'tshirts', component: tshirts , meta: { category: 't-shirts', title: 'Áo Thun' } },
      { path: 'products/shirts', name: 'shirts', component: shirts , meta: { category: 'shirts', title: 'Áo Sơ Mi' } },
      { path: 'products/polo', name: 'polo', component: polo , meta: { category: 'polo', title: 'Áo Polo' } },
      { path: 'products/jackets', name: 'jackets', component: jackets , meta: { category: 'jackets', title: 'Áo Khoác / Blazer' } },
      { path: 'products/hoodies', name: 'hoodies', component: hoodies , meta: { category: 'hoodies', title: 'Hoodie & Sweatshirt' } },
      
      // Quần
      { path: 'products/trousers', name: 'trousers', component: trousers , meta: { category: 'trousers', title: 'Quần Tây' } },
      { path: 'products/jeans', name: 'jeans', component: jeans , meta: { category: 'jeans', title: 'Denim / Jeans' } },
      { path: 'products/shorts', name: 'shorts', component: shorts , meta: { category: 'shorts', title: 'Quần Short' } },
      { path: 'products/kaki', name: 'kaki', component: kaki , meta: { category: 'kaki', title: 'Quần Kaki' } },
      { path: 'products/joggers', name: 'joggers', component: joggers , meta: { category: 'joggers', title: 'Quần Jogger' } },
      
      // Phụ kiện
      { path: 'products/bags', name: 'bags', component: bags , meta: { category: 'bags', title: 'Túi Xách / Balo' } },
      { path: 'products/hats', name: 'hats', component: hats , meta: { category: 'hats', title: 'Mũ / Nón' } },
      { path: 'products/belts', name: 'belts', component: belts , meta: { category: 'belts', title: 'Thắt Lưng' } },
      { path: 'products/wallets', name: 'wallets', component: wallets , meta: { category: 'wallets', title: 'Ví Da' } },
      { path: 'products/socks', name: 'socks', component: socks , meta: { category: 'socks', title: 'Tất / Vớ' } },
      
      // Bộ sưu tập
      { path: 'products/new-arrivals', name: 'new_arrivals', component: newarrivals , meta: { category: 'new-arrivals', title: 'New Arrivals 2025' } },
      { path: 'products/essentials', name: 'essentials', component: essentials , meta: { category: 'essentials', title: 'The Essentials' } },
      { path: 'products/monochrome', name: 'monochrome', component: monochrome , meta: { category: 'monochrome', title: 'Monochrome Series' } },
      { path: 'products/sale', name: 'sale', component: sale , meta: { category: 'sale', title: 'Sale' } },

      // Help
      { path: 'help/shipping', name: 'shipping', component: shipping, meta: { title: 'Chính sách vận chuyển' } },
      { path: 'help/returns', name: 'returns', component: returns, meta: { title: 'Đổi trả & Hoàn tiền' } },
      { path: 'help/size-guide', name: 'size-guide', component: size_guide, meta: { title: 'Hướng dẫn chọn size' } },
    ]
  },
  
  // Admin (layout riêng, không dùng Header/Footer chính)
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
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0 }
  }
})

export default router