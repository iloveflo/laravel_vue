<template>
  <div class="forgot-container">
    <div class="forgot-box">
      <h2>Quên mật khẩu</h2>
      
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleForgotPassword" v-if="!successMessage">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            placeholder="Nhập email của bạn"
            required
            :disabled="loading"
          />
        </div>

        <button type="submit" :disabled="loading" class="submit-button">
          <span v-if="loading">Đang gửi...</span>
          <span v-else>Gửi link đặt lại mật khẩu</span>
        </button>
      </form>

      <div class="login-link">
        <p><router-link to="/login">Quay lại đăng nhập</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = ref({
  email: ''
})

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleForgotPassword = async () => {
  errorMessage.value = ''
  successMessage.value = ''
  loading.value = true

  try {
    const response = await fetch('http://localhost:8000/api/auth/forgot-password', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({ email: form.value.email }),
    })

    const data = await response.json()
    
    if (data.success) {
      successMessage.value = data.message || 'Chúng tôi đã gửi link đặt lại mật khẩu đến email của bạn. Vui lòng kiểm tra email.'
    } else {
      errorMessage.value = data.message || 'Có lỗi xảy ra'
    }
  } catch (error) {
    errorMessage.value = 'Có lỗi xảy ra khi gửi yêu cầu'
    console.error('Forgot password error:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.forgot-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f0f0f0;
  padding: 20px;
}

.forgot-box {
  background: white;
  padding: 30px;
  border: 1px solid #ddd;
  width: 100%;
  max-width: 400px;
}

.forgot-box h2 {
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

.submit-button {
  width: 100%;
  padding: 10px;
  background: #333;
  color: white;
  border: none;
  font-size: 16px;
  cursor: pointer;
  margin-top: 10px;
}

.submit-button:hover:not(:disabled) {
  background: #555;
}

.submit-button:disabled {
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


