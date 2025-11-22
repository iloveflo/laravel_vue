import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useCartStore = defineStore('cart', () => {
    const items = ref([]) 

    // Getter: Tính tổng tiền
    const totalAmount = computed(() => {
        return items.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    })

    // Action: Thêm vào giỏ
    // Vì sản phẩm có Size và Color, ta cần tạo một key duy nhất
    const addToCart = (product, size, color) => {
        const uniqueId = `${product.id}-${size}-${color}`
        
        const existingItem = items.value.find(item => item.uniqueId === uniqueId)

        if (existingItem) {
            existingItem.quantity++
        } else {
            items.value.push({
                uniqueId: uniqueId,
                id: product.id,
                name: product.name,
                price: product.sale_price || product.price, // Lấy giá hiển thị
                image: product.image, // URL ảnh
                size: size,
                color: color,
                quantity: 1
            })
        }
    }

    const removeFromCart = (uniqueId) => {
        items.value = items.value.filter(item => item.uniqueId !== uniqueId)
    }
    
    const clearCart = () => {
        items.value = []
    }

    // Action: Gửi đơn hàng lên Laravel
    const checkout = async (customerInfo) => {
        try {
            const response = await axios.post('http://localhost:8000/api/checkout', {
                customer: customerInfo,
                cart_items: items.value
            })
            // Nếu thành công thì xóa giỏ
            clearCart()
            return response.data
        } catch (error) {
            throw error.response.data
        }
    }

    return { items, totalAmount, addToCart, removeFromCart, checkout }
}, {
    persist: true // Tự động lưu vào localStorage
})