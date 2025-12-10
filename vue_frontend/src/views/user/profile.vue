<template>
  <div class="profile-container">
    <div class="card">
      <div class="card-header">
        <h3>Hồ Sơ Của Tôi</h3>
        <p>Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
      </div>
      
      <div class="card-body">
        <form @submit.prevent="saveProfile">
          
          <div class="avatar-section">
            <div class="avatar-wrapper">
                <img :src="getAvatarSrc()" alt="Avatar" class="avatar-img">
                
                <div class="avatar-action">
                    <button type="button" class="btn-select-img" @click="$refs.fileInput.click()">
                        Chọn Ảnh
                    </button>
                    
                    <input 
                        type="file" 
                        ref="fileInput" 
                        style="display: none" 
                        accept="image/jpeg, image/png, image/jpg" 
                        @change="handleFileUpload"
                    >
                    <p class="file-hint">Dụng lượng file tối đa 2 MB<br>Định dạng: .JPEG, .PNG</p>
                </div>
            </div>
          </div>

          <hr class="divider">

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
            <label>Họ Tên</label>
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
            <div class="input-wrapper">
              <input 
                type="email" 
                v-model="user.email" 
                disabled 
                class="form-control read-only"
              >
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

          <div class="form-group">
            <label>Địa Chỉ</label>
            <div class="input-wrapper with-action">
              <input 
                type="text" 
                v-model="user.address" 
                :disabled="!isAddressEditable"
                class="form-control"
                placeholder="Nhập địa chỉ nhận hàng"
              >
              <a 
                href="#" 
                class="action-link" 
                @click.prevent="isAddressEditable = true"
                v-if="!isAddressEditable"
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
import axios from 'axios';

export default {
  data() {
    return {
      user: {
        username: '',
        full_name: '',
        email: '',
        phone: '',
        address: '', 
        avatar: null
      },
      // State quản lý upload ảnh
      selectedFile: null,
      previewAvatar: null,
      
      // State quản lý edit
      isPhoneEditable: false,
      isAddressEditable: false,

      // Cấu hình đường dẫn ảnh gốc (Sửa lại theo domain thực tế của bạn)
      // Laravel storage link thường là: http://domain/storage/
      imageBaseUrl: 'http://localhost:8000/storage/' 
    };
  },
  mounted() {
    this.fetchUserProfile();
  },
  methods: {
    // 1. Lấy thông tin User
    async fetchUserProfile() {
      try {
        const response = await axios.get('/profile'); // Route API lấy user
        // Gán dữ liệu, đảm bảo address không bị null
        this.user = {
            ...response.data.data,
            address: response.data.data.address || ''
        };
      } catch (error) {
        console.error('Lỗi tải thông tin:', error);
        // Xử lý logout nếu cần
      }
    },

    // 2. Helper hiển thị ảnh
    getAvatarSrc() {
        // Nếu đang có ảnh preview (người dùng vừa chọn file xong)
        if (this.previewAvatar) {
            return this.previewAvatar;
        }
        // Nếu user có ảnh từ DB
        if (this.user.avatar) {
            // Kiểm tra xem trong DB lưu full link hay path tương đối
            if (this.user.avatar.startsWith('http')) {
                return this.user.avatar;
            }
            // Nối với đường dẫn storage của Laravel
            return this.imageBaseUrl + this.user.avatar;
        }
        // Ảnh mặc định nếu chưa có gì
        return 'https://via.placeholder.com/150'; 
    },

    // 3. Xử lý khi chọn file từ máy tính
    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate client size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert("File quá lớn! Vui lòng chọn ảnh dưới 2MB.");
            return;
        }

        this.selectedFile = file;
        // Tạo URL ảo để hiển thị ngay lập tức (Preview)
        this.previewAvatar = URL.createObjectURL(file);
    },

    // 4. Lưu thông tin (Dùng FormData)
    async saveProfile() {
      // Validate Phone Client-side
      const phoneRegex = /^\d{10}$/;
      if (!this.user.phone || !phoneRegex.test(this.user.phone)) {
        alert('Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 chữ số.');
        return;
      }

      // Tạo FormData để gửi dữ liệu bao gồm cả file
      let formData = new FormData();
      formData.append('full_name', this.user.full_name);
      formData.append('phone', this.user.phone);
      formData.append('address', this.user.address || ''); // Gửi chuỗi rỗng nếu null

      // Chỉ gửi avatar nếu có chọn file mới
      if (this.selectedFile) {
          formData.append('avatar', this.selectedFile);
      }
      
      // Lưu ý: Không append email vì backend không cho sửa

      try {
        // Gửi POST request
        // Axios tự động set Content-Type: multipart/form-data khi thấy FormData
        const response = await axios.post('/update', formData);

        alert(response.data.message);

        // Reset trạng thái edit
        this.isPhoneEditable = false;
        this.isAddressEditable = false;

        // Cập nhật lại thông tin mới nhất từ server trả về
        if (response.data.data) {
            this.user = response.data.data;
            // Reset phần upload
            this.selectedFile = null;
            this.previewAvatar = null;
        }

      } catch (error) {
        if (error.response && error.response.data.errors) {
          // Hiển thị lỗi validate từ Backend
          let errorMsg = Object.values(error.response.data.errors).flat().join('\n');
          alert(errorMsg);
        } else {
          console.error(error);
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

/* AVATAR CSS */
.avatar-section {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}
.avatar-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}
.avatar-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #ddd;
}
.btn-select-img {
    background: white;
    border: 1px solid #ddd;
    padding: 6px 15px;
    cursor: pointer;
    font-size: 13px;
    color: #555;
    box-shadow: 0 1px 1px rgba(0,0,0,0.05);
}
.btn-select-img:hover {
    background-color: #f8f8f8;
}
.file-hint {
    font-size: 12px;
    color: #999;
    text-align: center;
    margin-top: 5px;
}
.divider {
    border: 0;
    border-top: 1px solid #f1f1f1;
    margin-bottom: 25px;
}

/* FORM CSS */
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
  padding: 9px 12px;
  border: 1px solid #ddd;
  border-radius: 2px;
  font-size: 14px;
  outline: none;
}
.form-control:focus {
    border-color: #888;
}

/* Style cho input bị khóa */
.form-control:disabled {
  background-color: #fff; /* Giữ màu trắng nếu chỉ disabled tạm thời */
  color: #333;
}

/* Style đặc biệt cho field Read-only vĩnh viễn (Email, Username) */
.read-only {
  background-color: #f5f5f5 !important;
  color: #888 !important;
  cursor: not-allowed;
  border-color: #eee !important;
}

.action-link {
  color: #0055AA;
  margin-left: 15px;
  text-decoration: none;
  cursor: pointer;
  white-space: nowrap;
  font-size: 13px;
}
.action-link:hover {
    text-decoration: underline;
}

.form-footer {
  margin-top: 30px;
  padding-left: 170px; /* Căn thẳng hàng với input */
}

.btn-save {
  background-color: #000000; /* Màu cam Shopee */
  color: white;
  border: none;
  padding: 10px 25px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  box-shadow: 0 1px 1px rgba(0,0,0,0.1);
}

.btn-save:hover {
  background-color: #5a5351;
}
</style>