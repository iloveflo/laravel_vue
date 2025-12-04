<template>
  <div class="cart-container">
    <h2>Giỏ hàng của bạn</h2>

    <div v-if="loading" class="loading">Đang tải giỏ hàng...</div>

    <div v-else-if="cartItems.length === 0" class="empty-cart">
      <p>Giỏ hàng đang trống.</p>
      <router-link to="/products" class="continue-shopping">Tiếp tục mua sắm</router-link>
    </div>

    <div v-else>
      <table class="cart-table">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>Đơn giá</th>
            <th>Số lượng</th>
            <th>Thành tiền</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in cartItems" :key="item.id">
            <td class="product-col">
              <div class="product-info">
                <img 
                  :src="item.image ? item.image : '/placeholder.jpg'" 
                  alt="Product Image" 
                  class="product-img"
                />
                <div class="product-details">
                  <span class="product-name">{{ item.name }}</span>
                  <div class="attributes">
                    <span v-if="item.size">Size: {{ item.size }}</span>
                    <span v-if="item.color"> | Màu: {{ item.color }}</span>
                  </div>
                </div>
              </div>
            </td>

            <td>
              <span class="price">{{ formatCurrency(item.unit_price) }}</span>
              <br>
              <small v-if="item.original_price > item.unit_price" class="old-price">
                {{ formatCurrency(item.original_price) }}
              </small>
            </td>

            <td>
              <span class="qty-badge">{{ item.quantity }}</span>
            </td>

            <td>
              <strong class="line-total">{{ formatCurrency(item.line_total) }}</strong>
            </td>

            <td>
              <button @click="removeItem(item.id)" class="btn-remove" :disabled="isRemoving === item.id">
                {{ isRemoving === item.id ? '...' : 'Xóa' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="cart-summary">
        <div class="summary-row">
          <span>Tổng số lượng:</span>
          <span>{{ summary.total_items }} sản phẩm</span>
        </div>
        <div class="summary-row total-row">
          <span>Tổng thanh toán:</span>
          <span class="total-price">{{ formatCurrency(summary.total_price) }}</span>
        </div>
        <div class="actions">
          <button class="btn-checkout">Đặt hàng</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// --- State ---
const cartItems = ref([]);
const summary = ref({ total_items: 0, total_price: 0 });
const loading = ref(true);
const isRemoving = ref(null); // Để tracking item nào đang bị xóa để hiện loading


// --- Helper Functions ---

// 1. Hàm format tiền tệ (VND)
const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// 2. Hàm lấy hoặc tạo Session ID cho khách vãng lai
const getSessionId = () => {
  let sessionId = localStorage.getItem('cart_session_id');
  if (!sessionId) {
    // Nếu chưa có, tạo chuỗi ngẫu nhiên đơn giản (hoặc dùng thư viện uuid)
    sessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + Date.now();
    localStorage.setItem('cart_session_id', sessionId);
  }
  return sessionId;
};

// 3. Hàm lấy Token (nếu user đã login)
const getAuthToken = () => {
  return localStorage.getItem('token'); // Giả sử bạn lưu token ở đây khi login
};

// --- API Calls ---

// 1. Lấy danh sách giỏ hàng
const fetchCart = async () => {
  loading.value = true;
  try {
    const token = getAuthToken();
    const sessionId = getSessionId();

    const config = {
      params: { session_id: sessionId }, // Luôn gửi session_id để backend xử lý fallback
      headers: {}
    };

    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }

    const response = await axios.get(`/cart`, config);

    if (response.data.status === 'success') {
      cartItems.value = response.data.data;
      summary.value = response.data.summary;
    }
  } catch (error) {
    console.error("Lỗi tải giỏ hàng:", error);
  } finally {
    loading.value = false;
  }
};

// 2. Xóa sản phẩm
const removeItem = async (cartId) => {
  if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;

  isRemoving.value = cartId; // Bật loading cho nút xóa
  try {
    const token = getAuthToken();
    const sessionId = getSessionId();

    // Đối với method DELETE, axios nhận params qua option 'params' hoặc 'data'
    // Nhưng query params là an toàn nhất cho API chúng ta vừa viết
    const config = {
      params: { session_id: sessionId },
      headers: {}
    };

    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }

    await axios.delete(`/cart/remove/${cartId}`, config);

    // Xóa thành công thì tải lại giỏ hàng để cập nhật tổng tiền
    await fetchCart();
    // Phát tín hiệu để Header biết mà cập nhật
    window.dispatchEvent(new Event('cart-updated')); 
  } catch (error) {
    console.error("Lỗi xóa sản phẩm:", error);
    alert('Không thể xóa sản phẩm. Vui lòng thử lại.');
  } finally {
    isRemoving.value = null;
  }
};

// --- Lifecycle ---
onMounted(() => {
  fetchCart();
});
</script>

<style scoped>
/* --- CẤU TRÚC CHUNG & FONT --- */
.cart-container {
  max-width: 1200px;
  margin: 0 auto;
  /* Giữ khoảng cách lớn phía trên để tránh Header */
  padding-top: 120px; 
  padding-bottom: 80px;
  padding-left: 20px;
  padding-right: 20px;
  /* Phông chữ hệ thống hiện đại, dễ đọc nhất */
  font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  color: #333; /* Màu chữ xám đậm dễ chịu hơn đen tuyền */
  background-color: #fff;
}

h2 {
  font-size: 1.8rem;
  font-weight: 700;
  color: #000;
  margin-bottom: 30px;
  padding-bottom: 15px;
  border-bottom: 1px solid #e5e5e5; /* Đường kẻ mỏng tinh tế */
}

/* --- TRẠNG THÁI LOADING / EMPTY --- */
.loading, .empty-cart {
  text-align: center;
  padding: 80px 0;
  font-size: 1.1rem;
  color: #666;
}

.continue-shopping {
  display: inline-block;
  margin-top: 15px;
  color: #000;
  text-decoration: underline;
  font-weight: 600;
}

/* --- BẢNG GIỎ HÀNG --- */
.cart-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 40px;
}

.cart-table th {
  text-align: left;
  padding: 15px;
  font-size: 0.9rem;
  font-weight: 600;
  color: #555;
  background-color: #f9f9f9; /* Nền header bảng xám nhẹ */
  border-bottom: 2px solid #eee;
}

.cart-table td {
  padding: 20px 15px;
  border-bottom: 1px solid #eee;
  vertical-align: middle; /* Căn giữa theo chiều dọc */
}

/* --- CỘT SẢN PHẨM --- */
.product-info {
  display: flex;
  align-items: center; /* Căn giữa ảnh và chữ */
  gap: 20px;
}

.product-img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 4px; /* Bo góc nhẹ cho mềm mại */
  border: 1px solid #eee;
}

.product-details {
  display: flex;
  flex-direction: column;
}

.product-name {
  font-size: 1rem;
  font-weight: 600;
  color: #000;
  margin-bottom: 5px;
  text-decoration: none;
}

.attributes {
  font-size: 0.85rem;
  color: #777;
}

.attributes span {
  margin-right: 10px;
}

/* --- GIÁ & SỐ LƯỢNG --- */
.price {
  font-weight: 600;
  color: #333;
}

.old-price {
  font-size: 0.85rem;
  text-decoration: line-through;
  color: #999;
  margin-left: 8px;
}

.qty-badge {
  display: inline-block;
  padding: 5px 12px;
  background: #f5f5f5;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.9rem;
  color: #333;
}

.line-total {
  font-size: 1.1rem;
  font-weight: 700;
  color: #000;
}

/* --- NÚT XÓA --- */
.btn-remove {
  background: transparent;
  border: none;
  color: #080808;
  cursor: pointer;
  font-size: 0.9rem;
  transition: color 0.2s;
}

.btn-remove:hover {
  color: #ee2629; /* Hover vào hiện màu đỏ cảnh báo */
  text-decoration: underline;
}

.btn-remove:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* --- TỔNG KẾT GIỎ HÀNG --- */
.cart-summary {
  margin-left: auto; /* Đẩy sang phải */
  width: 100%;
  max-width: 350px;
  background: #fcfcfc;
  padding: 25px;
  border-radius: 8px;
  border: 1px solid #eee;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 0.95rem;
  color: #555;
}

.total-row {
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid #eee;
  font-weight: 700;
  font-size: 1.2rem;
  color: #000;
  align-items: center;
}

.total-price {
  color: #d32f2f; /* Màu đỏ nổi bật cho tổng tiền */
}

/* --- NÚT CHECKOUT --- */
.actions {
  margin-top: 25px;
}

.btn-checkout {
  width: 100%;
  background-color: #000; /* Nút đen */
  color: #fff;           /* Chữ trắng */
  border: none;
  padding: 16px;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 6px; /* Bo góc hiện đại */
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-checkout:hover {
  background-color: #333; /* Hover sáng hơn 1 chút */
}

/* --- RESPONSIVE MOBILE --- */
@media (max-width: 768px) {
  .cart-container {
    padding-top: 100px;
  }
  
  .cart-table thead {
    display: none; /* Ẩn header bảng trên mobile */
  }

  .cart-table tr {
    display: block;
    border-bottom: 1px solid #eee;
    padding-bottom: 20px;
    margin-bottom: 20px;
  }

  .cart-table td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border: none;
  }

  /* Riêng cột sản phẩm thì căn trái */
  .cart-table td.product-col {
    justify-content: flex-start;
  }
  
  /* Thêm label giả để người dùng hiểu trên mobile */
  .cart-table td::before {
    content: attr(data-label); /* Cần thêm data-label vào HTML nếu muốn xịn hơn */
    font-weight: 600;
    color: #999;
    font-size: 0.85rem;
  }
  
  .cart-summary {
    max-width: 100%;
    border: none;
    background: transparent;
    padding: 0;
    margin-top: 20px;
  }
}
</style>