<template>
  <div class="home-page">
    
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
         <div class="carousel-inner">
            <div class="carousel-item active" style="overflow: hidden;">
                    <img 
                        src="neon-gaming-background.webp"
                        alt="Banner Shop" 
                        class="d-block w-100 animated-bg" 
                        style="height: 500px; object-fit: cover;"
                    >

                <div class="carousel-caption d-none d-md-block text-start">
                    <div class="p-4 rounded" style="background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(5px); display: inline-block;">
                        <h1 class="display-3 fw-bold text-warning" style="text-shadow: 2px 2px 4px #000;">Shop</h1>
                        <p class="fs-4 text-white" style="text-shadow: 1px 1px 2px #000;">Giảm giá lên đến 50% cho các sản phẩm Apple & Samsung.</p>
                        <button @click="router.push('/products')" class="btn btn-primary btn-lg px-5 rounded-pill shadow">Mua ngay</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5">
      <div class="row text-center g-4">
        <div class="col-md-3">
          <div class="p-3 border rounded shadow-sm hover-up">
            <i class="bi bi-truck fs-1 text-primary"></i>
            <h5 class="mt-3">Giao hàng miễn phí</h5>
            <p class="text-muted small">Cho đơn hàng trên 5 triệu</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-3 border rounded shadow-sm hover-up">
            <i class="bi bi-shield-check fs-1 text-success"></i>
            <h5 class="mt-3">Bảo hành 12 tháng</h5>
            <p class="text-muted small">Chính hãng VN/A</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-3 border rounded shadow-sm hover-up">
            <i class="bi bi-headset fs-1 text-danger"></i>
            <h5 class="mt-3">Hỗ trợ 24/7</h5>
            <p class="text-muted small">Tư vấn kỹ thuật online</p>
          </div>
        </div>
        <div class="col-md-3">
          <div class="p-3 border rounded shadow-sm hover-up">
            <i class="bi bi-arrow-repeat fs-1 text-warning"></i>
            <h5 class="mt-3">Đổi trả 30 ngày</h5>
            <p class="text-muted small">Nếu có lỗi nhà sản xuất</p>
          </div>
        </div>
      </div>
    </div>

    <div class="container mb-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold border-start border-4 border-primary ps-3">Sản phẩm nổi bật</h2>
        <router-link to="/products" class="btn btn-outline-primary rounded-pill">Xem tất cả <i class="bi bi-arrow-right"></i></router-link>
      </div>
      <div class="row row-cols-1 row-cols-md-4 g-4" v-if="featuredProducts.length > 0">
            <div class="col" v-for="product in featuredProducts" :key="product.id">
            <div class="card h-100 shadow-sm border-0 product-card">
                
                <img 
                    :src="product.image || 'https://via.placeholder.com/300'" 
                    class="card-img-top p-3" 
                    style="height: 200px; object-fit: contain;"
                    alt="Product Image"
                >
                
                <div class="card-body d-flex flex-column">
                <h6 class="card-title fw-bold text-truncate">{{ product.name }}</h6>
                
                <div class="mt-auto">
                    <p class="text-danger fw-bold fs-5 mb-2">{{ formatPrice(product.price) }}</p>
                    
                    <button @click="router.push(`/product/${product.id}`)" class="btn btn-sm btn-primary w-100 rounded-pill">
                    <i class="bi bi-eye"></i> Xem chi tiết
                    </button>
                </div>
                </div>
                
                <span class="position-absolute top-0 start-0 badge bg-danger m-2">Hot</span>
            </div>
            </div>
        </div>
  
            <div v-else class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p>Đang tải sản phẩm...</p>
            </div>
                

        
    </div>

    <div class="bg-dark text-white py-5 text-center" style="background: linear-gradient(45deg, #1a1a1a, #4a4a4a);">
      <div class="container">
        <h2 class="mb-3">Đăng ký Ngay để nhận những ưu đãi hấp dẫn</h2>
        <button class="btn btn-warning btn-lg px-5 fw-bold" @click="router.push('/register')">Đăng ký ngay</button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router'; // 1. Phải import cái này mới dùng được router.push
import axios from 'axios';

const router = useRouter(); // 2. Khởi tạo router
const featuredProducts = ref([]);

// 3. Hàm format tiền (FIX LỖI QUAN TRỌNG)
const formatPrice = (value) => {
    if (!value) return '0 ₫';
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// 4. Gọi API lấy sản phẩm nổi bật
onMounted(async () => {
    try {
        // Gọi đúng cái Route vừa tạo ở Backend
        const response = await axios.get('/api/products/featured'); 
        featuredProducts.value = response.data;
    } catch (error) {
        console.error("Lỗi tải sản phẩm nổi bật:", error);
    }
});
</script>
<style scoped>
/* Hiệu ứng hover nhẹ lên các card */
.hover-up {
  transition: transform 0.3s ease;
}
.hover-up:hover {
  transform: translateY(-5px);
  background-color: #f8f9fa;
}

.product-card {
  transition: all 0.3s;
}
.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
@keyframes kenBurnsEffect {
  0% {
    transform: scale(1); /* Kích thước ban đầu */
  }
  100% {
    transform: scale(1.15); /* Phóng to lên 15% */
  }
}

/* Áp dụng vào ảnh */
.animated-bg {
  /*
    - animation-name: tên hiệu ứng vừa tạo ở trên
    - animation-duration: 20s (thời gian chạy 1 lượt là 20 giây -> rất chậm và mượt)
    - animation-timing-function: ease-in-out (chuyển động mềm mại ở đầu và cuối)
    - animation-iteration-count: infinite (lặp lại vô tận)
    - animation-direction: alternate (chạy đi rồi chạy ngược lại: zoom vào -> zoom ra)
  */
  animation: kenBurnsEffect 20s ease-in-out infinite alternate;

  /* Đảm bảo tâm điểm zoom là giữa ảnh */
  transform-origin: center center;
  /* Giúp hiệu ứng mượt hơn trên một số trình duyệt */
  will-change: transform;
}
</style>