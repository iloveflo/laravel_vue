<script setup>
import { reactive } from 'vue'
import { useCartStore } from '@/stores/cart'

const cartStore = useCartStore()

// Form thông tin khách hàng
const customer = reactive({
    full_name: '',
    email: '',
    phone: '',
    address: ''
})

const handleCheckout = async () => {
    if(cartStore.items.length === 0) return alert('Giỏ hàng trống!')
    
    try {
        const res = await cartStore.checkout(customer)
        alert('Đặt hàng thành công! Mã đơn: ' + res.order_code)
    } catch (err) {
        alert('Lỗi: ' + (err.message || 'Có lỗi xảy ra'))
    }
}
</script>

<template>
    <div class="cart-page">
        <h2>Giỏ hàng của bạn</h2>
        
        <table v-if="cartStore.items.length > 0">
            <tr v-for="item in cartStore.items" :key="item.uniqueId">
                <td>{{ item.name }} (Size: {{ item.size }})</td>
                <td>
                    <button @click="item.quantity--">-</button>
                    {{ item.quantity }}
                    <button @click="item.quantity++">+</button>
                </td>
                <td>{{ item.price * item.quantity }}</td>
                <td><button @click="cartStore.removeFromCart(item.uniqueId)">Xóa</button></td>
            </tr>
        </table>
        <p v-else>Chưa có sản phẩm nào.</p>
        
        <h3>Tổng tiền: {{ cartStore.totalAmount }}</h3>

        <div class="checkout-form">
            <input v-model="customer.full_name" placeholder="Họ tên" />
            <input v-model="customer.email" placeholder="Email" />
            <input v-model="customer.phone" placeholder="Số điện thoại" />
            <textarea v-model="customer.address" placeholder="Địa chỉ giao hàng"></textarea>
            
            <button @click="handleCheckout">XÁC NHẬN ĐẶT HÀNG</button>
        </div>
    </div>
</template>