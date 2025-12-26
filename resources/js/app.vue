<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useRouter, useRoute } from 'vue-router'
import { useToast } from "vue-toastification";
//note
const toast = useToast();
// --- 1. STATE QUẢN LÝ ---
const router = useRouter()
const route = useRoute()
// const currentView = ref('products') // ❌ Đã bỏ biến này
const user = ref(null) 
const token = ref(localStorage.getItem('auth_token')) 


// Cấu hình Axios
if (token.value) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
}


// --- 2. LOGIC AUTH ---
const handleLogin = async (credentials) => {
  try {
    const res = await axios.post('/api/login', credentials);
    token.value = res.data.token || res.data.access_token;
    user.value = res.data.user;
    
    localStorage.setItem('auth_token', token.value);
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    
    alert(`Xin chào, ${user.value ? user.value.name : 'Bạn'}!`);
    await fetchUser();
    

    fetchCart();
    router.push('/'); // ✅ Chuyển trang bằng Router
  } catch (err) {
    alert('❌ Đăng nhập thất bại: ' + (err.response?.data?.message || err.message));
  }
}
const handleRegister = async (formData) => {
  try {
    // Gọi API Laravel (Bạn cần chắc chắn Backend đã có route /api/register)
    const res = await axios.post('/api/register', formData);
    
    toast.success("Đăng ký thành công!");
    
    // Đăng ký xong thì tự đăng nhập luôn cho tiện
    await handleLogin({ 
      email: formData.email, 
      password: formData.password 
    });
    
  } catch (err) {
    alert('❌ Lỗi đăng ký: ' + (err.response?.data?.message || err.message));
  }
}

const handleLogout = async () => {
  if(!confirm('Đăng xuất nhé?')) return;
  try { await axios.post('/api/logout'); } catch(e) {}
  
  token.value = null; user.value = null; cartItems.value = [];
  localStorage.removeItem('auth_token');
  delete axios.defaults.headers.common['Authorization'];
  
  router.push('/login'); // ✅ Đá về trang login
}

// --- 3. LOGIC SẢN PHẨM & GIỎ HÀNG ---
const products = ref([]); const cartItems = ref([]); 
const currentPage = ref(1); const lastPage = ref(1);

const currentFilters = ref({}) 
const isloading=ref(false)

// Thêm tham số shouldPush vào cuối
const fetchProducts = async (page = 1, filters = {}, shouldPush = true) => {
  isloading.value = true;
  try {
    // Chỉ push router nếu không phải lần đầu load trang (F5)
    if (shouldPush && parseInt(route.query.page) !== page) {
      router.push({ 
        query: { ...route.query, page: page.toString() } 
      }).catch(() => {})
    }

    if (Object.keys(filters).length > 0) {
      currentFilters.value = filters
    }

    const params = {
      page: page,
      keyword: currentFilters.value.keyword || '',
      price_min: currentFilters.value.min_price || '',
      price_max: currentFilters.value.max_price || '',
    }

    const res = await axios.get('/api/products', { params })
    
    products.value = res.data.data.data
    currentPage.value = res.data.data.current_page
    lastPage.value = res.data.data.last_page
  } catch (e) { 
    console.error(e) 
  } finally {
    isloading.value = false
  }
}

const fetchCart = async () => {
  if (!token.value) return; 
  try {
    const res = await axios.get('/api/cart');
    cartItems.value = res.data.data || res.data;
  } catch (e) { 
    if(e.response && e.response.status === 401) handleLogout();
  }
}
//add to cart
const addToCart = async (product) => {
  if (!token.value) {
    toast.error("Vui lòng đăng nhập!");
    router.push('/login');
    return;
  }
  if(products.stock <=0){
    toast.error("Sản phẩm hết hàng!");
    return;
  }
  try {
    await axios.post('/api/cart', { product_id: product.id, quantity: 1 });
      toast.success("Đã thêm " + product.name + " vào giỏ hàng!");

    const productInList = products.value.find(p => p.id === product.id);
        if (productInList && productInList.stock > 0) {
            productInList.stock -= 1; 
        }
     fetchCart();
  } catch (e) {toast.error("Không thể thêm hàng: " + e.message); }
}

const updateQuantity = async (item, change) => {
  const newQty = item.quantity + change;
  if(newQty < 1) return;
  try {
    await axios.put('/api/cart/' + item.id, { quantity: newQty });
    fetchCart();
  } catch (e) { alert('Lỗi update') }
}

const removeFromCart = async (id) => {
  if(!confirm("Xóa nhé?")) return;
  try { await axios.delete('/api/cart/' + id); fetchCart(); } 
  catch (e) { alert('Lỗi xóa') }
}

const totalAmount = computed(() => {
  return cartItems.value.reduce((sum, item) => {
    const price = item.product ? Number(item.product.price) : 0;
    const quantity = Number(item.quantity);
    return sum + (price * quantity);
  }, 0);
});

// Chuyển view sang Checkout
const handleCheckout = () => { 
  router.push('/checkout'); 
}

// --- 4. LOGIC ĐẶT HÀNG ---
const submitOrder = async (orderInfo) => {
  if(!confirm("Xác nhận đặt hàng?")) return;
  isloading.value=true
  try {
    const payload = {
      shipping_address: orderInfo.address, 
      phone: orderInfo.phone,
      payment_method: orderInfo.payment_method,
      items: cartItems.value.map(item => ({
        product_id: item.product ? item.product.id : item.product_id,
        quantity: item.quantity
      }))
    };

    console.log("Đang gửi đơn hàng:", payload); 
    const res = await axios.post('/api/orders', payload);

    if (res.data.status) {
        // --- XỬ LÝ VNPAY ---
        if (orderInfo.payment_method === 'vnpay') {
           try {
               const vnpayRes = await axios.post('/api/payment/vnpay', {
                   order_id: res.data.data.id
               });
               const vnpayUrl = vnpayRes.data.payment_url; 
               if (vnpayUrl) {
                   window.location.href = vnpayUrl; 
                   return; 
               } else {
                   alert('Lỗi: Server không trả về link thanh toán!');
               }
           } catch (vnpayErr) {
               console.error('Lỗi API VNPAY:', vnpayErr);
               alert('Không thể tạo giao dịch VNPAY.');
               return;
           }
        }

        // --- XỬ LÝ COD ---
        alert('🎉 ' + res.data.message);
        cartItems.value = [];
        fetchCart(); 
        router.push('/');
    }

  } catch (err) {
    console.error("Lỗi đặt hàng:", err);
    const serverErrors = err.response?.data?.errors;
    if (serverErrors) {
        const errorMsg = Object.values(serverErrors).flat().join('\n');
        alert('❌ Lỗi dữ liệu:\n' + errorMsg);
    } else {
        alert('❌ Lỗi đặt hàng: ' + (err.response?.data?.message || err.message));
    }
  } finally{
    isloading.value=false
  }
}
// Hàm lấy thông tin user từ Token (để F5 không bị mất)
const fetchUser = async () => {
    if (!token.value) return;
    try {
        const res = await axios.get('/api/user'); // Route mặc định của Laravel Sanctum
        user.value = res.data; // Lưu lại thông tin (bao gồm role)
        console.log("👤 User Info:", user.value); // <--- Xem role ở đây nè
    } catch (e) {
        // Token hết hạn hoặc lỗi -> Đăng xuất
        handleLogout();
    }
}

// --- 5. KHỞI TẠO ---
onMounted(async() => {
  document.title = "🛍️ Shop Của Hậu"
  // Check VNPAY redirect
  const urlParams = new URLSearchParams(window.location.search);
  const vnpStatus = urlParams.get('vnpay_status');

  if (vnpStatus === 'success') {
      alert('✅ THANH TOÁN VNPAY THÀNH CÔNG!');
      cartItems.value = []; 
      fetchCart(); 
      router.push('/orders'); // Xem lịch sử đơn
      window.history.replaceState({}, document.title, "/"); 
  } 
  else if (vnpStatus === 'failed') {
      alert('❌ Thanh toán thất bại hoặc bị hủy!');
      window.history.replaceState({}, document.title, "/");
  }

  // fetchProducts(); 
  if (token.value) { 
    await fetchUser();
    fetchCart(); }
    // 1. Lấy số trang từ URL (ví dụ: localhost:8000/admin/product?page=5)
    const pageFromUrl = parseInt(route.query.page) || 1
    
    // 2. Gọi API với đúng số trang đó
    fetchProducts(pageFromUrl,{},false)
  
})
</script>

<template>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white shadow-sm rounded">
       <div class="d-flex align-items-center gap-3">
          <h1 class="h3 m-0 text-primary cursor-pointer" @click="router.push('/')">🛍️ Shop Của Hậu</h1>
          <span v-if="user" class="text-muted">| Hi, {{ user.name }}</span>
          <router-link to="/wishlist" class="btn  text-danger position-relative me-2" title="Yêu thích">
                  <i class="bi bi-heart-fill"></i>
              </router-link>
       </div>

       <div class="d-flex gap-2">
          <template v-if="token">
             <button class="btn btn-outline-secondary" @click="router.push('/orders')">📦 Đơn mua</button>
             
             <button class="btn btn-outline-primary position-relative" @click="router.push('/cart')">
               🛒 Giỏ hàng
               <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ cartItems.length }}</span>
             </button>
             
              <router-link 
                v-if="user && user.role === 'admin'" 
                to="/admin/dashboard" 
                class="btn btn-danger fw-bold">
                 Trang Quản Lý
             </router-link>
             <button class="btn btn-danger" @click="handleLogout">Đăng xuất</button>
          </template>
          
          <template v-else>
             <button class="btn btn-primary" @click="router.push('/login')">Đăng nhập</button>
          </template>
       </div>
    </div>
    
    <router-view 
        :products="products"
        :cartItems="cartItems"
        :totalAmount="totalAmount"
        :user="user"
        :currentPage="currentPage"
        :lastPage="lastPage"
        :isloading="isloading"
        @changePage="fetchProducts"
        @addToCart="addToCart"
        @removeFromCart="removeFromCart"
        @updateQuantity="updateQuantity"
        @checkout="handleCheckout"
        @submit-order="submitOrder"
        @login-success="handleLogin"
        @cancel="router.push('/cart')"
        @register-submit="handleRegister"
        @search="fetchProducts(1, $event)"
    ></router-view>
    
  </div>
</template>