<template>
<<<<<<< HEAD
  <div class="search-page">
    <h1>Kết quả tìm kiếm cho: "{{ searchQuery }}"</h1>
    <div class="products-grid">
      <!-- Hiển thị sản phẩm ở đây -->
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const searchQuery = ref(route.query.q || '')

onMounted(() => {
  // Gọi API tìm kiếm với searchQuery.value
  console.log('Searching for:', searchQuery.value)
})
</script>
=======
  <div class="page-container">
    <h1>Search</h1>
  </div>
</template>
>>>>>>> 8758146 (Push project to huy branch)
