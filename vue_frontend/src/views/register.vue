<template>
  <div class="register-page">
    <h2>Đăng ký</h2>

    <form @submit.prevent="submitRegister" class="register-form">
      <div class="form-group">
        <label>Username</label>
        <input type="text" v-model="form.username" placeholder="Nhập username" required />
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" v-model="form.email" placeholder="Nhập email" required />
      </div>

      <div class="form-group">
        <label>Mật khẩu</label>
        <input type="password" v-model="form.password" placeholder="Nhập mật khẩu" required />
      </div>

      <div class="form-group">
        <label>Xác nhận mật khẩu</label>
        <input type="password" v-model="form.password_confirmation" placeholder="Nhập lại mật khẩu" required />
      </div>

      <div class="form-group">
        <label>Họ tên</label>
        <input type="text" v-model="form.full_name" placeholder="Nhập họ tên" />
      </div>

      <div class="form-group">
        <label>Số điện thoại</label>
        <input type="text" v-model="form.phone" placeholder="10 chữ số" />
      </div>

      <div class="form-group">
        <label>Địa chỉ</label>
        <input type="text" v-model="form.address" placeholder="Nhập địa chỉ" />
      </div>

      <div class="form-group">
        <label>Avatar</label>
        <input type="file" @change="onFileChange" />
      </div>

      <button type="submit" class="btn-register">Đăng ký</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = ref({
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  full_name: '',
  phone: '',
  address: '',
  avatar: null
})

const errors = ref({})

function onFileChange(e) {
  form.value.avatar = e.target.files[0]
}

async function submitRegister() {
  errors.value = {} // reset lỗi

  // ======== Frontend validation =========
  if (!form.value.username) {
    errors.value.username = 'Username không được để trống'
  }

  if (!form.value.email || !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(form.value.email)) {
  errors.value.email = 'Email không hợp lệ'
  }

  if (!form.value.password) {
    errors.value.password = 'Password không được để trống'
  } else if (form.value.password.length < 8) {
    errors.value.password = 'Password phải có ít nhất 8 ký tự'
  }

  if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = 'Password xác nhận không khớp'
  }

  if (form.value.phone && !/^\d{10}$/.test(form.value.phone)) {
    errors.value.phone = 'Phone phải gồm 10 chữ số'
  }

  if (Object.keys(errors.value).length > 0) {
    alert('Có lỗi trong form, kiểm tra lại!')
    return
  }

  // ======== Tạo FormData ========
  const formData = new FormData()
  Object.keys(form.value).forEach(key => {
    if (form.value[key] !== null) {
      formData.append(key, form.value[key])
    }
  })

  try {
    const response = await axios.post('/register', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    alert('Đăng ký thành công!')

    // Reset form
    Object.keys(form.value).forEach(key => form.value[key] = '')

    // ===== Chuyển hướng về trang login =====
    router.push('/login')

  } catch (err) {
    if (err.response?.data?.errors) {
      errors.value = err.response.data.errors
      console.log('Backend errors:', errors.value)
      alert('Username hoặc email đã tồn tại.')
    } else {
      console.error(err)
      alert('Có lỗi xảy ra, thử lại sau.')
    }
  }
}
</script>


<style scoped>
.register-page {
  max-width: 480px;
  margin: 100px auto;
  padding: 30px 36px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.08);
  font-family: "Inter", sans-serif;
  color: #111;
}

.register-page h2 {
  font-size: 26px;
  font-weight: 700;
  margin-bottom: 24px;
  text-align: center;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 6px;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group input[type="file"] {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  background: #f9fafb;
  transition: 0.25s;
}

.form-group input:focus {
  outline: none;
  border-color: #111;
  background: #fff;
}

.btn-register {
  background: #111;
  color: #fff;
  font-weight: 600;
  font-size: 15px;
  padding: 12px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.25s;
}

.btn-register:hover {
  background: #333;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>
