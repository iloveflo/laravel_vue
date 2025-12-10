<template>
  <div class="container mt-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Quản Lý Mã Khuyến Mại</h4>
        <button class="btn btn-primary" @click="openModal()">
          <i class="fas fa-plus"></i> Thêm Mã Mới
        </button>
      </div>

      <div class="card-body border-bottom" style="border-bottom: 2px solid #000 !important; background: #f9f9f9;">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" 
                       v-model="filters.keyword" 
                       @input="handleSearch" 
                       placeholder="Nhập mã code để tìm...">
            </div>

            <div class="col-md-3">
                <select class="form-select" v-model="filters.status" @change="fetchCoupons(1)">
                    <option value="all">-- Tất cả trạng thái --</option>
                    <option value="active">Đang hoạt động</option>
                    <option value="inactive">Tạm khóa</option>
                </select>
            </div>

            <div class="col-md-3">
                <select class="form-select" v-model="filters.filter_expiry" @change="fetchCoupons(1)">
                    <option value="all">-- Tất cả thời hạn --</option>
                    <option value="valid">Còn hạn sử dụng</option>
                    <option value="expired">Đã hết hạn</option>
                </select>
            </div>

            <div class="col-md-2">
                <button class="btn btn-secondary w-100" @click="resetFilters">
                    <i class="fas fa-sync-alt"></i> Đặt lại
                </button>
            </div>
        </div>
      </div>
      <div class="card-body">
        <div v-if="alert.message" :class="['alert', alert.type === 'success' ? 'alert-success' : 'alert-danger']">
          {{ alert.message }}
        </div>

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p>Đang tải dữ liệu...</p>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>Mã Code</th>
                <th>Giảm giá</th>
                <th>Điều kiện</th>
                <th>Lượt dùng</th>
                <th>Thời gian</th>
                <th>Trạng thái</th>
                <th style="width: 150px;">Hành động</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="coupon in coupons" :key="coupon.id">
                <td>
                  <strong>{{ coupon.code }}</strong><br>
                  <small class="text-muted">{{ coupon.description }}</small>
                </td>
                <td>
                  <span v-if="coupon.discount_type === 'percent'" class="badge bg-info text-dark">
                    {{ coupon.discount_value }}%
                  </span>
                  <span v-else class="badge bg-success">
                    -{{ formatCurrency(coupon.discount_value) }}
                  </span>
                  <div v-if="coupon.max_discount" class="small text-danger">
                    Tối đa: {{ formatCurrency(coupon.max_discount) }}
                  </div>
                </td>
                <td>
                  <small>Đơn tối thiểu:</small><br>
                  {{ formatCurrency(coupon.min_order_value || 0) }}
                </td>
                <td>
                  {{ coupon.used_count }} / {{ coupon.usage_limit || '∞' }}
                </td>
                <td>
                  <small>
                    Start: {{ formatDate(coupon.start_date) }} <br>
                    End: {{ formatDate(coupon.end_date) }}
                  </small>
                </td>
                <td>
                  <span :class="['badge', coupon.status === 'active' ? 'bg-success' : 'bg-secondary']">
                    {{ coupon.status === 'active' ? 'Hoạt động' : 'Tạm khóa' }}
                  </span>
                </td>
                <td>
                  <button class="btn btn-sm btn-warning me-2" @click="editCoupon(coupon)">Sửa</button>
                  <button class="btn btn-sm btn-danger" @click="deleteCoupon(coupon.id)">Xóa</button>
                </td>
              </tr>
              <tr v-if="coupons.length === 0">
                <td colspan="7" class="text-center">
                    Không tìm thấy dữ liệu phù hợp.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <nav v-if="pagination.last_page > 1" class="mt-3">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
              <button class="page-link" @click="fetchCoupons(pagination.current_page - 1)">Trước</button>
            </li>
            
            <li v-for="page in pagination.last_page" :key="page" 
                class="page-item" :class="{ active: page === pagination.current_page }">
              <button class="page-link" @click="fetchCoupons(page)">{{ page }}</button>
            </li>

            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
              <button class="page-link" @click="fetchCoupons(pagination.current_page + 1)">Sau</button>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <div v-if="showModal" class="modal-backdrop-custom">
      <div class="modal-dialog-custom card">
        <div class="card-header d-flex justify-content-between">
          <h5 class="mb-0">{{ isEditing ? 'Cập nhật Mã' : 'Thêm Mã Mới' }}</h5>
          <button type="button" class="btn-close" @click="closeModal()"></button>
        </div>
        <div class="card-body">
          <form @submit.prevent="saveCoupon">
             <div class="row g-3">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Mã Code <span class="text-danger">*</span></label>
                  <input type="text" v-model="form.code" class="form-control" :class="{ 'is-invalid': errors.code }" placeholder="VD: SALE2025">
                  <div class="invalid-feedback">{{ errors.code?.[0] }}</div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Loại giảm giá</label>
                  <select v-model="form.discount_type" class="form-select">
                    <option value="percent">Phần trăm (%)</option>
                    <option value="fixed">Số tiền cố định</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                  <input type="number" v-model="form.discount_value" class="form-control" :class="{ 'is-invalid': errors.discount_value }">
                  <div class="invalid-feedback">{{ errors.discount_value?.[0] }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Giảm tối đa (Nếu là %)</label>
                    <input type="number" v-model="form.max_discount" class="form-control">
                </div>
              </div>

              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Đơn tối thiểu</label>
                  <input type="number" v-model="form.min_order_value" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Giới hạn số lần dùng</label>
                  <input type="number" v-model="form.usage_limit" class="form-control" placeholder="Để trống nếu không giới hạn">
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Ngày bắt đầu</label>
                        <input type="datetime-local" v-model="form.start_date" class="form-control" :class="{ 'is-invalid': errors.start_date }">
                        <div class="invalid-feedback">{{ errors.start_date?.[0] }}</div>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Ngày kết thúc</label>
                        <input type="datetime-local" v-model="form.end_date" class="form-control" :class="{ 'is-invalid': errors.end_date }">
                         <div class="invalid-feedback">{{ errors.end_date?.[0] }}</div>
                    </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Trạng thái</label>
                  <select v-model="form.status" class="form-select">
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Tạm khóa</option>
                  </select>
                </div>
              </div>
              
              <div class="col-12 mb-3">
                 <label class="form-label">Mô tả</label>
                 <textarea v-model="form.description" class="form-control" rows="2"></textarea>
              </div>
            </div>

            <div class="text-end mt-3">
              <button type="button" class="btn btn-secondary me-2" @click="closeModal()">Hủy</button>
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                {{ isSubmitting ? 'Đang lưu...' : 'Lưu lại' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

// --- CONFIG ---
// Đảm bảo đường dẫn này đúng với route trong routes/api.php
const API_URL = '/admin/coupons'; 

// --- STATE ---
const coupons = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const isSubmitting = ref(false);
const pagination = ref({
    current_page: 1,
    last_page: 1,
    total: 0
});

// BIẾN LƯU TRẠNG THÁI BỘ LỌC (MỚI)
const filters = reactive({
    keyword: '',
    status: 'all',
    filter_expiry: 'all'
});
let searchTimeout = null; // Để debounce search

const alert = reactive({ message: '', type: '' });
const errors = ref({});

// Form Model
const form = reactive({
    id: null,
    code: '',
    description: '',
    discount_type: 'percent', 
    discount_value: 0,
    min_order_value: 0,
    max_discount: null,
    usage_limit: null,
    start_date: '',
    end_date: '',
    status: 'active'
});

// --- METHODS ---

// 1. Lấy danh sách Coupon (CẬP NHẬT)
const fetchCoupons = async (page = 1) => {
    loading.value = true;
    try {
        // Tạo params gửi lên
        const params = {
            page: page,
            // Nếu là 'all' thì không gửi lên, hoặc gửi rỗng tùy backend xử lý
            status: filters.status !== 'all' ? filters.status : null,
            filter_expiry: filters.filter_expiry !== 'all' ? filters.filter_expiry : null,
            keyword: filters.keyword
        };

        const response = await axios.get(API_URL, { params });
        
        const result = response.data.data; // Cấu trúc trả về từ Laravel Paginate
        coupons.value = result.data;
        pagination.value = {
            current_page: result.current_page,
            last_page: result.last_page,
            total: result.total
        };
    } catch (error) {
        console.error(error);
        showAlert('Không thể tải dữ liệu', 'error');
    } finally {
        loading.value = false;
    }
};

// Hàm xử lý tìm kiếm có delay (Debounce) để tránh gọi API liên tục khi gõ
const handleSearch = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchCoupons(1); // Reset về trang 1 khi tìm kiếm
    }, 500); // Đợi 500ms sau khi ngừng gõ mới tìm
};

// Hàm đặt lại bộ lọc
const resetFilters = () => {
    filters.keyword = '';
    filters.status = 'all';
    filters.filter_expiry = 'all';
    fetchCoupons(1);
};

// 2. Mở Modal
const openModal = () => {
    resetForm();
    isEditing.value = false;
    showModal.value = true;
    errors.value = {};
};

const editCoupon = (coupon) => {
    Object.assign(form, coupon);
    if(form.start_date) form.start_date = form.start_date.slice(0, 16);
    if(form.end_date) form.end_date = form.end_date.slice(0, 16);
    isEditing.value = true;
    showModal.value = true;
    errors.value = {};
};

const closeModal = () => {
    showModal.value = false;
};

// 3. Lưu
const saveCoupon = async () => {
    isSubmitting.value = true;
    errors.value = {}; 

    try {
        let response;
        if (isEditing.value) {
            response = await axios.put(`${API_URL}/${form.id}`, form);
        } else {
            response = await axios.post(API_URL, form);
        }

        showAlert(response.data.message, 'success');
        closeModal();
        fetchCoupons(pagination.value.current_page); 

    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            showAlert('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        }
    } finally {
        isSubmitting.value = false;
    }
};

// 4. Xóa
const deleteCoupon = async (id) => {
    if (!confirm('Bạn có chắc chắn muốn xóa mã giảm giá này?')) return;

    try {
        await axios.delete(`${API_URL}/${id}`);
        showAlert('Đã xóa mã giảm giá.', 'success');
        fetchCoupons(pagination.value.current_page);
    } catch (error) {
        const msg = error.response?.data?.message || 'Không thể xóa.';
        showAlert(msg, 'error');
    }
};

const resetForm = () => {
    Object.assign(form, {
        id: null, code: '', description: '', discount_type: 'percent',
        discount_value: 0, min_order_value: 0, max_discount: null,
        usage_limit: null, start_date: '', end_date: '', status: 'active'
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN');
};

const showAlert = (msg, type) => {
    alert.message = msg;
    alert.type = type;
    setTimeout(() => { alert.message = ''; }, 3000);
};

onMounted(() => {
    fetchCoupons();
});
</script>

<style scoped>
/* ========================================
   MINIMAL BLACK & WHITE THEME
   Modern | Square | Artistic
   ======================================== */

:root {
  --color-black: #000000;
  --color-white: #ffffff;
  --color-gray-50: #fafafa;
  --color-gray-100: #f5f5f5;
  --color-gray-200: #e5e5e5;
  --color-gray-300: #d4d4d4;
  --color-gray-400: #a3a3a3;
  --color-gray-600: #525252;
  --color-gray-800: #262626;
  --color-gray-900: #171717;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
  background: var(--color-white);
  color: var(--color-black);
  line-height: 1.6;
  letter-spacing: -0.02em;
}

/* ========================================
   CONTAINER & LAYOUT
   ======================================== */

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.mt-4 {
  margin-top: 2rem;
}

/* ========================================
   CARD COMPONENTS
   ======================================== */

.card {
  background: var(--color-white);
  border: 2px solid var(--color-black);
  border-radius: 0;
  box-shadow: 8px 8px 0 var(--color-black);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translate(-2px, -2px);
  box-shadow: 10px 10px 0 var(--color-black);
}

.card-header {
  background: var(--color-black);
  color: var(--color-white);
  padding: 1.5rem 2rem;
  border-bottom: 2px solid var(--color-black);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-header h4,
.card-header h5 {
  margin: 0;
  font-weight: 700;
  font-size: 1.5rem;
  letter-spacing: -0.03em;
  text-transform: uppercase;
}

.card-body {
  padding: 2rem;
  background: var(--color-white);
}

/* ========================================
   BUTTONS
   ======================================== */

.btn {
  font-family: inherit;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.75rem 1.5rem;
  border: 2px solid var(--color-black);
  border-radius: 0;
  cursor: pointer;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--color-black);
  color: var(--color-white);
}

.btn-primary:hover:not(:disabled) {
  background: var(--color-white);
  color: var(--color-black);
  transform: translate(-2px, -2px);
  box-shadow: 4px 4px 0 var(--color-black);
}

.btn-secondary {
  background: var(--color-white);
  color: var(--color-black);
}

.btn-secondary:hover:not(:disabled) {
  background: var(--color-gray-100);
  transform: translate(-2px, -2px);
  box-shadow: 4px 4px 0 var(--color-black);
}

.btn-warning {
  background: var(--color-white);
  color: var(--color-black);
  border-color: var(--color-gray-400);
}

.btn-warning:hover {
  background: var(--color-gray-900);
  color: var(--color-white);
  border-color: var(--color-black);
}

.btn-danger {
  background: var(--color-black);
  color: var(--color-white);
}

.btn-danger:hover {
  background: var(--color-gray-800);
  transform: translate(-2px, -2px);
  box-shadow: 4px 4px 0 var(--color-black);
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.75rem;
}

.btn-close {
  background: transparent;
  border: none;
  color: var(--color-white);
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease;
}

.btn-close::before {
  content: "×";
  font-size: 2rem;
  line-height: 1;
}

.btn-close:hover {
  transform: rotate(90deg);
}

.me-2 {
  margin-right: 0.5rem;
}

/* ========================================
   ALERTS
   ======================================== */

.alert {
  padding: 1rem 1.5rem;
  border: 2px solid var(--color-black);
  margin-bottom: 1.5rem;
  font-weight: 500;
  border-radius: 0;
}

.alert-success {
  background: var(--color-white);
  border-left: 6px solid var(--color-black);
}

.alert-danger {
  background: var(--color-black);
  color: var(--color-white);
}

/* ========================================
   TABLE
   ======================================== */

.table-responsive {
  overflow-x: auto;
  border: 2px solid var(--color-black);
}

.table {
  width: 100%;
  border-collapse: collapse;
  background: var(--color-white);
}

.table thead {
  background: var(--color-black);
  color: var(--color-white);
}

.table th {
  padding: 1rem;
  text-align: left;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.1em;
  border: 2px solid var(--color-black);
}

.table td {
  padding: 1rem;
  border: 2px solid var(--color-black);
  vertical-align: top;
}

.table tbody tr {
  transition: background 0.15s ease;
}

.table tbody tr:hover {
  background: var(--color-gray-50);
}

.table tbody tr:nth-child(even) {
  background: var(--color-gray-50);
}

.table tbody tr:nth-child(even):hover {
  background: var(--color-gray-100);
}

/* ========================================
   BADGES
   ======================================== */

.badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border: 2px solid var(--color-black);
  border-radius: 0;
}

.bg-success {
  background: var(--color-black);
  color: var(--color-white);
}

.bg-secondary {
  background: var(--color-white);
  color: var(--color-black);
}

.bg-info {
  background: var(--color-gray-200);
  color: var(--color-black);
  border-color: var(--color-gray-400);
}

.text-dark {
  color: var(--color-black);
}

/* ========================================
   FORM ELEMENTS
   ======================================== */

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.form-control,
.form-select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid var(--color-black);
  border-radius: 0;
  font-family: inherit;
  font-size: 1rem;
  background: var(--color-white);
  transition: all 0.2s ease;
}

.form-control:focus,
.form-select:focus {
  outline: none;
  border-color: var(--color-black);
  box-shadow: 4px 4px 0 var(--color-gray-300);
  transform: translate(-2px, -2px);
}

.form-control.is-invalid {
  border-color: var(--color-black);
  border-width: 3px;
}

textarea.form-control {
  resize: vertical;
  min-height: 100px;
}

.invalid-feedback {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: var(--color-black);
  font-weight: 600;
}

.mb-0 {
  margin-bottom: 0;
}

.mb-3 {
  margin-bottom: 1.5rem;
}

.mt-3 {
  margin-top: 1.5rem;
}

/* ========================================
   GRID SYSTEM
   ======================================== */

.row {
  display: flex;
  flex-wrap: wrap;
  margin: 0 -0.75rem;
}

.col-md-6,
.col-6,
.col-12 {
  padding: 0 0.75rem;
}

.col-12 {
  width: 100%;
}

.col-6 {
  width: 50%;
}

.col-md-6 {
  width: 50%;
}

.g-3 {
  margin: 0 -0.75rem;
}

/* ========================================
   LOADING & SPINNER
   ======================================== */

.text-center {
  text-align: center;
}

.py-5 {
  padding: 3rem 0;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
  border: 4px solid var(--color-gray-300);
  border-top-color: var(--color-black);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* ========================================
   PAGINATION
   ======================================== */

.pagination {
  display: flex;
  list-style: none;
  gap: 0.5rem;
  padding: 0;
  margin: 0;
}

.justify-content-center {
  justify-content: center;
}

.page-item {
  list-style: none;
}

.page-link {
  padding: 0.5rem 1rem;
  border: 2px solid var(--color-black);
  background: var(--color-white);
  color: var(--color-black);
  cursor: pointer;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.875rem;
  transition: all 0.15s ease;
}

.page-link:hover {
  background: var(--color-black);
  color: var(--color-white);
  transform: translate(-2px, -2px);
  box-shadow: 4px 4px 0 var(--color-gray-400);
}

.page-item.active .page-link {
  background: var(--color-black);
  color: var(--color-white);
}

.page-item.disabled .page-link {
  opacity: 0.3;
  cursor: not-allowed;
}

.page-item.disabled .page-link:hover {
  background: var(--color-white);
  color: var(--color-black);
  transform: none;
  box-shadow: none;
}

/* ========================================
   MODAL
   ======================================== */

.modal-backdrop-custom {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(254, 250, 250, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
  animation: fadeIn 0.2s ease;
  backdrop-filter: blur(4px);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-dialog-custom {
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s ease;
  background: var(--color-white);
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* ========================================
   UTILITY CLASSES
   ======================================== */

.d-flex {
  display: flex;
}

.justify-content-between {
  justify-content: space-between;
}

.align-items-center {
  align-items: center;
}

.text-end {
  text-align: right;
}

.text-danger {
  color: var(--color-black);
  font-weight: 700;
}

.text-muted {
  color: var(--color-gray-600);
  font-size: 0.875rem;
}

.small {
  font-size: 0.875rem;
}

strong {
  font-weight: 700;
}

/* ========================================
   RESPONSIVE
   ======================================== */

@media (max-width: 768px) {
  .col-md-6 {
    width: 100%;
  }
  
  .card-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }
  
  .table {
    font-size: 0.875rem;
  }
  
  .table th,
  .table td {
    padding: 0.75rem 0.5rem;
  }
  
  .btn-sm {
    padding: 0.4rem 0.75rem;
    font-size: 0.7rem;
  }
  
  .modal-dialog-custom {
    margin: 1rem;
  }

  .container {
    padding: 1.5rem 1rem;
  }
}
</style>