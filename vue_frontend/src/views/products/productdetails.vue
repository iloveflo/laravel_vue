<template>
  <div class="product-details-page" v-if="product">
    <!-- Top Section -->
    <div class="top-section">
      <!-- Images -->
      <div class="image-section">
        <img
          :src="mainImage"
          class="main-image"
          @error="replaceImage"
        />

        <div class="thumb-list">
          <img
            v-for="img in product.images"
            :key="img.id"
            :src="img.image_path"
            class="thumb"
            :class="{ active: img.image_path === mainImage }"
            @click="mainImage = img.image_path"
            @error="replaceImage"
          />
        </div>
      </div>

      <!-- Product Info -->
      <div class="info-section">
        <h2 class="title">{{ product.name }}</h2>

        <div class="price-wrapper">
          <span v-if="isSale" class="sale-price">
            {{ formatPrice(product.sale_price) }}
          </span>
          <span :class="{ 'original-price': isSale }">
            {{ formatPrice(product.price) }}
          </span>
        </div>

        <p class="description">{{ product.description }}</p>

        <!-- Sizes -->
        <div class="section-block">
          <h4>Kích thước</h4>
          <div class="size-list">
            <button
              v-for="s in product.sizes"
              :key="s.id"
              class="size-btn"
              :class="{ active: selectedSize === s.size }"
              @click="selectedSize = s.size"
            >
              {{ s.size }}
            </button>
          </div>
        </div>

        <!-- Colors -->
        <div class="section-block">
          <h4>Màu sắc</h4>
          <div class="color-list">
            <div
              v-for="c in product.colors"
              :key="c.id"
              class="color-circle"
              :style="{ backgroundColor: c.color_code }"
              :class="{ active: selectedColor === c.color_name }"
              @click="selectedColor = c.color_name"
            ></div>
          </div>
        </div>

        <!-- Quantity -->
        <div class="section-block">
          <h4>Số lượng</h4>
          <div class="qty-box">
            <button @click="decreaseQty">-</button>
            <input type="number" v-model="quantity" />
            <button @click="increaseQty">+</button>
          </div>
        </div>

        <!-- Add to Cart -->
        <button class="add-cart-btn" @click="addToCart">
          Thêm vào giỏ hàng
        </button>
      </div>
    </div>

    <!-- Reviews -->
    <div class="reviews-section">
      <h3>Đánh giá sản phẩm</h3>

      <div v-if="product.reviews?.length > 0">
        <div
          v-for="rv in product.reviews"
          :key="rv.id"
          class="review-card"
        >
          <strong>{{ rv.user_name || 'Người dùng' }}</strong>
          <p>{{ rv.comment }}</p>
          <span class="rating">⭐ {{ rv.rating }}/5</span>
        </div>
      </div>

      <div v-else>
        <p>Chưa có đánh giá nào.</p>
      </div>
    </div>
  </div>

  <!-- Loading -->
  <div v-else class="loading">Đang tải sản phẩm...</div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import axios from "axios";

const route = useRoute();
const slug = route.params.slug;

const product = ref(null);
const mainImage = ref("");

const selectedSize = ref("");
const selectedColor = ref("");
const quantity = ref(1);

// Fetch product details
onMounted(async () => {
  try {
    const res = await axios.get(`/products/${slug}`);
    product.value = res.data.product;

    // Set main image
    mainImage.value =
      product.value.images?.find((img) => img.is_primary)?.image_path ||
      product.value.images?.[0]?.image_path ||
      "/placeholder.jpg";
  } catch (err) {
    console.error("Lỗi:", err);
  }
});

const replaceImage = (e) => {
  e.target.src = "https://via.placeholder.com/300x300?text=No+Image";
};

const formatPrice = (price) =>
  Number(price).toLocaleString("vi-VN") + "₫";

const isSale = (product) =>
  product?.sale_price &&
  parseFloat(product.sale_price) < parseFloat(product.price);

const increaseQty = () => {
  quantity.value++;
};

const decreaseQty = () => {
  if (quantity.value > 1) quantity.value--;
};

const addToCart = () => {
  if (!selectedSize.value) {
    alert("Vui lòng chọn kích thước!");
    return;
  }
  if (!selectedColor.value) {
    alert("Vui lòng chọn màu sắc!");
    return;
  }

  alert(
    `🛒 Đã thêm vào giỏ hàng:
Sản phẩm: ${product.value.name}
Size: ${selectedSize.value}
Màu: ${selectedColor.value}
Số lượng: ${quantity.value}`
  );
};
</script>

<style scoped>
/* ================================
   MINIMALIST BLACK & WHITE THEME
   Sharp Lines, Geometric Beauty
   ================================ */

.product-details-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 150px 40px 60px; /* Tăng padding-top từ 60px lên 150px */
  background: #ffffff;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  color: #000000;
}

/* ================================
   TOP SECTION - GRID LAYOUT
   ================================ */

.top-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 80px;
  margin-bottom: 100px;
  border-bottom: 1px solid #000000;
  padding-bottom: 80px;
}

/* ================================
   IMAGE SECTION
   ================================ */

.image-section {
  position: relative;
  max-width: 550px; /* Giới hạn chiều rộng */
}

.main-image {
  width: 100%;
  aspect-ratio: 3/4;
  object-fit: cover;
  border: 2px solid #000000;
  display: block;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.main-image:hover {
  transform: translateY(-4px);
  box-shadow: 8px 8px 0 #000000;
}

.thumb-list {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-top: 20px;
}

.thumb {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  border: 1px solid #000000;
  cursor: pointer;
  transition: all 0.2s ease;
  opacity: 0.5;
}

.thumb:hover {
  opacity: 0.8;
}

.thumb.active {
  opacity: 1;
  border-width: 2px;
  box-shadow: 4px 4px 0 #000000;
}

/* ================================
   INFO SECTION
   ================================ */

.info-section {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.title {
  font-size: 42px;
  font-weight: 700;
  letter-spacing: -1px;
  line-height: 1.1;
  margin: 0;
  text-transform: uppercase;
  border-left: 6px solid #000000;
  padding-left: 20px;
}

/* ================================
   PRICE
   ================================ */

.price-wrapper {
  display: flex;
  align-items: center;
  gap: 16px;
  font-size: 32px;
  font-weight: 700;
  padding: 20px 0;
  border-top: 1px solid #000000;
  border-bottom: 1px solid #000000;
}

.sale-price {
  color: #000000;
}

.original-price {
  font-size: 24px;
  text-decoration: line-through;
  opacity: 0.4;
  font-weight: 400;
}

/* ================================
   DESCRIPTION
   ================================ */

.description {
  font-size: 16px;
  line-height: 1.8;
  margin: 0;
  color: #333333;
}

/* ================================
   SECTION BLOCKS
   ================================ */

.section-block {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.section-block h4 {
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  margin: 0;
}

/* ================================
   SIZE SELECTOR
   ================================ */

.size-list {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.size-btn {
  min-width: 60px;
  height: 60px;
  border: 2px solid #000000;
  background: #ffffff;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-transform: uppercase;
}

.size-btn:hover {
  background: #000000;
  color: #ffffff;
  transform: translateY(-2px);
}

.size-btn.active {
  background: #000000;
  color: #ffffff;
  box-shadow: 4px 4px 0 rgba(0, 0, 0, 0.2);
}

/* ================================
   COLOR SELECTOR
   ================================ */

.color-list {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.color-circle {
  width: 50px;
  height: 50px;
  border: 2px solid #000000;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.color-circle:hover {
  transform: scale(1.1);
}

.color-circle.active {
  box-shadow: 0 0 0 4px #ffffff, 0 0 0 6px #000000;
  transform: scale(1.05);
}

/* ================================
   QUANTITY BOX
   ================================ */

.qty-box {
  display: inline-flex;
  border: 2px solid #000000;
  width: fit-content;
}

.qty-box button {
  width: 40px;
  height: 40px;
  border: none;
  background: #ffffff;
  font-size: 20px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
  flex-shrink: 0;
}

.qty-box button:first-child {
  border-right: 1px solid #000000;
}

.qty-box button:last-child {
  border-left: 1px solid #000000;
}

.qty-box button:hover {
  background: #000000;
  color: #ffffff;
}

.qty-box input {
  width: 60px;
  height: 40px;
  border: none;
  text-align: center;
  font-size: 16px;
  font-weight: 600;
  outline: none;
  padding: 0;
  margin: 0;
  box-sizing: border-box;
  -moz-appearance: textfield;
}

.qty-box input::-webkit-outer-spin-button,
.qty-box input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.qty-box input:focus {
  background: #f5f5f5;
}
/* ================================
   ADD TO CART BUTTON
   ================================ */

.add-cart-btn {
  width: 100%;
  height: 70px;
  background: #000000;
  color: #ffffff;
  border: none;
  font-size: 18px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin-top: 20px;
}

.add-cart-btn:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 0 rgba(0, 0, 0, 0.3);
}

.add-cart-btn:active {
  transform: translateY(-2px);
  box-shadow: 0 4px 0 rgba(0, 0, 0, 0.3);
}

/* ================================
   REVIEWS SECTION
   ================================ */

.reviews-section {
  padding: 60px 0;
}

.reviews-section h3 {
  font-size: 32px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: -1px;
  margin: 0 0 40px 0;
  border-left: 6px solid #000000;
  padding-left: 20px;
}

.review-card {
  border: 1px solid #000000;
  padding: 24px;
  margin-bottom: 16px;
  transition: all 0.2s ease;
  background: #ffffff;
}

.review-card:hover {
  transform: translateX(8px);
  box-shadow: -8px 8px 0 rgba(0, 0, 0, 0.1);
}

.review-card strong {
  display: block;
  font-size: 16px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 12px;
}

.review-card p {
  margin: 0 0 12px 0;
  line-height: 1.6;
  color: #333333;
}

.rating {
  font-size: 14px;
  font-weight: 600;
  display: inline-block;
  padding: 4px 12px;
  background: #000000;
  color: #ffffff;
}

/* ================================
   LOADING STATE
   ================================ */

.loading {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 60vh;
  font-size: 24px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
}

/* ================================
   RESPONSIVE
   ================================ */

@media (max-width: 1024px) {
  .top-section {
    grid-template-columns: 1fr;
    gap: 60px;
  }

  .title {
    font-size: 36px;
  }
}

@media (max-width: 768px) {
  .product-details-page {
    padding: 40px 20px;
  }

  .title {
    font-size: 28px;
  }

  .price-wrapper {
    font-size: 24px;
  }

  .thumb-list {
    grid-template-columns: repeat(3, 1fr);
  }

  .add-cart-btn {
    height: 60px;
    font-size: 16px;
  }
}
</style>
