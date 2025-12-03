<template>
  <div class="products-page">
    <div class="container">
      <div class="layout">
       <aside class="sidebar" :class="{ hidden: !showFilters }">
        <div class="filter-panel">
            <div class="filter-header">
            <h2>
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Bộ lọc
            </h2>
            </div>

            <!-- Nút Áp dụng lên đầu -->
            <button @click="applyFilters" class="apply-btn" style="margin: 10px 0;">
            Áp dụng
            </button>

            <!-- Price Filter -->
            <div class="filter-section">
            <h3>Khoảng giá - VND</h3>
            <div class="price-inputs">
                <input v-model="filters.minPrice" type="number" placeholder="Từ" class="input"/>
                <input v-model="filters.maxPrice" type="number" placeholder="Đến" class="input"/>
            </div>
            </div>

            <!-- Size Filter -->
            <div class="filter-section">
            <h3>Kích cỡ</h3>
            <div class="size-buttons">
                <button
                v-for="size in availableSizes"
                :key="size"
                @click="toggleFilter('sizes', size)"
                :class="{ active: filters.sizes.includes(size) }"
                class="size-btn"
                >
                {{ size }}
                </button>
            </div>
            </div>

            <!-- Color Filter -->
            <div class="filter-section">
            <h3>Màu sắc</h3>
            <div class="color-list">
                <label
                v-for="(color, idx) in availableColors"
                :key="idx"
                class="color-item"
                >
                <input
                    type="checkbox"
                    :checked="filters.colors.includes(color.name)"
                    @change="toggleFilter('colors', color.name)"
                />
                <div
                    class="color-box"
                    :style="{ backgroundColor: color.code }"
                />
                <span>{{ color.name }}</span>
                </label>
            </div>
            </div>

        </div>
        </aside>


        <!-- Products Grid -->
        <main class="main-content">
          <div class="header">
            <h1>Sản phẩm ({{ products.length }})</h1>
            <button @click="showFilters = !showFilters" class="toggle-filter-btn">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              {{ showFilters ? 'Ẩn bộ lọc' : 'Hiện bộ lọc' }}
            </button>
          </div>

          <div class="products-grid">
            <div
              v-for="product in products"
              :key="product.id"
              class="product-card"
            >
              <!-- Product Image -->
              <div class="product-image">
                <img
                  :src="product.images?.find(img => img.is_primary)?.image_path || '/placeholder.jpg'"
                  :alt="product.name"
                  @error="(e) => e.target.src = 'https://via.placeholder.com/300x300?text=No+Image'"
                />
                <span v-if="product.featured" class="badge">
                  Nổi bật
                </span>
              </div>

              <!-- Product Info -->
              <div class="product-info">
                <h3 class="product-name">{{ product.name }}</h3>

                <div class="product-price">
                  <div v-if="product.sale_price && parseFloat(product.sale_price) < parseFloat(product.price)" class="price-sale">
                    <span class="sale-price">{{ formatPrice(product.sale_price) }}</span>
                    <span class="original-price">{{ formatPrice(product.price) }}</span>
                  </div>
                  <span v-else class="price">{{ formatPrice(product.price) }}</span>
                </div>

                <!-- Sizes -->
                <div class="product-sizes">
                  <span
                    v-for="size in product.sizes?.slice(0, 5)"
                    :key="size.id"
                    class="size-tag"
                  >
                    {{ size.size }}
                  </span>
                </div>

                <!-- Colors -->
                <div class="product-colors">
                  <div
                    v-for="color in product.colors?.slice(0, 5)"
                    :key="color.id"
                    class="color-circle"
                    :style="{ backgroundColor: color.color_code }"
                    :title="color.color_name"
                  />
                </div>
                <button class="detail-btn" @click="gotoDetail(product)">
                  Xem chi tiết
                </button>
              </div>
            </div>
          </div>

          <!-- Loading indicator -->
          <div v-if="loading" class="loading">
            <div class="spinner"></div>
            <p>Đang tải...</p>
          </div>

          <!-- Intersection observer target -->
          <div ref="observerTarget" class="observer-target"></div>

          <!-- No more products -->
          <div v-if="!hasMore && products.length > 0" class="no-more">
            Đã hiển thị tất cả sản phẩm
          </div>

          <!-- No products found -->
          <div v-if="!loading && products.length === 0" class="no-products">
            <p>Không tìm thấy sản phẩm nào</p>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';


const router = useRouter();
const products = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const hasMore = ref(true);
const showFilters = ref(true);

const filters = reactive({
  minPrice: '',
  maxPrice: '',
  sizes: [],
  colors: []
});

const availableSizes = ref(['S', 'M', 'L', 'XL', 'XXL']);
const availableColors = ref([]);
const observerTarget = ref(null);
let observer = null;

// Fetch products
const fetchProducts = async (page, isNewFilter = false) => {
  if (loading.value) return;

  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: page,
      per_page: 20
    });

    if (filters.minPrice) params.append('min_price', filters.minPrice);
    if (filters.maxPrice) params.append('max_price', filters.maxPrice);
    if (filters.sizes.length > 0) params.append('sizes', filters.sizes.join(','));
    if (filters.colors.length > 0) params.append('colors', filters.colors.join(','));
    
    const response = await fetch(`/api/products?${params.toString()}`);
    const data = await response.json();

    if (isNewFilter) {
      products.value = data.data;
    } else {
      products.value = [...products.value, ...data.data];
    }

    hasMore.value = data.current_page < data.last_page;
    currentPage.value = data.current_page;

    // Extract unique colors
    const colors = new Set();
    data.data.forEach(product => {
      product.colors?.forEach(color => {
        colors.add(JSON.stringify({ name: color.color_name, code: color.color_code }));
      });
    });
    availableColors.value = Array.from(colors).map(c => JSON.parse(c));

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

  // Kiểm tra minPrice không âm
  if (filters.minPrice && min < 0) {
    alert('Giá tối thiểu không được âm.');
    return false;
  }

  // Kiểm tra minPrice <= maxPrice nếu cả 2 có giá trị
  if (filters.minPrice && filters.maxPrice && min > max) {
    alert('Khoảng giá không hợp lệ: giá từ phải nhỏ hơn hoặc bằng giá đến.');
    return false;
  }

  return true;
};


// Apply filters
const applyFilters = () => {
    if (!isPriceValid()) {
        alert('Khoảng giá không hợp lệ: giá từ phải nhỏ hơn hoặc bằng giá đến.');
        return;
    }
    products.value = [];
    currentPage.value = 1;
    fetchProducts(1, true);
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

// Setup intersection observer
const setupObserver = () => {
  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && hasMore.value && !loading.value) {
        fetchProducts(currentPage.value + 1);
      }
    },
    { threshold: 0.1 }
  );

  if (observerTarget.value) {
    observer.observe(observerTarget.value);
  }
};

onMounted(() => {
  fetchProducts(1, true);
  setupObserver();
});

onUnmounted(() => {
  if (observer && observerTarget.value) {
    observer.unobserve(observerTarget.value);
  }
});
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.products-page {
  min-height: 100vh;
  background-color: #ffffff;
  padding-top: 80px;
}

.container {
  max-width: 100%;
  margin: 0;
  padding: 0;
}

.layout {
  display: flex;
  gap: 0;
}

/* Sidebar */
.sidebar {
  width: 280px;
  background: #000000;
  transition: all 0.4s ease;
  min-height: calc(100vh - 80px);
  overflow: hidden;
  box-shadow: 2px 0 10px rgba(0,0,0,0.5);
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

/* Scrollbar đẹp */
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

/* Header */
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

/* Apply Button on top */
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
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.apply-btn:hover {
  background: #000000;
  color: #ffffff;
  border: 1px solid #ffffff;
  box-shadow: 0 2px 8px rgba(255,255,255,0.2);
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
  background: rgba(255,255,255,0.05);
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


/* Main Content */
.main-content {
  flex: 1;
  background: #ffffff;
  padding: 40px 60px;
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
}

.product-card:hover {
  transform: translateY(-2px);
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
  padding: 24px 20px;
}

.product-name {
  font-size: 20px;
  font-weight: 400;
  color: #000000;
  margin: 0 0 16px 0;
  height: 40px;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  letter-spacing: 0.5px;
  line-height: 1.5;
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
  color: #000000;
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
  color: #000000;
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
}

.detail-btn:hover {
  background: #ffffff;
  color: #000000;
  outline: 1px solid #000000;
}

/* Loading */
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
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading p {
  margin-top: 16px;
  color: #999999;
  font-size: 11px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

.observer-target {
  height: 16px;
}

.no-more {
  text-align: center;
  padding: 60px 0;
  color: #999999;
  font-size: 11px;
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

.no-products {
  text-align: center;
  padding: 100px 0;
}

.no-products p {
  font-size: 14px;
  color: #999999;
  margin: 0;
  letter-spacing: 2px;
  text-transform: uppercase;
}

/* Responsive */
@media (max-width: 1400px) {
  .products-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 992px) {
  .toggle-filter-btn {
    display: flex;
  }
  
  .products-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .main-content {
    padding: 30px 40px;
  }
}

@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    left: 0;
    top: 0;
    height: 100vh;
    z-index: 1000;
    padding-top: 80px;
  }
  
  .sidebar.hidden {
    transform: translateX(-100%);
  }
  
  .main-content {
    padding: 20px 30px;
  }
  
  .header h1 {
    font-size: 24px;
  }
}

@media (max-width: 576px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .main-content {
    padding: 20px;
  }
  
  .header h1 {
    font-size: 20px;
    letter-spacing: 2px;
  }
}
</style>