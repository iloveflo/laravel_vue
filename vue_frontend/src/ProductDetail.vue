<script setup>
import { ref } from 'vue'
import { useCartStore } from '@/stores/cart'

const props = defineProps(['product']) // Nhận object sản phẩm từ cha
const cartStore = useCartStore()

const selectedSize = ref('M')
const selectedColor = ref('Red')

const handleAddToCart = () => {
    cartStore.addToCart(props.product, selectedSize.value, selectedColor.value)
    alert('Đã thêm vào giỏ hàng!')
}
</script>

<template>
    <div class="product-card">
        <h3>{{ product.name }}</h3>
        <p>Giá: {{ product.sale_price ?? product.price }}</p>
        
        <select v-model="selectedSize">
            <option value="S">S</option>
            <option value="M">M</option>
            <option value="L">L</option>
        </select>
        
        <button @click="handleAddToCart">Thêm vào giỏ</button>
    </div>
</template>