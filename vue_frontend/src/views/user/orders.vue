<template>
  <div class="shopee-container">
    <div class="main-content">
      
      <div class="tabs-header">
        <div 
          v-for="tab in tabs" 
          :key="tab.value"
          @click="changeTab(tab.value)"
          :class="['tab-item', currentStatus === tab.value ? 'active' : '']"
        >
          {{ tab.label }}
        </div>
      </div>

      <div v-if="loading" class="loading-state">Đang tải đơn hàng...</div>

      <div v-else class="order-list">
        
        <div v-if="orders.length === 0" class="empty-state">
          <img src="https://deo.shopeemobile.com/shopee/shopee-pcmall-live-sg/orderlist/5fafbb923393b712b96488590b8f781f.png" alt="Empty">
          <p>Chưa có đơn hàng nào</p>
        </div>

        <div v-for="order in orders" :key="order.id" class="order-card">
          
          <div class="card-header">
            <div class="shop-info">
              <span class="favorite-badge">Yêu thích</span>
              <span class="shop-name">Shop Florentic</span>
              <button class="btn-chat">Chat</button>
              <button class="btn-view-shop">Xem Shop</button>
            </div>
            <div class="order-status">
              {{ getStatusLabel(order.order_status) }}
            </div>
          </div>

          <div class="card-body">
            <div v-for="item in order.order_items" :key="item.id" class="product-item">
              <div class="img-wrapper">
                <img :src="getImageUrl(item.product_image)" alt="Product Image">
              </div>
              <div class="product-info">
                <h3 class="product-name">{{ item.product_name }}</h3>
                <div class="product-variant">Phân loại hàng: {{ item.color }}, {{ item.size }}</div>
                <div class="product-qty">x{{ item.quantity }}</div>
              </div>
              <div class="product-price">
                <span class="old-price">{{ formatCurrency(item.price * 1.2) }}</span>
                <span class="current-price">{{ formatCurrency(item.price) }}</span>
              </div>
            </div>
          </div>

          <div class="card-footer">
            <div class="total-section">
              <span class="total-label">Thành tiền:</span>
              <span class="total-price">{{ formatCurrency(order.total_amount) }}</span>
            </div>

            <div class="action-buttons">
              <div class="btn-group">
                <span class="note-text" v-if="order.order_status === 'cancelled'">Đã hủy bởi bạn</span>
                
                <button v-if="order.order_status === 'completed'" class="btn-solid">Đánh Giá</button>
                <button v-else class="btn-solid">Mua Lại</button>

                <button class="btn-outline">Liên Hệ Người Bán</button>
                <button class="btn-outline">Xem Chi Tiết</button>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      orders: [],
      loading: false,
      currentStatus: 'all',
      tabs: [
        { label: 'Tất cả', value: 'all' },
        { label: 'Chờ xác nhận', value: 'pending' },
        { label: 'Đang vận chuyển', value: 'shipping' },
        { label: 'Hoàn thành', value: 'completed' },
        { label: 'Đã hủy', value: 'cancelled' },
      ],
      // ĐỔI PORT NÀY THÀNH PORT BACKEND CỦA BẠN (VD: 8000)
      backendUrl: 'http://localhost:8000/' 
    };
  },
  mounted() {
    this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      this.loading = true;
      try {
        const response = await axios.get('/my-orders', {
          params: { status: this.currentStatus }
        });
        // Lấy data đúng cấu trúc Laravel Paginate
        this.orders = response.data.data.data; 
      } catch (error) {
        console.error("Lỗi:", error);
      } finally {
        this.loading = false;
      }
    },
    changeTab(val) {
      this.currentStatus = val;
      this.fetchOrders();
    },
    formatCurrency(val) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
    },
    // Xử lý ảnh: Nối domain backend vào đường dẫn tương đối
    getImageUrl(path) {
      if (!path) return 'https://via.placeholder.com/80';
      if (path.startsWith('http')) return path;
      // Tránh lỗi 2 dấu gạch chéo
      const baseUrl = this.backendUrl.endsWith('/') ? this.backendUrl : this.backendUrl + '/';
      const cleanPath = path.startsWith('/') ? path.substring(1) : path;
      return baseUrl + cleanPath;
    },
    getStatusLabel(status) {
      const map = {
        'pending': 'CHỜ XÁC NHẬN',
        'confirmed': 'ĐÃ XÁC NHẬN',
        'shipping': 'ĐANG VẬN CHUYỂN',
        'completed': 'HOÀN THÀNH',
        'cancelled': 'ĐÃ HỦY'
      };
      return map[status] || status;
    }
  }
};
</script>

<style scoped>
/* RESET CSS CƠ BẢN */
* { box-sizing: border-box; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }

.shopee-container {
  background-color: #f5f5f5; /* Màu nền xám đặc trưng Shopee */
  min-height: 100vh;
  padding-bottom: 50px;
}

.main-content {
  max-width: 980px;
  margin: 0 auto;
  padding-top: 20px;
}

/* --- 1. TABS --- */
.tabs-header {
  display: flex;
  background: white;
  border-bottom: 1px solid rgba(0,0,0,.09);
  position: sticky;
  top: 0;
  z-index: 10;
}

.tab-item {
  flex: 1;
  text-align: center;
  padding: 16px 0;
  cursor: pointer;
  font-size: 16px;
  color: rgba(0,0,0,.8);
  border-bottom: 2px solid transparent;
  transition: color .2s;
}

.tab-item:hover { color: #000000; }

.tab-item.active {
  color: #000000;
  border-bottom: 2px solid #000000;
  font-weight: 500;
}

/* --- 2. ORDER CARD --- */
.order-card {
  background: white;
  margin-top: 12px;
  box-shadow: 0 1px 1px 0 rgba(0,0,0,.05);
  border-radius: 2px;
}

/* Header */
.card-header {
  padding: 12px 24px;
  border-bottom: 1px solid rgba(0,0,0,.09);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.shop-info { display: flex; align-items: center; gap: 8px; }

.favorite-badge {
  background: #000000;
  color: white;
  font-size: 12px;
  padding: 2px 4px;
  border-radius: 2px;
  font-weight: 500;
}

.shop-name { font-weight: 600; font-size: 14px; margin-right: 5px; }

.btn-chat, .btn-view-shop {
  background: #160f0e;
  color: white;
  border: none;
  font-size: 12px;
  padding: 4px 8px;
  cursor: pointer;
  border-radius: 2px;
}
.btn-view-shop {
    background: white;
    border: 1px solid rgba(0,0,0,.26);
    color: #555;
}

.order-status {
  color: #000000;
  text-transform: uppercase;
  font-size: 14px;
}

/* Body (Sản phẩm) */
.card-body { padding: 12px 24px; }

.product-item {
  display: flex;
  padding: 12px 0;
  border-bottom: 1px solid rgba(0,0,0,.09);
}
.product-item:last-child { border-bottom: none; }

.img-wrapper img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border: 1px solid #e1e1e1;
  background-color: #fafafa;
}

.product-info {
  flex: 1;
  margin-left: 12px;
  display: flex;
  flex-direction: column;
}

.product-name {
  font-size: 16px;
  color: rgba(0,0,0,.87);
  margin: 0 0 5px 0;
  line-height: 20px;
  /* Cắt dòng nếu tên quá dài */
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.product-variant { color: rgba(0,0,0,.54); font-size: 14px; margin-bottom: 5px;}
.product-qty { color: rgba(0,0,0,.87); font-size: 14px; }

.product-price { text-align: right; display: flex; flex-direction: column; justify-content: center; }
.old-price { text-decoration: line-through; color: rgba(0,0,0,.26); font-size: 14px; margin-right: 5px; }
.current-price { color: #000000; font-size: 16px; }

/* Footer */
.card-footer {
  background: #fffefb; /* Màu nền vàng rất nhạt đặc trưng của footer đơn hàng */
  border-top: 1px solid rgba(0,0,0,.09);
  padding: 24px;
}

.total-section {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 15px;
}

.total-label { font-size: 14px; color: rgba(0,0,0,.8); margin-right: 10px; }
.total-price { color: #000000; font-size: 24px; font-weight: 500; }

.action-buttons { display: flex; justify-content: flex-end; }
.btn-group { display: flex; align-items: center; gap: 10px; }

.note-text { color: rgba(0,0,0,.54); font-size: 14px; margin-right: 10px; }

.btn-solid {
  background-color: #000000;
  color: #fff;
  border: 1px solid transparent;
  height: 40px;
  padding: 0 20px;
  min-width: 150px;
  border-radius: 2px;
  font-size: 14px;
  cursor: pointer;
  transition: opacity 0.2s;
}
.btn-solid:hover { opacity: 0.9; }

.btn-outline {
  background-color: #fff;
  color: #555;
  border: 1px solid rgba(0,0,0,.09);
  height: 40px;
  padding: 0 20px;
  min-width: 150px;
  border-radius: 2px;
  font-size: 14px;
  cursor: pointer;
  transition: background-color 0.2s;
}
.btn-outline:hover { background-color: rgba(0,0,0,.02); }

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 0;
  background: white;
}
.empty-state img { width: 100px; margin-bottom: 20px; }
.empty-state p { color: rgba(0,0,0,.54); font-size: 16px; }
</style>