<template>
  <div class="products-page">
    <div class="container">
      <div class="layout">
        
        <div 
          class="sidebar-overlay" 
          :class="{ active: showFilters }"
          @click="showFilters = false"
        ></div>

        <aside class="sidebar" :class="{ open: showFilters }">
          <div class="filter-panel">
            <div class="filter-header">
              <h2>
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Bộ lọc
              </h2>
              <button class="close-filter-btn" @click="showFilters = false">
                ✕
              </button>
            </div>

            <button @click="applyFilters" class="apply-btn">
              Áp dụng
            </button>

            <div class="filter-section">
              <h3>Khoảng giá - VND</h3>
              <div class="price-inputs">
                <input v-model="filters.minPrice" type="number" placeholder="Từ" class="input"/>
                <input v-model="filters.maxPrice" type="number" placeholder="Đến" class="input"/>
              </div>
            </div>

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
                  ></div>
                  <span>{{ color.name }}</span>
                </label>
              </div>
            </div>
          </div>
        </aside>


        <main class="main-content">
          <div class="header">
            <h1>Sản phẩm <span class="count">({{ products.length }})</span></h1>
            
            <button @click="showFilters = !showFilters" class="toggle-filter-btn">
              <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              {{ showFilters ? 'Ẩn bộ lọc' : 'Bộ lọc' }}
            </button>
          </div>

          <div class="products-grid">
            <div
              v-for="product in products"
              :key="product.id"
              class="product-card"
            >
              <div class="product-image">
                <img
                  :src="product.images?.find(img => img.is_primary)?.image_path || '/placeholder.jpg'"
                  :alt="product.name"
                  @error="(e) => e.target.src = 'https://via.placeholder.com/300x300?text=No+Image'"
                />
                <span v-if="product.featured" class="badge">Nổi bật</span>
              </div>

              <div class="product-info">
                <h3 class="product-name">{{ product.name }}</h3>
                <div class="product-price">
                   <div v-if="product.sale_price && parseFloat(product.sale_price) < parseFloat(product.price)" class="price-sale">
                    <span class="sale-price">{{ formatPrice(product.sale_price) }}</span>
                    <span class="original-price">{{ formatPrice(product.price) }}</span>
                  </div>
                  <span v-else class="price">{{ formatPrice(product.price) }}</span>
                </div>
                
                <div class="product-sizes">
                  <span v-for="size in product.sizes?.slice(0, 5)" :key="size.id" class="size-tag">{{ size.size }}</span>
                </div>
                
                <button class="detail-btn" @click="gotoDetail(product)">Xem chi tiết</button>
              </div>
            </div>
          </div>
          
          <div v-if="loading" class="loading"><div class="spinner"></div></div>
          <div ref="observerTarget" class="observer-target"></div>
          <div v-if="!loading && products.length === 0" class="no-products"><p>Không tìm thấy sản phẩm</p></div>

        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
const isMobile = window.innerWidth < 992;
const showFilters = ref(!isMobile);

const router = useRouter();
const products = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const hasMore = ref(true);
// const showFilters = ref(true);

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
/* --- BASE STYLES (Giữ nguyên các style cơ bản) --- */
* { box-sizing: border-box; }
.products-page { min-height: 100vh; background-color: #ffffff; padding-top: 80px; }
.container { max-width: 100%; margin: 0; padding: 0; }
.layout { display: flex; position: relative; } /* Thêm position relative */

/* --- SIDEBAR DESKTOP --- */
.sidebar {
  width: 280px;
  background: #000;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  height: calc(100vh - 80px); /* Sửa lại height cố định để sticky hoạt động tốt */
  position: sticky;
  top: 80px;
  overflow: hidden;
  z-index: 90;
  flex-shrink: 0; /* Không cho sidebar bị co lại */
}

/* Ẩn sidebar trên desktop = width 0 */
.sidebar:not(.open) {
  width: 0;
  opacity: 0;
}

.filter-panel {
  width: 280px; /* Cố định width nội dung để không bị méo khi slide */
  padding: 40px 30px;
  height: 100%;
  overflow-y: auto;
  color: #fff;
}

/* Nút đóng chỉ hiện trên mobile */
.close-filter-btn { display: none; }
.sidebar-overlay { display: none; }

/* --- MAIN CONTENT --- */
.main-content {
  flex: 1;
  background: #ffffff;
  padding: 40px 60px;
  width: 100%; /* Đảm bảo content chiếm hết khi sidebar ẩn */
  transition: padding 0.3s;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e0e0e0;
  flex-wrap: wrap;
  gap: 15px;
}

.header h1 {
  font-size: 24px;
  font-weight: 300;
  text-transform: uppercase;
  letter-spacing: 2px;
  margin: 0;
}
.header .count { font-size: 0.8em; color: #777; }

.toggle-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #000;
  color: #fff;
  border: none;
  cursor: pointer;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 1px;
}

/* --- GRID SẢN PHẨM --- */
.products-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1px;
  background: #e0e0e0;
  border: 1px solid #e0e0e0;
}

.product-card { background: #fff; position: relative; }
.product-image { aspect-ratio: 3/4; overflow: hidden; position: relative; } /* Tỉ lệ ảnh chuẩn thời trang */
.product-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.product-card:hover .product-image img { transform: scale(1.05); }

/* Các style text, button trong card giữ nguyên... */
.product-info { padding: 16px; }
.product-name { font-size: 14px; margin-bottom: 8px; height: 40px; }
.price { font-size: 16px; font-weight: 600; }
.detail-btn { 
  margin-top: 10px; 
  width: 100%; padding: 10px; 
  background: #000; color: #fff; 
  border: none; text-transform: uppercase; 
  font-size: 10px; letter-spacing: 1px; cursor: pointer;
}

/* =========================================
   RESPONSIVE BREAKPOINTS
   ========================================= */

/* --- TABLET & SMALL LAPTOP (Max 1200px) --- */
@media (max-width: 1200px) {
  /* body{
    margin-top: 200vh;
  } */
  .products-grid {
    grid-template-columns: repeat(3, 1fr); /* Giảm xuống 3 cột */
  }
  .main-content { padding: 30px; }
}


/* --- MOBILE & TABLET PORTRAIT (Max 991px) --- */
@media (max-width: 900px) {
  
  /* 1. Layout thay đổi */
  .products-page { padding-top: 60px; /* Header mobile thường nhỏ hơn */ }
  .sidebar {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    height: 100vh;
    z-index: 1000;
    width: 300px; /* Độ rộng drawer */
    max-width: 85%;
    transform: translateX(-100%); /* Ẩn sang trái */
    transition: transform 0.3s ease-out;
    box-shadow: 5px 0 15px rgba(0,0,0,0.3);
    opacity: 1; /* Reset opacity desktop */
  }
  
.main-content { padding: 50px; }
  /* Khi mở trên mobile: trượt vào */
  .sidebar.open {
    transform: translateX(0);
    width: 300px;
  }
  
  /* Reset logic ẩn width của desktop */
  .sidebar:not(.open) { width: 300px; }

  /* 2. Overlay nền tối */
  .sidebar-overlay {
    display: block;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.6);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
    backdrop-filter: blur(2px);
  }
  .sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
  }

  /* 3. Nút đóng filter */
  .close-filter-btn {
    display: block;
    background: transparent;
    border: none;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
    margin-left: auto;
  }
  
  .filter-header {
    justify-content: space-between;
    margin-bottom: 30px;
  }

  /* 4. Grid sản phẩm tablet */
  .products-grid {
    grid-template-columns: repeat(2, 1fr); /* 2 cột là chuẩn nhất */
  }
  
  /* .main-content { padding: 20px 15px; } */
  .filter-panel { padding: 30px 20px; }
}

/* --- SMALL MOBILE (Max 480px) --- */
@media (max-width: 480px) {
  .header {
    flex-direction: row; /* Vẫn giữ ngang */
    align-items: center;
  }
  .main-content { padding: 80px; }
  .header h1 { font-size: 18px; }
  
  .toggle-filter-btn {
    padding: 8px 12px;
    font-size: 10px;
  }
  
  .products-grid {
    /* Tùy chọn: 1 cột nếu muốn ảnh to, 2 cột nếu muốn xem nhiều */
    grid-template-columns: repeat(2, 1fr); 
    gap: 1px;
  }
  
  .product-info { padding: 10px; }
  .product-name { font-size: 13px; margin-bottom: 4px; }
  .price { font-size: 14px; }
  
  /* Ẩn bớt chi tiết thừa trên mobile nhỏ */
  .product-sizes, .product-colors { display: none; }
}
</style>