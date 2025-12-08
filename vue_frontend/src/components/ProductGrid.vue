<template>
  <section class="product-grid-section">
    <div class="section-header">
      <h2 class="section-title">{{ title }}</h2>
      <a href="#" class="view-all">Xem tất cả &rarr;</a>
    </div>
    
    <div class="grid-container" v-if="products.length > 0">
      <ProductCard 
        v-for="product in products" 
        :key="product.id" 
        :product="product" 
      />
    </div>
    <div v-else class="loading-state">
      Đang tải sản phẩm...
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import ProductCard from './ProductCard.vue';

const props = defineProps({
  title: {
    type: String,
    default: "Sản Phẩm Mới"
  }
});

const products = ref([]);

// Mock Data for now - waiting for API hookup
const fetchProducts = async () => {
    // Simulate API call
    setTimeout(() => {
        products.value = [
            { id: 1, name: "Áo Polo Classic Fit", price: 450000, oldPrice: 600000, discount: 25, image: "https://images.unsplash.com/photo-1586363104862-3a5e2ab60d99?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" },
            { id: 2, name: "Áo Thun Premium Cotton", price: 320000, image: "https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=880&q=80" },
            { id: 3, name: "Sơ Mi Oxford Trắng", price: 550000, image: "https://images.unsplash.com/photo-1596755094514-f87e34085b2c?ixlib=rb-4.0.3&auto=format&fit=crop&w=688&q=80" },
            { id: 4, name: "Quần Jeans Slim Fit", price: 790000, image: "https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" },
            { id: 5, name: "Áo Hoodie Đường Phố", price: 650000, oldPrice: 800000, discount: 15, image: "https://images.unsplash.com/photo-1556905055-8f358a7a47b2?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" },
            { id: 6, name: "Áo Khoác Bomber", price: 1200000, image: "https://images.unsplash.com/photo-1591047139829-d91aecb6caea?ixlib=rb-4.0.3&auto=format&fit=crop&w=736&q=80" },
            { id: 7, name: "Giày Sneaker Sport", price: 1500000, image: "https://images.unsplash.com/photo-1542291026-7eec264c27ff?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" },
             { id: 8, name: "Đồng Hồ Cổ Điển", price: 2500000, image: "https://images.unsplash.com/photo-1524592094714-0f0654e20314?ixlib=rb-4.0.3&auto=format&fit=crop&w=689&q=80" }
        ];
    }, 500);
};

onMounted(() => {
    fetchProducts();
});
</script>

<style scoped>
.product-grid-section {
  max-width: 1200px;
  margin: 0 auto 60px;
  padding: 0 20px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 30px;
  border-bottom: 2px solid #f5f5f5;
  padding-bottom: 10px;
}

.section-title {
  font-size: 2rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
  position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 0;
    width: 60px;
    height: 3px;
    background-color: #2c3e50;
}

.view-all {
  color: #666;
  text-decoration: none;
  font-size: 0.95rem;
  transition: color 0.2s;
}

.view-all:hover {
  color: #2c3e50;
}

.grid-container {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 30px;
}

.loading-state {
    text-align: center;
    padding: 50px;
    color: #999;
    font-style: italic;
}

@media (max-width: 1024px) {
  .grid-container {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .grid-container {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  .section-title { font-size: 1.5rem; }
}
</style>
