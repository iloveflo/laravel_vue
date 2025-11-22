<template>
  <div class="reset-container">
    <div class="reset-box">
      <h2>Đặt lại mật khẩu</h2>
      
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

      <form @submit.prevent="handleResetPassword" v-if="!successMessage">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            placeholder="Nhập email"
            required
            :disabled="loading || true"
            readonly
          />
        </div>

        <div class="form-group">
          <label for="password">Mật khẩu mới *</label>
          <input
            id="password"
            type="password"
            v-model="form.password"
            placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)"
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
            placeholder="Nhập lại mật khẩu mới"
            required
            :disabled="loading"
            minlength="6"
          />
        </div>

        <button type="submit" :disabled="loading" class="reset-button">
          <span v-if="loading">Đang đặt lại...</span>
          <span v-else>Đặt lại mật khẩu</span>
        </button>
      </form>

      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
        <div style="margin-top: 15px;">
          <router-link to="/login" class="login-link-btn">Đăng nhập ngay</router-link>
        </div>
      </div>

      <div class="login-link" v-if="!successMessage">
        <p><router-link to="/login">Quay lại đăng nhập</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()

const form = ref({
  email: '',
  token: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const validationErrors = ref({})

onMounted(() => {
  // Lấy token và email từ query params
  form.value.token = route.query.token || ''
  form.value.email = route.query.email || ''
  
  if (!form.value.token || !form.value.email) {
    errorMessage.value = 'Link không hợp lệ hoặc đã hết hạn'
  }
})

const handleResetPassword = async () => {
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

    const response = await fetch('http://localhost:8000/api/auth/reset-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({
        email: form.value.email,
        token: form.value.token,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation
      }),
    })

    const data = await response.json()
    
    if (data.success) {
      successMessage.value = data.message || 'Đặt lại mật khẩu thành công!'
    } else {
      if (data.errors) {
        validationErrors.value = data.errors
      } else {
        errorMessage.value = data.message || 'Đặt lại mật khẩu thất bại'
      }
    }
  } catch (error) {
    errorMessage.value = 'Có lỗi xảy ra khi đặt lại mật khẩu'
    console.error('Reset password error:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.reset-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f0f0;
  padding: 20px;
}

.reset-box {
  background: white;
  padding: 30px;
  border: 1px solid #ddd;
  width: 100%;
  max-width: 400px;
}

.reset-box h2 {
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

.form-group input[readonly] {
  background-color: #f5f5f5;
}

.reset-button {
  width: 100%;
  padding: 10px;
  background: #333;
  color: white;
  border: none;
  font-size: 16px;
  cursor: pointer;
  margin-top: 10px;
}

.reset-button:hover:not(:disabled) {
  background: #555;
}

.reset-button:disabled {
  background: #999;
  cursor: not-allowed;
}

.success-message {
  background-color: #dfd;
  color: #3a3;
  padding: 10px;
  margin-bottom: 15px;
  text-align: center;
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

.login-link-btn {
  display: inline-block;
  padding: 8px 20px;
  background: #333;
  color: white;
  text-decoration: none;
  border-radius: 4px;
}

.login-link-btn:hover {
  background: #555;
}
</style>


