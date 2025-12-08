<template>
  <section class="hero-section">
    <div class="hero-content">
      <h1 class="hero-title">Phong Cách Mới 2025</h1>
      <p class="hero-subtitle">Khám phá bộ sưu tập thời trang hiện đại, tối giản và tinh tế dành riêng cho bạn.</p>
      <button class="btn-primary">Mua Sắm Ngay</button>
    </div>
  </section>
</template>

<script setup>
</script>

<style scoped>
.hero-section {
  position: relative;
  width: 100%;
  height: 600px;
  background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url('https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80');
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: white;
  margin-bottom: 40px;
}

.hero-content {
  max-width: 800px;
  padding: 20px;
  animation: fadeIn 1s ease-out;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 700;
  margin-bottom: 20px;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.hero-subtitle {
  font-size: 1.2rem;
  margin-bottom: 30px;
  font-weight: 300;
  opacity: 0.9;
}

.btn-primary {
  padding: 15px 40px;
  font-size: 1rem;
  font-weight: 600;
  color: #fff;
  background-color: #2c3e50;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.btn-primary:hover {
  background-color: #34495e;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
  .hero-section { height: 400px; }
  .hero-title { font-size: 2.5rem; }
  .hero-subtitle { font-size: 1rem; }
}
</style>
