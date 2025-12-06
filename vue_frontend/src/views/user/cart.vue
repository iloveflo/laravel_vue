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
            <button class="btn-checkout" @click="handleCheckoutClick">Đặt hàng</button>
          </div>

          <div v-if="showCheckoutForm" class="checkout-modal">
            <div class="checkout-content">
              <h3>Thông tin giao hàng</h3>
              
              <form @submit.prevent="submitOrder">
                <div class="form-group">
                  <label>Họ tên:</label>
                  <input v-model="form.full_name" type="text" required placeholder="Nhập họ tên">
                </div>

                <div class="form-group">
                  <label>Email:</label>
                  <input v-model="form.email" type="email" required placeholder="Nhập email">
                </div>

                <div class="form-group">
                  <label>Số điện thoại:</label>
                  <input v-model="form.phone" type="text" required placeholder="Nhập số điện thoại">
                </div>

                <div class="form-group">
                  <label>Địa chỉ nhận hàng:</label>
                  <textarea v-model="form.address" required placeholder="Nhập địa chỉ chi tiết"></textarea>
                </div>

                <div class="form-group">
                  <label>Mã khuyến mại (nếu có):</label>
                  <input v-model="form.coupon_code" type="text" placeholder="Mã giảm giá">
                </div>

                <div class="form-group">
                  <label>Phương thức thanh toán:</label>
                  <select v-model="form.payment_method">
                    <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                    <option value="banking">Chuyển khoản ngân hàng</option>
                    <option value="vnpay">VNPAY</option>
                  </select>
                </div>

                <div class="form-actions">
                  <button type="button" @click="showCheckoutForm = false">Hủy</button>
                  <button type="submit" class="btn-confirm">Xác nhận thanh toán</button>
                </div>
              </form>
            </div>
          </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

// --- STATE: Giỏ hàng ---
const cartItems = ref([]);
const summary = ref({ total_items: 0, total_price: 0 });
const loading = ref(true);
const isRemoving = ref(null);

// --- STATE: Checkout (Khớp với HTML của bạn) ---
const showCheckoutForm = ref(false); // Biến bật/tắt modal
const isProcessing = ref(false); // Trạng thái loading khi submit
const form = ref({
  full_name: '',
  email: '',
  phone: '',
  address: '',
  coupon_code: '',
  payment_method: 'cod'
});

// --- HELPER FUNCTIONS ---

// 1. Format tiền tệ
const formatCurrency = (value) => {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// 2. Lấy Session ID (cho khách)
const getSessionId = () => {
  let sessionId = localStorage.getItem('cart_session_id');
  if (!sessionId) {
    sessionId = 'sess_' + Math.random().toString(36).substr(2, 9) + Date.now();
    localStorage.setItem('cart_session_id', sessionId);
  }
  return sessionId;
};

// 3. Lấy Token (nếu đã login)
const getAuthToken = () => {
  return localStorage.getItem('token'); 
};

// 4. Tạo Header Auth
const getHeaders = () => {
  const token = getAuthToken();
  return token ? { Authorization: `Bearer ${token}` } : {};
};

// --- API FUNCTIONS ---

// 1. Lấy danh sách giỏ hàng
const fetchCart = async () => {
  loading.value = true;
  try {
    const config = {
      params: { session_id: getSessionId() },
      headers: getHeaders()
    };
    
    // Gọi API lấy giỏ hàng
    const response = await axios.get('/cart', config);
    
    // Gán dữ liệu (tuỳ chỉnh theo response thực tế của bạn)
    if (response.data) {
        cartItems.value = response.data.data || []; 
        summary.value = response.data.summary || { total_items: 0, total_price: 0 };
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
  isRemoving.value = cartId;
  try {
    const config = {
      params: { session_id: getSessionId() },
      headers: getHeaders()
    };
    await axios.delete(`/cart/remove/${cartId}`, config);
    await fetchCart(); // Load lại giỏ
    window.dispatchEvent(new Event('cart-updated')); // Update header cart count
  } catch (error) {
    console.error("Lỗi xóa:", error);
  } finally {
    isRemoving.value = null;
  }
};

// --- LOGIC CHECKOUT (Khớp với HTML của bạn) ---

// 3. Click nút "Đặt hàng" -> Hiện form & Load info
const handleCheckoutClick = async () => {
  // Reset form về mặc định
  form.value = {
    full_name: '', email: '', phone: '', address: '', 
    coupon_code: '', payment_method: 'cod'
  };

  try {
    // Gọi API check info user
    const response = await axios.get('/checkout/info', { headers: getHeaders() });
    const data = response.data;

    // Nếu user đã login, fill data vào form
    if (data.is_logged_in && data.customer_info) {
      form.value.full_name = data.customer_info.full_name || '';
      form.value.email = data.customer_info.email || '';
      form.value.phone = data.customer_info.phone || '';
      form.value.address = data.customer_info.address || '';
    }

    // Hiện Modal
    showCheckoutForm.value = true;

  } catch (error) {
    console.error("Lỗi lấy thông tin user:", error);
    // Vẫn hiện form trắng nếu lỗi mạng
    showCheckoutForm.value = true;
  }
};

// 4. Submit form "Xác nhận thanh toán"
const submitOrder = async () => {
  if (cartItems.value.length === 0) {
    alert('Giỏ hàng trống!');
    return;
  }

  isProcessing.value = true;
  try {
    // Payload gửi đi (kèm session_id cho trường hợp guest)
    const payload = {
      ...form.value,
      session_id: getSessionId()
    };

    const response = await axios.post('/checkout/process', payload, { headers: getHeaders() });

    if (response.data.success) {
      alert('Đặt hàng thành công!');
      showCheckoutForm.value = false; // Đóng modal
      
      // Reset giỏ hàng client
      cartItems.value = [];
      summary.value = { total_items: 0, total_price: 0 };
      window.dispatchEvent(new Event('cart-updated'));
      
      // Có thể redirect: window.location.href = '/account/orders';
    }
  } catch (error) {
    console.error("Lỗi đặt hàng:", error);
    if (error.response && error.response.data.errors) {
       // Nếu Laravel trả về lỗi validate chi tiết
       alert('Vui lòng kiểm tra lại thông tin nhập vào.');
    } else {
       alert('Có lỗi xảy ra, vui lòng thử lại sau.');
    }
  } finally {
    isProcessing.value = false;
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

/* 1. Lớp phủ mờ (Overlay) */
.checkout-modal {
  position: fixed; /* Cố định vị trí so với cửa sổ trình duyệt */
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* Màu đen mờ 50% */
  display: flex;
  justify-content: center; /* Căn giữa ngang */
  align-items: center;     /* Căn giữa dọc */
  z-index: 9999;           /* Đảm bảo luôn nổi lên trên cùng (trên Header/Cart) */
  animation: fadeIn 0.3s ease-in-out; /* Hiệu ứng hiện dần */
}

/* 2. Hộp chứa nội dung form */
.checkout-content {
  background-color: #fff;
  width: 600px;
  max-width: 90%; /* Để không bị tràn màn hình trên mobile */
  max-height: 90vh; /* Chiều cao tối đa 90% màn hình */
  overflow-y: auto; /* Nếu form dài quá thì hiện thanh cuộn */
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); /* Đổ bóng tạo chiều sâu */
  position: relative;
}

/* Tiêu đề form */
.checkout-content h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #000;
  margin-bottom: 25px;
  text-align: center;
  border-bottom: 1px solid #eee;
  padding-bottom: 15px;
}

/* 3. Các trường nhập liệu */
.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  font-size: 0.95rem;
  color: #333;
}

/* Input, Textarea, Select */
.form-group input,
.form-group textarea,
.form-group select {
  width: 100%;
  padding: 12px;
  font-size: 0.95rem;
  font-family: inherit; /* Kế thừa font hệ thống */
  border: 1px solid #e5e5e5;
  border-radius: 6px;
  background-color: #f9f9f9;
  transition: border-color 0.2s, background-color 0.2s;
  box-sizing: border-box; /* Đảm bảo padding không làm vỡ layout */
}

.form-group textarea {
  resize: vertical; /* Chỉ cho phép kéo giãn chiều dọc */
  min-height: 80px;
}

/* Hiệu ứng khi click vào ô nhập */
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #000; /* Viền đen khi focus */
  background-color: #fff;
}

/* 4. Nút bấm (Hủy & Xác nhận) */
.form-actions {
  display: flex;
  justify-content: flex-end; /* Đẩy nút sang phải */
  gap: 15px; /* Khoảng cách giữa 2 nút */
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

/* Nút Hủy */
.form-actions button[type="button"] {
  padding: 12px 24px;
  background-color: #fff;
  border: 1px solid #ddd;
  color: #555;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.form-actions button[type="button"]:hover {
  background-color: #f1f1f1;
  color: #000;
}

/* Nút Xác nhận (Style giống nút Checkout ở ngoài) */
.btn-confirm {
  padding: 12px 30px;
  background-color: #000;
  color: #fff;
  border: none;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: opacity 0.2s;
}

.btn-confirm:hover {
  opacity: 0.8;
}

.btn-confirm:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

/* 5. Animation (Tùy chọn) */
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
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

/* --- RESPONSIVE CHO MODAL --- */
@media (max-width: 500px) {
  .checkout-content {
    padding: 20px;
    width: 95%;
  }
  
  .form-actions {
    flex-direction: column-reverse; /* Nút xác nhận lên trên, hủy xuống dưới trên mobile */
  }
  
  .form-actions button {
    width: 100%;
  }
}
</style>