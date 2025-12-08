<template>
  <div class="product-card">
    <div class="image-wrapper">
      <img :src="product.image || 'https://via.placeholder.com/300x400?text=No+Image'" :alt="product.name" />
      <div class="overlay">
        <button class="add-to-cart-btn" @click.stop="addToCart">
          <span class="icon">+</span> Thêm vào giỏ
        </button>
      </div>
      <span v-if="product.discount" class="badge discount">-{{ product.discount }}%</span>
    </div>
    <div class="product-info">
      <div class="rating">
        <span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span><span class="star">★</span>
      </div>
      <h3 class="product-name">{{ product.name }}</h3>
      <div class="price-box">
        <p class="price">{{ formatCurrency(product.price) }}</p>
        <p v-if="product.oldPrice" class="old-price">{{ formatCurrency(product.oldPrice) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  product: {
    type: Object,
    required: true
  }
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const addToCart = () => {
  alert(`Đã thêm ${props.product.name} vào giỏ hàng!`);
  // Emit event or use store here
};
</script>

<style scoped>
.product-card {
  background: white;
  transition: transform 0.3s ease;
  cursor: pointer;
  position: relative;
}

.product-card:hover {
  transform: translateY(-5px);
}

.image-wrapper {
  position: relative;
  overflow: hidden;
  padding-top: 133%; /* 3:4 Aspect Ratio */
  background-color: #f4f4f4;
}

.image-wrapper img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.product-card:hover .image-wrapper img {
  transform: scale(1.05);
}

.overlay {
  position: absolute;
  bottom: 0px;
  left: 0;
  width: 100%;
  padding: 15px;
  background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);
  display: flex;
  justify-content: center;
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.3s ease;
}

.product-card:hover .overlay {
  opacity: 1;
  transform: translateY(0);
}

.add-to-cart-btn {
  background-color: white;
  color: #333;
  border: none;
  padding: 10px 20px;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.8rem;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  gap: 5px;
}

.add-to-cart-btn:hover {
  background-color: #2c3e50;
  color: white;
}

.badge {
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 4px 8px;
  font-size: 0.75rem;
  font-weight: bold;
  color: white;
  text-transform: uppercase;
}

.badge.discount {
  background-color: #e74c3c;
}

.product-info {
  padding: 15px 0;
  text-align: left;
}

.rating {
  color: #f1c40f;
  font-size: 0.8rem;
  margin-bottom: 5px;
}

.product-name {
  font-size: 1rem;
  font-weight: 500;
  color: #333;
  margin: 0 0 5px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.price-box {
  display: flex;
  gap: 10px;
  align-items: baseline;
}

.price {
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

.old-price {
  font-size: 0.9rem;
  text-decoration: line-through;
  color: #999;
  margin: 0;
}
</style>
