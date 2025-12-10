<template>
  <div class="profile-container">
    <div class="card">
      <div class="card-header">
        <h3>Hồ Sơ Của Tôi</h3>
        <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
      </div>
      
      <div class="card-body">
        <form @submit.prevent="saveProfile">
          
          <div class="form-group">
            <label>Tên Đăng Nhập</label>
            <div class="input-wrapper">
              <input 
                type="text" 
                v-model="user.username" 
                disabled 
                class="form-control read-only"
              >
            </div>
          </div>

          <div class="form-group">
            <label>Tên</label>
            <div class="input-wrapper">
              <input 
                type="text" 
                v-model="user.full_name" 
                class="form-control"
              >
            </div>
          </div>

          <div class="form-group">
            <label>Email</label>
            <div class="input-wrapper with-action">
              <input 
                type="email" 
                v-model="user.email" 
                :disabled="!isEmailEditable"
                class="form-control"
              >
              <a 
                href="#" 
                class="action-link" 
                @click.prevent="isEmailEditable = true"
                v-if="!isEmailEditable"
              >
                Thay đổi
              </a>
            </div>
          </div>

   <div class="form-group">
            <label>Số Điện Thoại</label>
            <div class="input-wrapper with-action">
              <input 
                type="tel" 
                v-model="user.phone" 
                :disabled="!isPhoneEditable"
                class="form-control"
                maxlength="10"
                placeholder="Nhập 10 chữ số"
                @input="user.phone = user.phone.replace(/[^0-9]/g, '')"
              >
              <a 
                href="#" 
                class="action-link" 
                @click.prevent="isPhoneEditable = true"
                v-if="!isPhoneEditable"
              >
                Thay đổi
              </a>
            </div>
          </div>

          <div class="form-footer">
            <button type="submit" class="btn-save">Lưu</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'; // Đảm bảo bạn đã config base URL và Token cho axios

export default {
  data() {
    return {
      user: {
        username: '',
        full_name: '',
        email: '',
        phone: ''
      },
      isEmailEditable: false,
      isPhoneEditable: false
    };
  },
  mounted() {
    this.fetchUserProfile();
  },
  methods: {
    // Lấy thông tin user khi load trang
    async fetchUserProfile() {
      try {
        const response = await axios.get('/profile');
        this.user = response.data.data;
      } catch (error) {
        console.error('Lỗi tải thông tin:', error);
        alert('Vui lòng đăng nhập lại');
      }
    },

    // Lưu thông tin
async saveProfile() {
    // 1. Định nghĩa Regex: Bắt đầu và kết thúc phải là số, đúng 10 ký tự
    const phoneRegex = /^\d{10}$/;

    // 2. THÊM ĐOẠN NÀY: Kiểm tra hợp lệ trước khi gọi API
    // Nếu không có số điện thoại HOẶC số điện thoại không khớp format
    if (!this.user.phone || !phoneRegex.test(this.user.phone)) {
        alert('Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 chữ số.');
        return; // Dừng hàm lại ngay lập tức
    }

    try {
        const response = await axios.post('/update', {
            full_name: this.user.full_name,
            email: this.user.email,
            phone: this.user.phone
        });

        alert(response.data.message);

        // Sau khi lưu thành công, khóa lại các ô input
        this.isEmailEditable = false;
        this.isPhoneEditable = false;

    } catch (error) {
        if (error.response && error.response.data.errors) {
            // Hiển thị lỗi từ backend (ví dụ email trùng)
            let errorMsg = Object.values(error.response.data.errors).flat().join('\n');
            alert(errorMsg);
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.');
        }
    }
}
  }
};
</script>

<style scoped>
.profile-container {
  max-width: 800px;
  margin: 20px auto;
  font-family: Arial, sans-serif;
}

.card {
  background: white;
  border-radius: 4px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  padding: 20px;
}

.card-header {
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
  margin-bottom: 20px;
}

.form-group {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.form-group label {
  width: 150px;
  text-align: right;
  margin-right: 20px;
  color: #555;
}

.input-wrapper {
  flex: 1;
  display: flex;
  align-items: center;
}

.form-control {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.form-control:disabled {
  background-color: #f9f9f9;
  color: #333;
  border: none; /* Làm cho giống text thường khi không edit */
}

.read-only {
  background-color: #eee !important;
  cursor: not-allowed;
}

.action-link {
  color: #0055AA;
  margin-left: 10px;
  text-decoration: underline;
  cursor: pointer;
  white-space: nowrap;
  font-size: 13px;
}

.form-footer {
  margin-top: 30px;
  padding-left: 170px; /* Căn chỉnh với input */
}

.btn-save {
  background-color: #000000; /* Màu cam giống Shopee */
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.btn-save:hover {
  background-color: #646161;
}
</style>