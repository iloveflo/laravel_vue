<template>
<div class="admin-container">
<aside class="sidebar">
<router-link to="/admin/quan-ly-nguoi-dung">Quản lý người dùng</router-link>
<router-link to="/admin/quan-ly-san-pham">Quản lý sản phẩm</router-link>
<router-link to="/admin/quan-ly-gio-hang">Quản lý giỏ hàng</router-link>
<router-link to="/admin/thong-ke-bao-cao">Thống kê báo cáo</router-link>
</aside>


<main class="main-content">
<router-view />
</main>
</div>
</template>


<script setup>
</script>


<style scoped>
.admin-container {
display: flex;
height: 100vh;
}
.sidebar {
width: 220px;
background: #ddd;
padding: 20px;
display: flex;
flex-direction: column;
gap: 15px;
}
.main-content {
flex: 1;
padding: 20px;
background: #fff;
}
</style>