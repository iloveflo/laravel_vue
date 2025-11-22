<template>
  <div class="register-container">
    <div class="register-box">
      <h2>Đăng ký</h2>
      
      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>

      <div v-if="Object.keys(validationErrors).length > 0" class="validation-errors">
        <ul>
          <li v-for="(errors, field) in validationErrors" :key="field">
            <strong>{{ field }}:</strong> {{ errors[0] }}
          </li>
        </ul>
      </div>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label for="email">Email *</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            placeholder="Nhập email"
            required
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="full_name">Họ và tên *</label>
          <input
            id="full_name"
            type="text"
            v-model="form.full_name"
            placeholder="Nhập họ và tên"
            required
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="phone">Số điện thoại *</label>
          <input
            id="phone"
            type="tel"
            v-model="form.phone"
            placeholder="Nhập số điện thoại"
            required
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label for="password">Mật khẩu *</label>
          <input
            id="password"
            type="password"
            v-model="form.password"
            placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"
            required
            :disabled="loading"
            minlength="6"
          />
        </div>

        <div class="form-group">
          <label for="password_confirmation">Xác nhận mật khẩu *</label>
          <input
            id="password_confirmation"
            type="password"
            v-model="form.password_confirmation"
            placeholder="Nhập lại mật khẩu"
            required
            :disabled="loading"
            minlength="6"
          />
        </div>

        <button type="submit" :disabled="loading" class="register-button">
          <span v-if="loading">Đang đăng ký...</span>
          <span v-else>Đăng ký</span>
        </button>
      </form>

      <div class="login-link">
        <p>Đã có tài khoản? <router-link to="/login">Đăng nhập ngay</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import authService from '../services/auth.js'

const router = useRouter()

const form = ref({
  email: '',
  full_name: '',
  phone: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const errorMessage = ref('')
const validationErrors = ref({})

onMounted(() => {
  // Nếu đã đăng nhập, redirect theo role
  if (authService.isAuthenticated()) {
    const user = authService.getCurrentUser()
    if (user && user.role === 'admin') {
      router.push('/admin')
    } else {
      router.push('/')
    }
  }
})

const handleRegister = async () => {
  errorMessage.value = ''
  validationErrors.value = {}
  loading.value = true

  try {
    // Kiểm tra mật khẩu khớp
    if (form.value.password !== form.value.password_confirmation) {
      errorMessage.value = 'Mật khẩu xác nhận không khớp'
      loading.value = false
      return
    }

    const result = await authService.register({
      email: form.value.email,
      full_name: form.value.full_name,
      phone: form.value.phone || null,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    })
    
    if (result.success) {
      // Đăng ký thành công, chuyển đến trang chủ (user)
      router.push('/')
    } else {
      if (result.errors) {
        validationErrors.value = result.errors
      } else {
        errorMessage.value = result.message || 'Đăng ký thất bại'
      }
    }
  } catch (error) {
    errorMessage.value = 'Có lỗi xảy ra khi đăng ký'
    console.error('Register error:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f0f0;
  padding: 20px;
}

.register-box {
  background: white;
  padding: 30px;
  border: 1px solid #ddd;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.register-box h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #333;
  font-size: 24px;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color: #333;
}

.form-group input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  font-size: 14px;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  border-color: #666;
}

.form-group input:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.register-button {
  width: 100%;
  padding: 10px;
  background: #333;
  color: white;
  border: none;
  font-size: 16px;
  cursor: pointer;
  margin-top: 10px;
}

.register-button:hover:not(:disabled) {
  background: #555;
}

.register-button:disabled {
  background: #999;
  cursor: not-allowed;
}

.error-message {
  background-color: #fee;
  color: #c33;
  padding: 10px;
  margin-bottom: 15px;
  text-align: center;
}

.validation-errors {
  background-color: #fee;
  color: #c33;
  padding: 10px;
  margin-bottom: 15px;
}

.validation-errors ul {
  margin: 0;
  padding-left: 20px;
}

.validation-errors li {
  margin-bottom: 5px;
}

.login-link {
  margin-top: 15px;
  text-align: center;
  color: #666;
}

.login-link a {
  color: #0066cc;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}
</style>

