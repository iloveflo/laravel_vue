<template>
  <div class="login-container">
    <div class="login-box">
      <h2>Đăng nhập</h2>
      
      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email</label>
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
          <label for="password">Mật khẩu</label>
          <input
            id="password"
            type="password"
            v-model="form.password"
            placeholder="Nhập mật khẩu"
            required
            :disabled="loading"
          />
        </div>

        <button type="submit" :disabled="loading" class="login-button">
          <span v-if="loading">Đang đăng nhập...</span>
          <span v-else>Đăng nhập</span>
        </button>
      </form>

      <div class="register-link">
        <p>Chưa có tài khoản? <router-link to="/register">Đăng ký ngay</router-link></p>
        <p style="margin-top: 10px;"><router-link to="/forgot-password">Quên mật khẩu?</router-link></p>
        <p style="margin-top: 10px; font-size: 12px; color: #999;">Admin vui lòng liên hệ để được cấp tài khoản</p>
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
  password: ''
})

const loading = ref(false)
const errorMessage = ref('')

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

const handleLogin = async () => {
  errorMessage.value = ''
  loading.value = true

  try {
    const result = await authService.login(form.value.email, form.value.password)
    
    if (result.success) {
      // Đăng nhập thành công, redirect theo role
      if (result.user && result.user.role === 'admin') {
        // Admin → chuyển đến trang admin
        const redirectPath = router.currentRoute.value.query.redirect || '/admin'
        router.push(redirectPath)
      } else {
        // User → chuyển đến trang chủ
        router.push('/')
      }
    } else {
      errorMessage.value = result.message || 'Đăng nhập thất bại'
    }
  } catch (error) {
    errorMessage.value = 'Có lỗi xảy ra khi đăng nhập'
    console.error('Login error:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f0f0;
  padding: 20px;
}

.login-box {
  background: white;
  padding: 30px;
  border: 1px solid #ddd;
  width: 100%;
  max-width: 400px;
}

.login-box h2 {
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

.login-button {
  width: 100%;
  padding: 10px;
  background: #333;
  color: white;
  border: none;
  font-size: 16px;
  cursor: pointer;
  margin-top: 10px;
}

.login-button:hover:not(:disabled) {
  background: #555;
}

.login-button:disabled {
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

.register-link {
  margin-top: 15px;
  text-align: center;
  color: #666;
}

.register-link a {
  color: #0066cc;
  text-decoration: none;
}

.register-link a:hover {
  text-decoration: underline;
}
</style>