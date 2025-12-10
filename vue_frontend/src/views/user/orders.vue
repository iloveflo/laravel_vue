<template>
  <div class="container mx-auto p-4 bg-gray-100 min-h-screen">
    <div class="flex bg-white shadow-sm mb-4 sticky top-0 z-10 overflow-x-auto">
      <button 
        v-for="tab in tabs" 
        :key="tab.value"
        @click="changeTab(tab.value)"
        :class="['flex-1 py-4 px-2 text-center whitespace-nowrap border-b-2 font-medium transition-colors', 
                 currentStatus === tab.value ? 'border-orange-500 text-orange-500' : 'border-transparent text-gray-600 hover:text-orange-500']"
      >
        {{ tab.label }}
      </button>
    </div>

    <div v-if="loading" class="text-center py-10">
      <span class="text-gray-500">Đang tải đơn hàng...</span>
    </div>

    <div v-else>
      <div v-if="orders.length === 0" class="text-center py-20 bg-white">
        <img src="https://deo.shopeemobile.com/shopee/shopee-pcmall-live-sg/orderlist/5fafbb923393b712b96488590b8f781f.png" class="h-32 mx-auto mb-4" alt="No orders">
        <p class="text-gray-500">Chưa có đơn hàng nào.</p>
      </div>

      <div v-for="order in orders" :key="order.id" class="bg-white mb-4 p-4 rounded shadow-sm">
        <div class="flex justify-between items-center border-b pb-3 mb-3">
          <div class="font-bold text-sm">Shop Florentic</div>
          <div class="text-orange-500 text-sm font-medium uppercase">
            {{ getStatusLabel(order.order_status) }}
          </div>
        </div>

        <div v-for="item in order.order_items" :key="item.id" class="flex py-2 border-b last:border-0">
          <img :src="getImageUrl(item.product_image)" class="w-20 h-20 object-cover border rounded mr-4" alt="Product">
          
          <div class="flex-1">
            <h3 class="text-gray-800 font-medium line-clamp-2">{{ item.product_name }}</h3>
            <div class="text-gray-500 text-sm mt-1">Phân loại hàng: {{ item.color }}, {{ item.size }}</div>
            <div class="text-gray-900 mt-1">x{{ item.quantity }}</div>
          </div>
          
          <div class="text-right">
            <div class="text-orange-500 font-medium">{{ formatCurrency(item.price) }}</div>
            </div>
        </div>

        <div class="pt-4 border-t mt-2">
          <div class="flex justify-end items-center mb-4">
            <span class="text-gray-600 mr-2">Thành tiền:</span>
            <span class="text-xl font-bold text-orange-500">{{ formatCurrency(order.total_amount) }}</span>
          </div>

          <div class="flex justify-end gap-2">
            <button v-if="order.order_status === 'completed'" class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600">Đánh Giá</button>
            <button v-if="order.order_status === 'completed' || order.order_status === 'cancelled'" class="border border-gray-300 px-6 py-2 rounded hover:bg-gray-50">Mua Lại</button>
            <button v-if="order.order_status === 'pending'" class="border border-gray-300 px-6 py-2 rounded hover:bg-gray-50">Hủy Đơn</button>
            <button class="border border-gray-300 px-6 py-2 rounded hover:bg-gray-50">Liên Hệ Người Bán</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'; // Đảm bảo đã cấu hình axios với auth token

export default {
  data() {
    return {
      orders: [],
      loading: false,
      currentStatus: 'all',
      // Map các tab với giá trị enum trong DB
      tabs: [
        { label: 'Tất cả', value: 'all' },
        { label: 'Chờ xác nhận', value: 'pending' },
        { label: 'Vận chuyển', value: 'shipping' },
        { label: 'Hoàn thành', value: 'completed' },
        { label: 'Đã hủy', value: 'cancelled' },
        // { label: 'Trả hàng/Hoàn tiền', value: 'refunded' } // Nếu DB có
      ]
    };
  },
  mounted() {
    this.fetchOrders();
  },
  methods: {
    async fetchOrders() {
      this.loading = true;
      try {
        // Gọi API Laravel
        const response = await axios.get('/my-orders', {
          params: { status: this.currentStatus }
        });
        this.orders = response.data.data.data; // .data.data do Laravel paginate trả về object
      } catch (error) {
        console.error("Lỗi tải đơn hàng:", error);
      } finally {
        this.loading = false;
      }
    },
    changeTab(status) {
      this.currentStatus = status;
      this.fetchOrders();
    },
    formatCurrency(value) {
      return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
    },
    getImageUrl(path) {
      // Vì DB lưu 'uploads/products/...' nên cần ghép với domain gốc
      if (!path) return '/images/default-product.png'; 
      if (path.startsWith('http')) return path;
      return `http://localhost:8000/${path}`; // Thay localhost bằng domain backend thực tế
    },
    getStatusLabel(status) {
      const map = {
        'pending': 'Chờ xác nhận',
        'confirmed': 'Đã xác nhận',
        'shipping': 'Đang vận chuyển',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy'
      };
      return map[status] || status;
    }
  }
};
</script>
<style scoped>

</style>