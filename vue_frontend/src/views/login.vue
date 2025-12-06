<template>
  <div class="login-page-wrapper">
    <div class="login-container">
      <h2 class="login-title">ĐĂNG NHẬP</h2>
      
      <form @submit.prevent="handleLogin" class="login-form">
        
        <div class="form-group">
          <label class="form-label">EMAIL</label>
          <input 
            type="email" 
            v-model="email" 
            required 
            placeholder="NHẬP EMAIL CỦA BẠN"
            class="form-input"
          />
        </div>

        <div class="form-group">
          <div class="label-row">
            <label class="form-label">MẬT KHẨU</label>
            <router-link to="/forgot-password" class="forgot-link">QUÊN MẬT KHẨU?</router-link>
          </div>
          <input 
            type="password" 
            v-model="password" 
            required 
            placeholder="NHẬP MẬT KHẨU"
            class="form-input"
          />
        </div>

        <div class="form-group checkbox-group">
          <label class="custom-checkbox">
            <input type="checkbox" v-model="rememberMe">
            <span class="checkmark"></span>
            <span class="checkbox-label">GHI NHỚ ĐĂNG NHẬP</span>
          </label>
        </div>

        <p v-if="errorMessage" class="error-msg">{{ errorMessage }}</p>

        <button type="submit" class="btn-submit">ĐĂNG NHẬP</button>

        <div class="divider-text">HOẶC</div>

        <router-link to="/register" class="btn-register">Đăng ký</router-link>
        
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const email = ref('')
const password = ref('')
const rememberMe = ref(false)
const errorMessage = ref('')

// Nếu lần trước có lưu email, auto-fill cho user thường
onMounted(async () => {
  const token = localStorage.getItem('token')
  const remembered = localStorage.getItem('rememberedEmail')

  if (token) {
    try {
      // Lấy thông tin người dùng hiện tại
      const res = await axios.get('/me', {
        headers: { Authorization: `Bearer ${token}` }
      })
      const currentUser = res.data.user

      // Nếu là admin → redirect thẳng
      if (currentUser.role === 'admin') {
        window.location.href = '/admin'
        return
      }

      // Nếu là user thường → auto-fill remembered email
      if (remembered) {
        email.value = remembered
        rememberMe.value = true
      }

    } catch (err) {
      console.log('Không thể lấy thông tin user:', err)
      localStorage.removeItem('token')
      localStorage.removeItem('rememberedEmail')
    }
  } else if (remembered) {
    // Nếu chưa có token nhưng có rememberedEmail → auto-fill
    email.value = remembered
    rememberMe.value = true
  }
})

const handleLogin = async () => {
  errorMessage.value = ''

  try {
    const response = await axios.post('/login', {
      email: email.value,
      password: password.value,
      rememberMe: rememberMe.value,
    })

    const userRole = response.data.user.role

    // Lưu token
    localStorage.setItem('token', response.data.token)

    // Chỉ lưu rememberedEmail nếu không phải admin
    if (userRole !== 'admin' && rememberMe.value) {
      localStorage.setItem('rememberedEmail', email.value)
    } else {
      localStorage.removeItem('rememberedEmail')
    }

    // Redirect + reload
    if (userRole === 'admin') {
      window.location.href = '/admin'
    } else {
      window.location.href = '/'
    }

  } catch (error) {
    console.error('Login error:', error)
    if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Đăng nhập thất bại. Vui lòng kiểm tra email và mật khẩu.'
    }
  }
}
</script>



<style scoped>
/* --- Layout Chung --- */
.login-page-wrapper {
  display: flex;
  justify-content: center;
  padding: 110px 20px;
  background-color: #fff;
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
}

.login-container {
  width: 100%;
  max-width: 480px; /* Độ rộng vừa phải, chuẩn form login */
  background: #fff;
  /* Có thể thêm border bao quanh nếu thích kiểu thẻ bài */
  /* border: 2px solid #000; padding: 40px; */ 
}

/* --- Typography --- */
.login-title {
  font-size: 32px;
  font-weight: 900;
  text-align: center;
  margin-bottom: 40px;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: #000;
}

/* --- Inputs & Labels --- */
.form-group {
  margin-bottom: 24px;
}

.label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.form-label {
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1px;
  color: #000;
  text-transform: uppercase;
  display: block;
  margin-bottom: 8px; /* Khoảng cách với input */
}

.form-input {
  width: 100%;
  padding: 14px 16px;
  font-size: 14px;
  border: 1px solid #ccc; /* Viền xám mỏng ban đầu */
  background-color: #fff;
  color: #000;
  outline: none;
  transition: all 0.2s ease;
  
  /* Vuông vức tuyệt đối */
  border-radius: 0; 
}

/* Focus effect: Viền đen đậm */
.form-input:focus {
  border-color: #000;
  border-width: 1px; /* Hoặc 2px nếu muốn gắt hơn */
}

.form-input::placeholder {
  color: #999;
  font-size: 12px;
  text-transform: uppercase;
}

/* --- Links --- */
.forgot-link {
  font-size: 11px;
  color: #666;
  text-decoration: underline;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: color 0.2s;
}

.forgot-link:hover {
  color: #000;
}

/* --- Custom Checkbox (Vuông) --- */
.checkbox-group {
  margin-top: -10px;
  margin-bottom: 30px;
}

.custom-checkbox {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Ẩn input gốc */
.custom-checkbox input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

/* Tạo ô vuông mới */
.checkmark {
  height: 16px;
  width: 16px;
  background-color: #fff;
  border: 1px solid #ccc;
  margin-right: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  border-radius: 0; /* Vuông góc */
}

/* Khi hover */
.custom-checkbox:hover input ~ .checkmark {
  border-color: #000;
}

/* Khi checked: Đổi nền thành đen */
.custom-checkbox input:checked ~ .checkmark {
  background-color: #000;
  border-color: #000;
}

/* Dấu tích bên trong (tạo bằng CSS pseudo-element) */
.checkmark:after {
  content: "";
  display: none;
  width: 4px;
  height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
  margin-bottom: 2px;
}

.custom-checkbox input:checked ~ .checkmark:after {
  display: block;
}

/* --- Buttons --- */
.btn-submit, .btn-register {
  display: block;
  width: 100%;
  padding: 16px;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 2px;
  cursor: pointer;
  text-align: center;
  text-decoration: none;
  transition: all 0.3s;
  border-radius: 0; /* Vuông góc */
}

/* Nút Login: Đen chủ đạo */
.btn-submit {
  background-color: #000;
  color: #fff;
  border: 2px solid #000;
}

.btn-submit:hover {
  background-color: #fff;
  color: #000;
}

/* Nút Register: Trắng chủ đạo */
.btn-register {
  background-color: #fff;
  color: #000;
  border: 2px solid #000; /* Viền đen dày */
}

.btn-register:hover {
  background-color: #000;
  color: #fff;
}

/* --- Divider & Error --- */
.divider-text {
  text-align: center;
  margin: 20px 0;
  font-size: 11px;
  color: #999;
  position: relative;
}

/* Tạo đường kẻ ngang 2 bên chữ HOẶC */
.divider-text::before, .divider-text::after {
  content: "";
  position: absolute;
  top: 50%;
  width: 42%; /* Độ dài đường kẻ */
  height: 1px;
  background-color: #eee;
}
.divider-text::before { left: 0; }
.divider-text::after { right: 0; }

.error-msg {
  color: #d00; /* Đỏ đậm thay vì đỏ tươi */
  font-size: 12px;
  margin-bottom: 20px;
  text-align: center;
  font-weight: 600;
  text-transform: uppercase;
}
</style>
