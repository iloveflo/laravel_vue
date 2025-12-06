<template>
  <div class="products-page">
    <div class="container">
      <div class="layout">
        <div class="products-page">
          <div class="sidebar-overlay" :class="{ active: showFilters }" @click="showFilters = false"></div>

          <aside class="sidebar" :class="{ hidden: !showFilters }" @click.stop>
            <div class="filter-panel">
              <div class="filter-header">
                <h2>
                  <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                  </svg>
                  Bộ lọc
                </h2>
                <button class="close-sidebar-btn" @click="showFilters = false">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>

              <button @click="applyFilters" class="apply-btn" style="margin: 10px 0;">
                Áp dụng
              </button>

              <div class="filter-section">
                <h3>Khoảng giá - VND</h3>
                <div class="price-inputs">
                  <input v-model="filters.minPrice" type="number" placeholder="Từ" class="input" />
                  <input v-model="filters.maxPrice" type="number" placeholder="Đến" class="input" />
                </div>
              </div>

              <div class="filter-section">
                <h3>Kích cỡ</h3>
                <div class="size-buttons">
                  <button v-for="size in availableSizes" :key="size" @click="toggleFilter('sizes', size)"
                    :class="{ active: filters.sizes.includes(size) }" class="size-btn">
                    {{ size }}
                  </button>
                </div>
              </div>

              <div class="filter-section">
                <h3>Màu sắc</h3>
                <div class="color-list">
                  <label v-for="(color, idx) in availableColors" :key="idx" class="color-item">
                    <input type="checkbox" :checked="filters.colors.includes(color.name)"
                      @change="toggleFilter('colors', color.name)" />
                    <div class="color-box" :style="{ backgroundColor: color.code }" />
                    <span>{{ color.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </aside>
        </div>

        <!-- Products Grid -->
        <main class="main-content">
          <div class="header">
            <h1>Sản phẩm</h1>
            <button @click="showFilters = !showFilters" class="toggle-filter-btn">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              {{ showFilters ? 'Ẩn bộ lọc' : 'Hiện bộ lọc' }}
            </button>
          </div>

          <div class="products-grid">
            <div v-for="product in products" :key="product.id" class="product-card">
              <div class="product-image">
                <img :src="product.images?.find(img => img.is_primary)?.image_path || '/placeholder.jpg'"
                  :alt="product.name"
                  @error="(e) => e.target.src = 'https://via.placeholder.com/300x300?text=No+Image'" />
                <span v-if="product.featured" class="badge">Nổi bật</span>
              </div>
              <div class="product-info">
                <h3 class="product-name">{{ product.name }}</h3>

                <div class="product-price">
                  <div v-if="product.sale_price && parseFloat(product.sale_price) < parseFloat(product.price)"
                    class="price-sale">
                    <span class="sale-price">{{ formatPrice(product.sale_price) }}</span>
                    <span class="original-price">{{ formatPrice(product.price) }}</span>
                  </div>
                  <span v-else class="price">{{ formatPrice(product.price) }}</span>
                </div>

                <div class="product-sizes">
                  <span v-for="size in product.sizes?.slice(0, 5)" :key="size.id" class="size-tag">
                    {{ size.size }}
                  </span>
                </div>

                <div class="product-colors">
                  <div v-for="color in product.colors?.slice(0, 5)" :key="color.id" class="color-circle"
                    :style="{ backgroundColor: color.color_code }" :title="color.color_name" />
                </div>

                <button class="detail-btn" @click="gotoDetail(product)">Xem chi tiết</button>
              </div>
            </div>
          </div>

          <div v-if="loading" class="loading">
            <div class="spinner"></div>
            <p>Đang tải...</p>
          </div>

          <div v-if="!loading && products.length === 0" class="no-products">
            <p>Không tìm thấy sản phẩm nào</p>
          </div>

          <div v-if="totalPages > 1 && !loading" class="pagination-container">
            <button class="page-btn prev" :disabled="currentPage === 1" @click="changePage(currentPage - 1)">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>

            <div class="page-numbers">
              <button v-for="page in visiblePages" :key="page" class="page-btn"
                :class="{ active: currentPage === page, 'dots': page === '...' }"
                @click="page !== '...' ? changePage(page) : null" :disabled="page === '...'">
                {{ page }}
              </button>
            </div>

            <button class="page-btn next" :disabled="currentPage === totalPages" @click="changePage(currentPage + 1)">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'; // Thêm computed, bỏ onUnmounted
import { useRouter } from 'vue-router';

const router = useRouter();
const products = ref([]);
const loading = ref(false);

// Phân trang
const currentPage = ref(1);
const totalPages = ref(0); // Thêm biến tổng số trang
const itemsPerPage = 20;

const showFilters = ref(false);


const filters = reactive({
  minPrice: '',
  maxPrice: '',
  sizes: [],
  colors: []
});

const availableSizes = ref(['S', 'M', 'L', 'XL', 'XXL']);
const availableColors = ref([]);

// --- LOGIC PHÂN TRANG MỚI ---

// Tính toán các trang cần hiển thị (Ví dụ: 1 ... 4 5 6 ... 10)
const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const delta = 2; // Số trang hiển thị bên cạnh trang hiện tại
  const range = [];
  const rangeWithDots = [];
  let l;

  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
      range.push(i);
    }
  }

  for (let i of range) {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l !== 1) {
        rangeWithDots.push('...');
      }
    }
    rangeWithDots.push(i);
    l = i;
  }
  return rangeWithDots;
});

// Hàm chuyển trang
const changePage = (page) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    fetchProducts(page);
  }
};
// -----------------------------

// Fetch products
const fetchProducts = async (page = 1) => {
  // Nếu đang load thì thôi, trừ khi muốn force reload
  if (loading.value) return;

  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: page,
      per_page: itemsPerPage
    });

    if (filters.minPrice) params.append('min_price', filters.minPrice);
    if (filters.maxPrice) params.append('max_price', filters.maxPrice);
    if (filters.sizes.length > 0) params.append('sizes', filters.sizes.join(','));
    if (filters.colors.length > 0) params.append('colors', filters.colors.join(','));

    // Gọi API
    const response = await fetch(`/api/products?${params.toString()}`);
    const data = await response.json();

    // CẬP NHẬT DỮ LIỆU (Thay thế hoàn toàn, không nối thêm)
    products.value = data.data;

    // Cập nhật thông tin phân trang từ API trả về
    currentPage.value = data.current_page;
    totalPages.value = data.last_page; // Lưu ý: API của bạn cần trả về last_page

    // Trích xuất màu (Lưu ý: Chỉ lấy được màu của 20 sản phẩm hiện tại)
    const colors = new Set();
    data.data.forEach(product => {
      product.colors?.forEach(color => {
        colors.add(JSON.stringify({ name: color.color_name, code: color.color_code }));
      });
    });
    // Chỉ cập nhật màu nếu chưa có (hoặc cập nhật lại tùy logic của bạn)
    if (availableColors.value.length === 0) {
      availableColors.value = Array.from(colors).map(c => JSON.parse(c));
    }

    // Scroll lên đầu trang cho trải nghiệm tốt hơn
    window.scrollTo({ top: 0, behavior: 'smooth' });

  } catch (error) {
    console.error('Error fetching products:', error);
  } finally {
    loading.value = false;
  }
};

const gotoDetail = (product) => {
  router.push({
    name: 'product-details',
    params: { slug: product.slug },
  })
}

// Hàm kiểm tra giá
const isPriceValid = () => {
  const min = Number(filters.minPrice);
  const max = Number(filters.maxPrice);

  if (filters.minPrice && min < 0) {
    alert('Giá tối thiểu không được âm.');
    return false;
  }

  if (filters.minPrice && filters.maxPrice && min > max) {
    alert('Khoảng giá không hợp lệ: giá từ phải nhỏ hơn hoặc bằng giá đến.');
    return false;
  }

  return true;
};

// Apply filters
const applyFilters = () => {
  if (!isPriceValid()) {
    return;
  }
  // Khi filter, luôn quay về trang 1
  currentPage.value = 1;
  fetchProducts(1);
};

// Toggle filter checkbox
const toggleFilter = (type, value) => {
  if (filters[type].includes(value)) {
    filters[type] = filters[type].filter(item => item !== value);
  } else {
    filters[type].push(value);
  }
};

// Format price
const formatPrice = (price) => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(price);
};

onMounted(() => {
  if (window.innerWidth > 1024) {
    showFilters.value = true;
  }
  fetchProducts(1);
});
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.products-page {
  min-height: 100vh;
  background-color: #ffffff;
  padding-top: 50px;
}

.container {
  max-width: 100%;
  margin: 0;
  padding: 0;
}

.layout {
  display: flex;
  gap: 0;
  align-items: stretch; /* Thêm dòng này: Bắt buộc 2 cột cao bằng nhau */
}
/* --- SIDEBAR --- */
.sidebar {
  width: 280px;
  background: #000000;
  transition: all 0.4s ease;
  min-height: calc(100vh - 80px);
  overflow: hidden;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
}

.sidebar.hidden {
  width: 0;
}

/* Filter panel scrollable */
.filter-panel {
  background: #000000;
  padding: 40px 30px;
  position: sticky;
  top: 80px;
  color: #ffffff;
  height: calc(100vh - 80px);
  overflow-y: auto;
  scroll-behavior: smooth;
}

/* Custom Scrollbar */
.filter-panel::-webkit-scrollbar {
  width: 6px;
}

.filter-panel::-webkit-scrollbar-thumb {
  background: #555;
  border-radius: 3px;
}

.filter-panel::-webkit-scrollbar-track {
  background: #000;
}

/* Header Filter */
.filter-header h2 {
  font-size: 14px;
  font-weight: 400;
  margin: 0 0 20px 0;
  display: flex;
  align-items: center;
  gap: 10px;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #ffffff;
}

.icon {
  width: 18px;
  height: 18px;
}

/* Apply Button */
.apply-btn {
  width: 100%;
  padding: 14px;
  background: #ffffff;
  color: #000000;
  border: none;
  cursor: pointer;
  font-size: 12px;
  font-weight: 500;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-bottom: 30px;
  transition: all 0.3s ease;
  border-radius: 4px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

.apply-btn:hover {
  background: #000000;
  color: #ffffff;
  border: 1px solid #ffffff;
  box-shadow: 0 2px 8px rgba(255, 255, 255, 0.2);
}

.close-sidebar-btn {
  display: none;
  /* Ẩn trên desktop */
  background: none;
  border: none;
  cursor: pointer;
  color: #fff;
}

.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1999;
  /* Nằm dưới sidebar */
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s;
}

.sidebar-overlay.active {
  display: none;
  /* Desktop không cần overlay */
}

/* Filter sections */
.filter-section {
  margin-bottom: 40px;
  padding-bottom: 40px;
  border-bottom: 1px solid #333333;
}

.filter-section:last-of-type {
  border-bottom: none;
}

.filter-section h3 {
  font-size: 12px;
  font-weight: 400;
  margin: 0 0 20px 0;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #bbbbbb;
}

/* Price Inputs */
.price-inputs {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.input {
  width: 100%;
  padding: 12px 10px;
  border: none;
  border-bottom: 1px solid #333333;
  background: transparent;
  font-size: 14px;
  color: #ffffff;
  transition: border-color 0.3s, background 0.3s;
}

.input::placeholder {
  color: #777777;
}

.input:focus {
  outline: none;
  border-bottom-color: #ffffff;
  background: rgba(255, 255, 255, 0.05);
}

/* Size Buttons */
.size-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.size-btn {
  padding: 8px 16px;
  border: 1px solid #333333;
  background: transparent;
  color: #ffffff;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 12px;
  letter-spacing: 1px;
  border-radius: 4px;
}

.size-btn:hover {
  border-color: #ffffff;
  background: #ffffff;
  color: #000000;
}

.size-btn.active {
  background: #ffffff;
  color: #000000;
  border-color: #ffffff;
}

/* Color Filter */
.color-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.color-item {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.color-item:hover .color-box {
  transform: scale(1.1);
}

.color-item input {
  width: 16px;
  height: 16px;
  cursor: pointer;
  accent-color: #ffffff;
}

.color-box {
  width: 20px;
  height: 20px;
  border: 1px solid #333333;
  border-radius: 3px;
  transition: transform 0.2s ease;
}

.color-item span {
  font-size: 12px;
  letter-spacing: 0.5px;
  color: #ffffff;
}

/* --- MAIN CONTENT --- */
.main-content {
  flex: 1;
  background: #ffffff;
  padding: 100px 60px;
  display: flex;
  flex-direction: column;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 60px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.header h1 {
  font-size: 32px;
  font-weight: 300;
  margin: 0;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: #000000;
}

.toggle-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #000000;
  color: #ffffff;
  border: none;
  cursor: pointer;
  font-size: 11px;
  letter-spacing: 1px;
  text-transform: uppercase;
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1px;
  background: #e0e0e0;
  border: 1px solid #e0e0e0;
  margin-bottom: 40px;
}

.product-card {
  background: #ffffff;
  overflow: hidden;
  transition: all 0.3s;
  position: relative;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  transform: translateY(-2px);
  z-index: 2;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.product-image {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  background: #f5f5f5;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s;
}

.badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #000000;
  color: #ffffff;
  font-size: 9px;
  padding: 6px 12px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

.product-info {
  /* Tăng số 24px lên thành 40px hoặc 50px */
  padding: 24px 20px;

  flex: 1;
  display: flex;
  flex-direction: column;

  /* Thêm dòng này để nội dung dãn đều ra, nhìn sẽ đẹp hơn khi thẻ dài */
  justify-content: space-between;
}

.product-name {
  font-size: 20px;
  font-weight: 400;
  color: #000000;
  margin: 0 0 16px 0;

  /* --- SỬA PHẦN NÀY --- */
  line-height: 1.5;
  /* 1 dòng cao 30px (20px * 1.5) */
  min-height: 60px;
  /* Đặt tối thiểu 60px để chứa đủ 2 dòng */
  /* Xóa height: 40px cũ đi */

  display: -webkit-box;
  -webkit-line-clamp: 2;
  /* Giới hạn 2 dòng */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  /* Thêm dấu ... nếu dài hơn 2 dòng */

  letter-spacing: 0.5px;
}

.product-price {
  margin-bottom: 16px;
}

.price-sale {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sale-price {
  font-size: 20px;
  font-weight: 400;
  color: #f90505;
  letter-spacing: 0.5px;
}

.original-price {
  font-size: 20px;
  color: #999999;
  text-decoration: line-through;
  letter-spacing: 0.5px;
}

.price {
  font-size: 20px;
  font-weight: 400;
  color: #fd0505;
  letter-spacing: 0.5px;
}

.product-sizes {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}

.size-tag {
  font-size: 10px;
  background: #f5f5f5;
  padding: 6px 10px;
  letter-spacing: 1px;
  border: 1px solid #e0e0e0;
}

.product-colors {
  display: flex;
  gap: 6px;
  margin-bottom: 20px;
}

.color-circle {
  width: 20px;
  height: 20px;
  border: 1px solid #e0e0e0;
}

.detail-btn {
  width: 100%;
  padding: 12px;
  background: #000000;
  color: #ffffff;
  border: none;
  cursor: pointer;
  font-size: 10px;
  font-weight: 400;
  letter-spacing: 2px;
  text-transform: uppercase;
  transition: all 0.3s;
  margin-top: auto;
  /* Đẩy nút xuống dưới cùng */
}

.detail-btn:hover {
  background: #ffffff;
  color: #000000;
  outline: 1px solid #000000;
}

/* Loading & Empty State */
.loading {
  text-align: center;
  padding: 60px 0;
}

.spinner {
  display: inline-block;
  width: 40px;
  height: 40px;
  border: 2px solid #e0e0e0;
  border-top: 2px solid #000000;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}

.loading p,
.no-products p {
  margin-top: 16px;
  color: #999999;
  font-size: 11px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

.no-products {
  text-align: center;
  padding: 100px 0;
}

/* --- PAGINATION (NEW) --- */
.pagination-container {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 40px;
  margin-bottom: 20px;
  gap: 12px;
}

.page-numbers {
  display: flex;
  gap: 8px;
}

.page-btn {
  height: 40px;
  min-width: 40px;
  padding: 0 12px;
  border: 1px solid #e0e0e0;
  background-color: #ffffff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 400;
  font-size: 14px;
  color: #000000;
  transition: all 0.3s ease;
}

/* Hover effect cho nút phân trang */
.page-btn:hover:not(:disabled) {
  border-color: #000000;
  background-color: #f9f9f9;
}

/* Trang đang active */
.page-btn.active {
  background-color: #000000;
  color: #ffffff;
  border-color: #000000;
}

/* Nút bị disable (khi ở trang đầu/cuối) */
.page-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
  border-color: #f0f0f0;
}

/* Dấu ba chấm */
.page-btn.dots {
  border: none;
  cursor: default;
  background: none;
  font-weight: bold;
  letter-spacing: 2px;
}

.page-btn svg {
  width: 16px;
  height: 16px;
}

/* --- RESPONSIVE --- */
/* ... Giữ nguyên các phần CSS chung ở trên ... */

/* --- RESPONSIVE OPTIMIZATION --- */

/* 1. Tablet & Màn hình laptop nhỏ (1024px - 1400px) */
@media (max-width: 1400px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
    /* 3 cột */
  }
}

/* 2. Tablet dọc & Mobile ngang (768px - 1023px) */
@media (max-width: 1023px) {
  .layout {
    position: relative;
  }

  /* Chuyển Sidebar thành dạng Drawer (Trượt) */
  /* Hiển thị nút đóng và overlay */
  .close-sidebar-btn {
    display: block;
  }

  .sidebar-overlay.active {
    display: block;
    opacity: 1;
    visibility: visible;
  }

  .filter-header {
    display: flex;
    justify-content: space-between;
    /* Đẩy chữ Bộ lọc và nút X ra 2 bên */
    align-items: center;
  }

  /* Cấu hình Sidebar dạng Drawer (Ngăn kéo) */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 280px;
    background: #000;
    /* Màu nền sidebar */
    z-index: 2000;
    box-shadow: 4px 0 15px rgba(0, 0, 0, 0.3);

    /* Trạng thái HIỆN (showFilters = true) */
    transform: translateX(0);
    transition: transform 0.3s ease-in-out;
  }

  /* Trạng thái ẨN (showFilters = false -> có class .hidden) */
  .sidebar.hidden {
    transform: translateX(-100%);
    /* Trượt hẳn ra khỏi màn hình bên trái */
    width: 280px;
    /* Giữ nguyên width để animation mượt */
  }

  .filter-panel {
    top: 0;
    height: 100%;
    padding-top: 60px;
    /* Chừa chỗ cho nút đóng nếu có */
  }

  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    /* 2 cột */
  }

  .main-content {
    padding: 30px;
  }
}

/* 3. Mobile (Dưới 768px) */
@media (max-width: 768px) {
  .products-page {
    padding-top: 100px;
    /* Giảm khoảng cách top */
  }

  /* Header trang (Tiêu đề + Nút lọc) */
  .header {
    flex-direction: row;
    /* Giữ ngang hàng */
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    padding-bottom: 15px;
    gap: 10px;
  }

  .header h1 {
    font-size: 18px;
    /* Giảm size tiêu đề */
    letter-spacing: 1px;
    font-weight: 600;
  }

  .toggle-filter-btn {
    padding: 8px 12px;
    font-size: 10px;
    white-space: nowrap;
  }

  /* Main Content */
  .main-content {
    padding: 15px 10px;
    /* Giảm padding lề */
  }

  /* Grid Sản phẩm - Quan trọng: Giữ 2 cột trên mobile */
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1px;
    /* Giữ gap nhỏ tạo hiệu ứng border */
    border-top: 1px solid #e0e0e0;
    border-left: 1px solid #e0e0e0;
  }

  /* Tinh chỉnh thẻ sản phẩm trên mobile */
  .product-info {
    padding: 12px 10px;
    /* Giảm padding trong thẻ */
  }

  .product-name {
    font-size: 13px;
    /* Chữ tên nhỏ lại */
    min-height: 40px;
    /* Giảm chiều cao tối thiểu */
    margin-bottom: 8px;
    font-weight: 500;
    -webkit-line-clamp: 2;
  }

  .price-sale,
  .product-price {
    margin-bottom: 8px;
    flex-wrap: wrap;
    /* Cho phép giá xuống dòng nếu quá dài */
    gap: 6px;
  }

  .sale-price,
  .original-price,
  .price {
    font-size: 14px;
    /* Giá nhỏ lại */
  }

  /* Ẩn bớt size/color trên mobile nếu cần cho gọn, hoặc thu nhỏ */
  .size-tag {
    font-size: 9px;
    padding: 4px 6px;
  }

  .color-circle {
    width: 16px;
    height: 16px;
  }

  .badge {
    font-size: 8px;
    padding: 4px 8px;
  }

  /* Nút xem chi tiết */
  .detail-btn {
    padding: 8px;
    font-size: 9px;
  }

  /* Pagination trên mobile */
  .pagination-container {
    gap: 8px;
    margin-top: 30px;
  }

  .page-btn {
    height: 32px;
    min-width: 32px;
    font-size: 12px;
    padding: 0 6px;
  }
}

/* 4. Màn hình rất nhỏ (iPhone 5/SE - Dưới 375px) */
@media (max-width: 375px) {
  .products-grid {
    grid-template-columns: 1fr;
    /* Về 1 cột nếu màn hình quá bé */
  }

  .header h1 {
    font-size: 16px;
  }
}
</style>
    