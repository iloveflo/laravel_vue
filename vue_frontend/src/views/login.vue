<template>
  <div class="login-container">
    <h2>Admin Login</h2>
    <form @submit.prevent="handleLogin">
      <div>
        <label>Email</label>
        <input type="email" v-model="email" required />
      </div>
      <div>
        <label>Password</label>
        <input type="password" v-model="password" required />
      </div>
      <button type="submit">Login</button>
      <p v-if="errorMessage" style="color:red">{{ errorMessage }}</p>
    </form>
  </div>
</template>

<script>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

export default {
  setup() {
    const router = useRouter()
    const email = ref('')
    const password = ref('')
    const errorMessage = ref('')

    const handleLogin = async () => {
      try {
        const response = await axios.post('/login', {  // dùng relative path
          email: email.value,
          password: password.value,
        })

        localStorage.setItem('token', response.data.token)
        router.push('/admin')
      } catch (error) {
        if (error.response) {
          errorMessage.value = error.response.data.message
        } else {
          errorMessage.value = 'Lỗi kết nối server'
        }
      }
    }

    return { email, password, handleLogin, errorMessage }
  }
}
</script>


<style scoped>
.login-container {
  max-width: 400px;
  margin: auto;
  padding: 2rem;
  border: 1px solid #ccc;
  border-radius: 8px;
}
</style>
