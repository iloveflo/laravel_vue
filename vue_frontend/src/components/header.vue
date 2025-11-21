<template>
<header class="header">
<div class="left">My Website</div>
<div class="right">
<router-link to="/login">Đăng nhập</router-link>
</div>
</header>
</template>


<script setup>
</script>


<style scoped>
.header {
display: flex;
justify-content: space-between;
padding: 10px;
background: #eee;
}
</style>