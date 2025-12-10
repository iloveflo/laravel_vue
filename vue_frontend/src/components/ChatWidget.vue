<template>
    <div class="chat-widget-container">
        <button v-if="!isOpen" @click="toggleChat" class="chat-toggle-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="gemini-icon-btn">
                <path d="M12 2L14.8 9.2L22 12L14.8 14.8L12 22L9.2 14.8L2 12L9.2 9.2L12 2Z"
                    fill="url(#gemini-gradient-btn)" />
                <defs>
                    <linearGradient id="gemini-gradient-btn" x1="0" y1="0" x2="24" y2="24"
                        gradientUnits="userSpaceOnUse">
                        <stop stop-color="#E2E8F0" />
                        <stop offset="1" stop-color="#FFFFFF" />
                    </linearGradient>
                </defs>
            </svg>
        </button>

        <div v-else class="chat-box">
            <div class="chat-header">
                <div class="header-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"
                            fill="url(#gemini-gradient-header)" />
                        <defs>
                            <linearGradient id="gemini-gradient-header" x1="0" y1="0" x2="24" y2="24"
                                gradientUnits="userSpaceOnUse">
                                <stop stop-color="#FFF" />
                                <stop offset="1" stop-color="#D1E7DD" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <span>Gemini Assistant</span>
                </div>
                <button @click="toggleChat" class="close-btn">✖️</button>
            </div>

            <div class="chat-messages" ref="messagesContainer">
                <div v-for="(msg, index) in messages" :key="index" :class="['message-container', msg.sender]">

                    <div v-if="msg.sender === 'bot'" class="bot-avatar">
                        <svg viewBox="0 0 24 24" width="100%" height="100%">
                            <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"
                                fill="url(#gemini-gradient-avatar)" />
                            <defs>
                                <linearGradient id="gemini-gradient-avatar" x1="0" y1="0" x2="24" y2="24"
                                    gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#4E89FF" />
                                    <stop offset="1" stop-color="#A573FF" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>

                    <div class="message-content">
                        <div class="message-bubble">
                            {{ msg.text }}
                        </div>

                        <div v-if="msg.products && msg.products.length > 0" class="product-carousel">
                            <div v-for="product in msg.products" :key="product.id" class="product-card">
                                <img :src="product.main_image_url || 'https://via.placeholder.com/150'"
                                    alt="Product Image" class="product-img" />

                                <div class="product-info">
                                    <h5 class="product-name">{{ product.name }}</h5>
                                    <p class="product-price">{{ formatCurrency(product.sale_price || product.price) }}
                                    </p>

                                    <button @click="goToProduct(product.slug)" class="view-detail-btn">
                                        Xem chi tiết
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div v-if="isLoading" class="message-container bot typing">
                    <div class="bot-avatar">
                        <svg viewBox="0 0 24 24" width="100%" height="100%">
                            <path d="M12 2L14.5 9.5L22 12L14.5 14.5L12 22L9.5 14.5L2 12L9.5 9.5L12 2Z"
                                fill="url(#gemini-gradient-avatar)" />
                        </svg>
                    </div>
                    <div class="message-bubble"><span>.</span><span>.</span><span>.</span></div>
                </div>
            </div>

            <div class="chat-input">
                <input v-model="userMessage" @keyup.enter="sendMessage" placeholder="Hỏi AI về sản phẩm..."
                    :disabled="isLoading" />
                <button @click="sendMessage" :disabled="isLoading || !userMessage.trim()">➤</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, nextTick,onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; // Import Router để chuyển trang

const router = useRouter(); // Khởi tạo router
const isOpen = ref(false);
const userMessage = ref('');
// Thêm trường 'products' vào cấu trúc tin nhắn
const messages = ref([
    { sender: 'bot', text: 'Xin chào! Tôi là AI Gemini, tôi có thể giúp gì cho bạn?', products: [] }
]);

// --- PHẦN MỚI THÊM: LOAD LỊCH SỬ ---
onMounted(async () => {
  const currentSessionId = localStorage.getItem('chat_session_id');
  
  if (currentSessionId) {
    try {
      // Gọi API lấy lịch sử (Thay URL bằng đường dẫn thật của bạn)
      const response = await axios.get('/chat/history', {
        params: { session_id: currentSessionId }
      });

      // Nếu có lịch sử thì gán vào biến messages
      if (response.data && response.data.length > 0) {
        messages.value = [
            { sender: 'bot', text: 'Xin chào! Tôi là AI Gemini, tôi có thể giúp gì cho bạn?', products: [] },
            ...response.data
        ];
      }
    } catch (error) {
      console.error("Lỗi tải lịch sử chat:", error);
    }
  }
});
// ------------------------------------

const isLoading = ref(false);
const messagesContainer = ref(null);

const toggleChat = () => {
    isOpen.value = !isOpen.value;
    scrollToBottom();
};

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// Hàm chuyển hướng khi bấm nút Xem chi tiết
const goToProduct = (slug) => {
    isOpen.value = false;
    router.push(`/product/${slug}`);
};

const sendMessage = async () => {
    if (!userMessage.value.trim()) return;

    const text = userMessage.value;
    messages.value.push({ sender: 'user', text: text, products: [] });
    userMessage.value = '';
    isLoading.value = true;
    scrollToBottom();

    const currentSessionId = localStorage.getItem('chat_session_id');

    try {
        const response = await axios.post('/chat', {
            message: text,
            session_id: currentSessionId
        });

        messages.value.push({
            sender: 'bot',
            text: response.data.reply,
            products: response.data.products || [] // Gán sản phẩm vào đây
        });

        if (response.data.session_id) {
            localStorage.setItem('chat_session_id', response.data.session_id);
        }

    } catch (error) {
        console.error(error);
        messages.value.push({ sender: 'bot', text: 'Xin lỗi, tôi đang mất kết nối.', products: [] });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};
</script>

<style scoped>
/* GIỮ NGUYÊN CSS CŨ CỦA BẠN VÀ THÊM CÁC CLASS SAU: */

.chat-widget-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: 'Segoe UI', sans-serif;
}

.chat-toggle-btn {
    width: 65px;
    height: 65px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4E89FF 0%, #A573FF 100%);
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(78, 137, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}

.gemini-icon-btn {
    width: 35px;
    height: 35px;
    animation: pulse 2s infinite;
}

.chat-box {
    width: 340px;
    height: 480px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.chat-header {
    background: linear-gradient(90deg, #4E89FF 0%, #A573FF 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
}

.chat-messages {
    flex: 1;
    padding: 20px;
    background: #f8faff;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.bot-avatar {
    width: 32px;
    height: 32px;
    background: white;
    border-radius: 50%;
    padding: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
    flex-shrink: 0;
}

.chat-input {
    padding: 15px;
    border-top: 1px solid #eee;
    background: white;
    display: flex;
    gap: 10px;
}

.chat-input input {
    flex: 1;
    padding: 12px 15px;
    border: 1px solid #e1e4e8;
    border-radius: 25px;
    outline: none;
}

.chat-input input:focus {
    border-color: #4E89FF;
}

.chat-input button {
    background: linear-gradient(135deg, #4E89FF 0%, #A573FF 100%);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
}

@keyframes pulse {
    0% {
        opacity: 0.8;
        transform: scale(1);
    }

    50% {
        opacity: 1;
        transform: scale(1.05);
    }

    100% {
        opacity: 0.8;
        transform: scale(1);
    }
}

@keyframes blink {
    0% {
        opacity: 0.2;
    }

    20% {
        opacity: 1;
    }

    100% {
        opacity: 0.2;
    }
}

.typing span {
    animation: blink 1.4s infinite both;
    margin: 0 2px;
    color: #666;
    font-weight: bold;
}

.typing span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing span:nth-child(3) {
    animation-delay: 0.4s;
}

/* --- CSS MỚI CHO BỐ CỤC TIN NHẮN VÀ CARD --- */

.message-container {
    display: flex;
    gap: 10px;
    max-width: 100%;
}

.message-container.user {
    flex-direction: row-reverse;
    align-self: flex-end;
}

.message-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-width: 85%;
}

.message-bubble {
    padding: 12px 16px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.5;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    background: white;
    color: #1f2937;
    border: 1px solid #e5e7eb;
    border-bottom-left-radius: 4px;
}

.message-container.user .message-bubble {
    background: #4E89FF;
    color: white;
    border-bottom-right-radius: 4px;
    border: none;
}

/* --- PRODUCT CAROUSEL (Thanh trượt ngang sản phẩm) --- */
.product-carousel {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding-bottom: 5px;
    /* Để chừa chỗ cho thanh cuộn */
    width: 100%;
    scrollbar-width: thin;
    /* Firefox */
    scrollbar-color: #ccc transparent;
}

.product-carousel::-webkit-scrollbar {
    height: 4px;
}

.product-carousel::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}

/* --- PRODUCT CARD STYLE --- */
.product-card {
    min-width: 140px;
    width: 140px;
    background: white;
    border-radius: 10px;
    border: 1px solid #eee;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    display: flex;
    flex-direction: column;
}

.product-img {
    width: 100%;
    height: 100px;
    object-fit: cover;
}

.product-info {
    padding: 8px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.product-name {
    font-size: 12px;
    font-weight: bold;
    margin: 0 0 4px 0;
    color: #333;
    /* Cắt ngắn tên nếu quá dài (2 dòng) */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-price {
    font-size: 12px;
    color: #d0021b;
    /* Màu đỏ giá */
    font-weight: bold;
    margin-bottom: 8px;
}

.view-detail-btn {
    margin-top: auto;
    /* Đẩy nút xuống dưới cùng */
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 10px;
    cursor: pointer;
    transition: background 0.2s;
}

.view-detail-btn:hover {
    background: #0056b3;
}
</style>