<template>
  <div class="header-wrapper">
    <div class="top-bar">
      <span>MIỄN PHÍ VẬN CHUYỂN CHO ĐƠN HÀNG TỪ 500K</span>
    </div>

    <header class="site-header">
      <div class="header-container">
        
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" @click="toggleMobileMenu">
          <svg v-if="!mobileMenuOpen" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
          <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>

        <router-link to="/" class="logo">
          FLORENTIC
        </router-link>

        <nav class="nav" :class="{ 'mobile-open': mobileMenuOpen }">
          <router-link to="/" class="nav-link">TRANG CHỦ</router-link>
          
          <div class="nav-dropdown mega-parent">
            <router-link to="/products" class="nav-link">SẢN PHẨM</router-link>
            
            <div class="mega-menu">
              <div class="mega-content">
                
                <div class="mega-column">
                  <h4 class="column-title">Áo</h4>
                  <ul class="column-list">
                    <li><router-link to="/products/t-shirts">Áo Thun (T-Shirts)</router-link></li>
                    <li><router-link to="/products/shirts">Áo Sơ Mi (Shirts)</router-link></li>
                    <li><router-link to="/products/polo">Áo Polo</router-link></li>
                    <li><router-link to="/products/jackets">Áo Khoác / Blazer</router-link></li>
                    <li><router-link to="/products/hoodies">Hoodie & Sweatshirt</router-link></li>
                  </ul>
                </div>

                <div class="mega-column">
                  <h4 class="column-title">QUẦN</h4>
                  <ul class="column-list">
                    <li><router-link to="/products/trousers">Quần Tây (Trousers)</router-link></li>
                    <li><router-link to="/products/jeans">Denim / Jeans</router-link></li>
                    <li><router-link to="/products/shorts">Quần Short</router-link></li>
                    <li><router-link to="/products/kaki">Quần Kaki</router-link></li>
                    <li><router-link to="/products/joggers">Quần Jogger</router-link></li>
                  </ul>
                </div>

                <div class="mega-column">
                  <h4 class="column-title">PHỤ KIỆN</h4>
                  <ul class="column-list">
                    <li><router-link to="/products/bags">Túi Xách / Balo</router-link></li>
                    <li><router-link to="/products/hats">Mũ / Nón</router-link></li>
                    <li><router-link to="/products/belts">Thắt Lưng</router-link></li>
                    <li><router-link to="/products/wallets">Ví Da</router-link></li>
                    <li><router-link to="/products/socks">Tất / Vớ</router-link></li>
                  </ul>
                </div>

                <div class="mega-column highlight-column">
                  <h4 class="column-title">BỘ SƯU TẬP</h4>
                  <ul class="column-list">
                    <li><router-link to="/products/new-arrivals">New Arrivals 2025</router-link></li>
                    <li><router-link to="/products/essentials">The Essentials</router-link></li>
                    <li><router-link to="/products/monochrome">Monochrome Series</router-link></li>
                    <li class="mt-4"><router-link to="/products/sale" class="text-sale">VIEW ALL SALE</router-link></li>
                  </ul>
                </div>

              </div>
            </div>
          </div>

          <router-link to="/about" class="nav-link">GIỚI THIỆU</router-link>
          <router-link to="/contact" class="nav-link">LIÊN HỆ</router-link>
        </nav>

        <div class="header-actions">
          <!-- Search Box -->
          <div class="search-wrapper">
            <button class="icon-btn search-toggle" @click="toggleSearch">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
            </button>
            
            <div class="search-dropdown" :class="{ active: searchOpen }">
              <input 
                type="text" 
                v-model="searchQuery"
                placeholder="Tìm kiếm sản phẩm..."
                class="search-input"
                @keyup.enter="performSearch"
              />
              <button class="search-btn" @click="performSearch">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
              </button>
            </div>
          </div>

          <!-- User Account -->
          <div class="account-wrapper">
            <div class="icon-btn account-btn">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <!-- Tên user khi đã login -->
              <div v-if="user" class="username">{{ user.full_name }}</div>
            </div>
            <div class="account-dropdown">
              <!-- Khi chưa đăng nhập -->
              <template v-if="!user">
                <router-link to="/login" class="dropdown-item">Đăng nhập</router-link>
                <router-link to="/register" class="dropdown-item">Đăng ký</router-link>
              </template>
              <!-- Khi đã đăng nhập -->
              <template v-else>
                <router-link to="/user/profile" class="dropdown-item">Tài khoản</router-link>
                <router-link to="/user/orders" class="dropdown-item">Đơn hàng</router-link>
                <div class="divider"></div>
                <button @click="logout" class="dropdown-item logout-btn">Đăng xuất</button>
              </template>
            </div>
          </div>

          <!-- Shopping Cart -->
          <router-link to="/user/cart" class="icon-btn cart-btn">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            <span class="cart-count" v-if="cartCount > 0">{{ cartCount }}</span>
          </router-link>
        </div>
      </div>
    </header>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const email = ref('')
const rememberMe = ref(false)
const searchOpen = ref(false)
const searchQuery = ref('')
const mobileMenuOpen = ref(false)

// 👉 STATE USER
const user = ref(null)

const cartCount = ref(0);

// Hàm gọi API lấy số lượng
const fetchCartCount = async () => {
  try {
    const token = localStorage.getItem('token');
    const sessionId = localStorage.getItem('cart_session_id');

    // Nếu không có gì thì reset về 0
    if (!token && !sessionId) {
      cartCount.value = 0;
      return;
    }

    const config = {
      params: { session_id: sessionId },
      headers: token ? { 'Authorization': `Bearer ${token}` } : {}
    };

    const response = await axios.get(`/cart`, config);
    
    if (response.data && response.data.summary) {
      cartCount.value = response.data.summary.total_items;
    }
  } catch (error) {
    console.error("Lỗi lấy giỏ hàng:", error);
  }
};

// Lifecycle
onMounted(() => {
  // 1. Gọi ngay khi Header hiện ra
  fetchCartCount();

  // 2. Đăng ký lắng nghe sự kiện 'cart-updated' từ bất kỳ đâu phát ra
  window.addEventListener('cart-updated', fetchCartCount);
});

onUnmounted(() => {
  // Dọn dẹp sự kiện khi Header bị hủy (tránh lỗi memory leak)
  window.removeEventListener('cart-updated', fetchCartCount);
});

// --- FETCH USER INFO /me ---
async function fetchUser() {
  const token = localStorage.getItem('token')
  if (!token) return

  try {
    const res = await axios.get('/me', {
      headers: { Authorization: `Bearer ${token}` }
    })
    const currentUser = res.data.user || res.data // tùy backend trả về

    // Nếu admin → redirect thẳng admin
    if (currentUser.role === 'admin') {
      window.location.href = '/admin'
      return
    }

    // User thường → gán vào reactive
    user.value = {
      id: currentUser.id,
      email: currentUser.email,
      username: currentUser.username,
      full_name: currentUser.full_name,
      avatar: currentUser.avatar,
      role: currentUser.role,
    }

  } catch (err) {
    console.log('Không thể lấy thông tin user:', err)
    localStorage.removeItem('token')
    user.value = null
  }
}

// --- ON MOUNTED ---
onMounted(() => {
  // Nếu rememberedEmail tồn tại và không phải admin, auto-fill
  const remembered = localStorage.getItem('rememberedEmail')
  if (remembered) {
    email.value = remembered
    rememberMe.value = true
  }

  fetchUser()
})

// --- LOGOUT ---
function logout() {
  try {
    axios.post('/logout', {}, { headers: { Authorization: `Bearer ${localStorage.getItem('token')}` } })
  } catch (e) { /* ignore */ }

  // Clear localStorage và reactive user
  localStorage.removeItem('token')
  localStorage.removeItem('rememberedEmail')
  user.value = null

  router.push('/')
}

// ------ SEARCH FUNCTIONS ------
const toggleSearch = () => { searchOpen.value = !searchOpen.value }
const toggleMobileMenu = () => { mobileMenuOpen.value = !mobileMenuOpen.value }
const performSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/search', query: { q: searchQuery.value } })
    searchOpen.value = false
    searchQuery.value = ''
    mobileMenuOpen.value = false
  }
}
</script>


<style scoped>
.header-wrapper {
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
}

/* ----- Top Bar ----- */
.top-bar {
  background-color: #000;
  color: #fff;
  font-size: 15px;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  text-align: center;
  padding: 8px 0;
  font-weight: 600;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1001;
}

/* ----- Header - FIXED WHEN SCROLL ----- */
.site-header {
  background-color: #fff;
  border-bottom: 1px solid #000; 
  position: fixed;
  top: 32px; /* Cách top-bar */
  left: 0;
  right: 0;
  z-index: 1000;
  height: 80px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.header-container {
  max-width: 1440px;
  margin: 0 auto;
  padding: 0 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
}

/* ----- Logo ----- */
.logo {
  font-weight: 800;
  font-size: 24px;
  color: #000;
  text-decoration: none;
  letter-spacing: 4px;
  text-transform: uppercase;
  border: 2px solid #000;
  padding: 5px 10px;
  transition: all 0.3s ease;
}

.logo:hover {
  background-color: #000;
  color: #fff;
}

/* ----- Navigation Menu ----- */
.nav {
  display: flex;
  align-items: center;
  gap: 40px;
}

.nav-link {
  font-size: 13px;
  font-weight: 600;
  color: #000;
  text-decoration: none;
  text-transform: uppercase;
  letter-spacing: 1px;
  position: relative;
  padding: 28px 0;
  cursor: pointer;
  transition: color 0.3s ease;
}

.nav-link::after {
  content: '';
  position: absolute;
  width: 0;
  height: 2px;
  bottom: 20px;
  left: 0;
  background-color: #000;
  transition: width 0.3s ease;
}

.nav-link:hover::after {
  width: 100%;
}

/* ----- Mega Menu ----- */
.mega-parent {
  position: static;
}

.mega-menu {
  opacity: 0;
  visibility: hidden;
  position: absolute;
  top: 100%;
  left: 0;
  width: 100%;
  background-color: #fff;
  border-bottom: 1px solid #000;
  z-index: 99;
  transition: all 0.3s ease;
  transform: translateY(10px);
}

.mega-parent:hover .mega-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.mega-content {
  max-width: 1440px;
  margin: 0 auto;
  padding: 40px 40px 60px 40px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 40px;
}

.column-title {
  font-size: 14px;
  font-weight: 800;
  color: #000;
  margin-bottom: 24px;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  border-left: 3px solid #000;
  padding-left: 12px;
}

.column-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.column-list li {
  margin-bottom: 12px;
}

.column-list a {
  text-decoration: none;
  color: #555;
  font-size: 13px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: color 0.2s, padding-left 0.2s;
  display: block;
}

.column-list a:hover {
  color: #000;
  font-weight: 700;
  padding-left: 5px;
}

.mt-4 {
  margin-top: 24px;
}

.text-sale {
  color: #d00 !important;
  font-weight: 800 !important;
  border-bottom: 1px solid #d00;
  display: inline-block !important;
}

/* ----- Header Actions ----- */
.header-actions {
  display: flex;
  align-items: center;
  gap: 24px;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  color: #000;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: transform 0.2s, background 0.2s;
  border-radius: 4px;
}

.icon-btn:hover {
  background-color: #f5f5f5;
  transform: scale(1.05);
}

/* ----- Search Box ----- */
.search-wrapper {
  position: relative;
}

.search-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 12px;
  background: #fff;
  border: 2px solid #000;
  padding: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 300px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.search-dropdown.active {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  padding: 8px 12px;
  font-size: 14px;
  background: #f5f5f5;
}

.search-btn {
  background: #000;
  color: #fff;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: background 0.2s;
}

.search-btn:hover {
  background: #333;
}

/* ----- Account Dropdown ----- */
.account-wrapper {
  position: relative;
}

.account-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 12px;
  background: #fff;
  border: 2px solid #000;
  min-width: 180px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
}

.account-wrapper:hover .account-dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.account-dropdown .dropdown-item {
  display: block;
  padding: 12px 20px;
  color: #000;
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.2s;
}

.account-dropdown .dropdown-item:hover {
  background: #000;
  color: #fff;
}

/* Nút đăng xuất */
.account-dropdown .logout-btn {
  display: block;
  width: 100%;
  padding: 12px 20px;
  background: #fbfafa;   
  color: #080808;          
  border: none;
  text-align: left;
  font-size: 13px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: all 0.2s;
}

.account-dropdown .logout-btn:hover {
  background: #060606;       /* Hover nền trắng */
  color: #fbfafa;            /* Chữ đen */
}

.divider {
  height: 1px;
  background: #e5e5e5;
  margin: 8px 0;
}

/* ----- Cart ----- */
.cart-btn {
  position: relative;
}

.cart-count {
  position: absolute;
  top: 2px;
  right: 2px;
  background-color: #d00;
  color: #fff;
  font-size: 10px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 50%;
  padding: 2px;
}

/* ----- Responsive ----- */
@media (max-width: 1024px) {
  /* Mobile Menu Button */
  .mobile-menu-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    color: #000;
    order: -1;
  }

  .nav {
    position: fixed;
    top: 112px; /* top-bar (32px) + header (80px) */
    left: 0;
    right: 0;
    background: #fff;
    flex-direction: column;
    align-items: flex-start;
    padding: 20px;
    gap: 0;
    border-bottom: 1px solid #000;
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: all 0.3s ease;
    z-index: 999;
  }

  .nav.mobile-open {
    max-height: 80vh;
    opacity: 1;
    overflow-y: auto;
  }

  .nav-link {
    width: 100%;
    padding: 16px 0;
    border-bottom: 1px solid #e5e5e5;
  }

  .nav-link::after {
    display: none;
  }

  /* Mobile Mega Menu */
  .mega-parent {
    width: 100%;
  }

  .mega-menu {
    position: static;
    width: 100%;
    border: none;
    opacity: 1;
    visibility: visible;
    transform: none;
    display: none;
  }

  .mega-parent:hover .mega-menu {
    display: none;
  }

  .mega-parent.mobile-open .mega-menu {
    display: block;
  }

  .mega-content {
    grid-template-columns: 1fr;
    padding: 20px 0;
    gap: 20px;
  }

  .column-list a {
    padding: 8px 0;
  }

  .logo {
    font-size: 20px;
    border-width: 1px;
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
  }

  .header-container {
    padding: 0 20px;
    position: relative;
  }

  .header-actions {
    gap: 12px;
  }

  .search-dropdown {
    min-width: 250px;
    right: -20px;
  }
}

@media (min-width: 1025px) {
  .mobile-menu-btn {
    display: none;
  }
}
</style>