<script setup>
import { ref } from 'vue'

const props = defineProps(['products', 'categories','currentPage', 'lastPage', 'cartItems', 'product', 'isloading'])
const emit = defineEmits(['changePage', 'addToCart', 'search']) 

// Khai báo biến (Tên biến đặt sao cũng được, quan trọng là lúc emit)
const keyword = ref('') 
const minPrice = ref('')
const maxPrice = ref('')
const category_id=ref('')


const isLimitReached = (p) => {
  if (!props.cartItems || !p) return false;
  
  // Tìm xem sản phẩm 'p' này có trong giỏ hàng chưa
  const cartItem = props.cartItems.find(item => 
    (item.product_id === p.id) || (item.product?.id === p.id)
  );
    return p.stock <=0;
  // So sánh số lượng trong giỏ với số lượng tồn kho của chính nó
  //return cartItem ? cartItem.quantity >= p.stock : false;
}
// Xử lý khi bấm nút Lọc
const handleSearch = () => {
    console.log("1. Đã bấm nút Lọc, dữ liệu là:", { 
        keyword: keyword.value, 
        category_id: category_id.value,
        min_price: minPrice.value, 
        max_price: maxPrice.value 
    });

    emit('search', {
        keyword: keyword.value,
        category_id: category_id.value,
        min_price: minPrice.value,
        max_price: maxPrice.value
    })
}

// Reset form
const resetSearch = () => {
    keyword.value = ''
    minPrice.value = ''
    maxPrice.value = ''
    category_id.value=''
    handleSearch() 
}
</script>

<template>
  <div>
    <div class="card mb-4 shadow-sm bg-light border-0">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <input v-model="keyword" type="text" class="form-control" placeholder="🔍 Tìm tên sản phẩm..." @keyup.enter="handleSearch">
                </div>
              
                <div class="col-md-2 ">
                    <input v-model="minPrice" type="number" class="form-control" placeholder="Giá từ...">
                </div>
                <div class="col-md-2 ">
                    <input v-model="maxPrice" type="number" class="form-control" placeholder="Giá đến...">
                    
                </div>
                <div class="col-md-2 ">
                    <select v-model="category_id" class="form-select" @change="handleSearch">
                        <option value="">📂 Tất cả danh mục</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                            {{ cat.name }}
                        </option>
                    </select>
                </div>
                <div class="col-md-1 d-grid gap-2 ">
                    <button class="btn btn-primary w-10 fw-bold " @click="handleSearch">Lọc</button>
                   
                 </div>
                 <div class="col-md-1 d-grid gap-2 ">
                     <button class="btn btn-outline-danger w-10 " @click="resetSearch">Reset</button>
                </div>
            </div>

            
        </div>
    </div>
    

    <div v-if="products.length > 0" class="row">
      <div class="col-md-3 mb-4" v-for="product in products" :key="product.id">
        <div class="card h-100 shadow-sm hover-shadow">
            <router-link :to="{ name: 'ProductDetail', params: { id: product.id } }">
                <img 
                    :src="product.image_url || 'https://placehold.co/300'" 
                    class="card-img-top p-3" 
                    style="height: 200px; object-fit: contain; cursor: pointer" 
                    alt="...">
            </router-link>
            
            <div class="card-body d-flex flex-column">
               <router-link :to="{ name: 'ProductDetail', params: { id: product.id }}"
                class="text-decoration-none  mb-2 flex-grow-1">
                    <h6 class="card-title text-truncate">{{ product.name }}</h6>
                
                <p class="card-text text-danger fw-bold fs-5">{{ Number(product.price).toLocaleString() }} đ</p>
                </router-link>
                <button 
                    class="btn w-100" 
                    :class="product.stock > 0 && !isLimitReached(product) ? 'btn-outline-primary' : 'btn-outline-secondary'"
                    :disabled="product.stock <= 0 || isLimitReached(product)"
                    @click="$emit('addToCart', product)"
                >
                    <i class="bi" :class="product.stock > 0 && !isLimitReached(product) ? 'bi-cart-plus' : 'bi-dash-circle'"></i>
                    
                    <span v-if="product.stock <= 0">Hết hàng</span>
                    <span v-else-if="isLimitReached(product )">Đã đạt giới hạn kho</span>
                    <span v-else>Thêm vào giỏ</span>
                </button>
                <div class="text-center mt-1" style="min-height: 20px;">
                    <small v-if="isLimitReached(product)" class="text-danger fw-bold">
                    Kho chỉ còn {{ product.stock }} sản phẩm
                    </small>
                    <small v-else-if="product.stock > 0" class="text-muted">
                    Tồn kho: {{ product.stock }}
                    </small>
                </div>
            </div>
        </div>
      </div>
    </div>
    
    <div v-else class="text-center py-5">
        <h4 class="text-muted">😢 Không tìm thấy sản phẩm nào!</h4>
        <button class="btn btn-link" @click="resetSearch">Xem tất cả</button>
    </div>

    <div class="d-flex justify-content-center mt-4" v-if="lastPage > 1">
        <button class="btn btn-outline-secondary me-2" :disabled="currentPage === 1" @click="$emit('changePage', currentPage - 1)">« Trước</button>
        <span class="align-self-center fw-bold">Trang {{ currentPage }} / {{ lastPage }}</span>
        <button class="btn btn-outline-secondary ms-2" :disabled="currentPage === lastPage" @click="$emit('changePage', currentPage + 1)">Sau »</button>
    </div>
  </div>
</template>