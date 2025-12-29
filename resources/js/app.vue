<script setup>
import { ref, computed, onMounted,reactive } from 'vue'
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
// const fetchProducts = async (page = 1, filters = {}, shouldPush = true) => {
//   isloading.value = true;
//   try {
//     // Chỉ push router nếu không phải lần đầu load trang (F5)
//     if (shouldPush && parseInt(route.query.page) !== page) {
//       router.push({ 
//         query: { ...route.query, page: page.toString() } 
//       }).catch(() => {})
//     }

//     if (Object.keys(filters).length > 0) {
//       currentFilters.value = filters
//     }

//     const params = {
//       page: page,
//       keyword: currentFilters.value.keyword || '',
//       price_min: currentFilters.value.min_price || '',
//       price_max: currentFilters.value.max_price || '',
//     }

//     const res = await axios.get('/api/products', { params })
    
//     products.value = res.data.data.data
//     currentPage.value = res.data.data.current_page
//     lastPage.value = res.data.data.last_page
//   } catch (e) { 
//     console.error(e) 
//   } finally {
//     isloading.value = false
//   }
// }
const productCache = ref({}); 

const fetchProducts = async (page = 1, filters = {}, shouldPush = true) => {
  // Logic cập nhật Router cũ của ông (Giữ nguyên)
  if (shouldPush && parseInt(route.query.page) !== page) {
      router.push({ 
        query: { ...route.query, page: page.toString() } 
      }).catch(() => {})
  }

  if (Object.keys(filters).length > 0) {
      currentFilters.value = filters
  }

  // 2. TẠO PARAMS CHUẨN
  const params = {
      page: page,
      keyword: currentFilters.value.keyword || '',
      price_min: currentFilters.value.min_price || '',
      price_max: currentFilters.value.max_price || '',
  }

  // 3. TẠO "CHÌA KHÓA" CACHE (Quan trọng)
  // Biến object params thành chuỗi để làm ID duy nhất. 
  // Ví dụ: '{"page":1,"keyword":"iphone"}'
  const cacheKey = JSON.stringify(params);

  // 4. KIỂM TRA KHO HÀNG (CACHE)
  if (productCache.value[cacheKey]) {
      // ✅ CÓ HÀNG: Lấy ra xài luôn, KHÔNG gọi API
      const cachedData = productCache.value[cacheKey];
      
      products.value = cachedData.data;
      currentPage.value = cachedData.current_page;
      lastPage.value = cachedData.last_page;

      // UX: Cuộn lên đầu trang ngay lập tức tạo cảm giác nhanh
      window.scrollTo({ top: 0, behavior: 'auto' }); 
      
      // 🚀 Tải ngầm trang sau (Prefetch)
      prefetchNextPage(page, currentFilters.value);
      
      return; // Dừng hàm tại đây
  }

  // 5. NẾU KHÔNG CÓ TRONG KHO -> MỚI GỌI API
  isloading.value = true;
  try {
    const res = await axios.get('/api/products', { params })
    
    // Dữ liệu API trả về
    const responseData = res.data.data; // Lưu gọn

    // Cập nhật biến hiển thị
    products.value = responseData.data;
    currentPage.value = responseData.current_page;
    lastPage.value = responseData.last_page;

    // 6. LƯU VÀO KHO ĐỂ DÙNG LẦN SAU
    productCache.value[cacheKey] = {
        data: responseData.data,
        current_page: responseData.current_page,
        last_page: responseData.last_page
    };

    // UX: Cuộn lên đầu
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // 🚀 Tải ngầm trang sau
    prefetchNextPage(page, currentFilters.value);

  } catch (e) { 
    console.error(e) 
  } finally {
    isloading.value = false
  }
}

// === HÀM TẢI NGẦM (CHẠY ÂM THẦM KHÔNG ẢNH HƯỞNG UI) ===
const prefetchNextPage = async (currentPage, filters) => {
    // Nếu chưa đến trang cuối thì mới tải trang kế
    if (currentPage < lastPage.value) {
        const nextPage = currentPage + 1;
        
        // Tạo params cho trang sau
        const nextParams = {
            page: nextPage,
            keyword: filters.keyword || '',
            price_min: filters.min_price || '',
            price_max: filters.max_price || '',
        };

        const nextCacheKey = JSON.stringify(nextParams);

        // Nếu trong kho chưa có trang sau thì mới tải
        if (!productCache.value[nextCacheKey]) {
            try {
                // Gọi API nhưng KHÔNG bật biến isloading
                const res = await axios.get('/api/products', { params: nextParams });
                
                // Lưu luôn vào kho
                productCache.value[nextCacheKey] = {
                    data: res.data.data.data,
                    current_page: res.data.data.current_page,
                    last_page: res.data.data.last_page
                };
                console.log(`[Prefetch] Đã tải ngầm trang ${nextPage}`);
            } catch (e) {
                // Lỗi tải ngầm thì kệ nó, không cần báo user
            }
        }
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

// const updateQuantity = async (item, change) => {
//   const oldQty = item.quantity;
//   const newQty = item.quantity + change;
//   if(newQty < 1) return;
//   try {
//     await axios.put('/api/cart/' + item.id, { quantity: newQty });
//     fetchCart();
//   } catch (e) { alert('Lỗi update') }
// }
const updateQuantity = async (item, change) => {
  // 1. Lưu lại giá trị cũ (Để lỡ lỗi thì quay xe)
  const oldQty = item.quantity;
  const newQty = oldQty + change;

  // 2. Validate (Kiểm tra điều kiện)
  // Không cho nhỏ hơn 1
  if (newQty < 1) return;
  
  // Kiểm tra tồn kho (Nếu biến item có chứa thông tin product)
  if (item.product && item.product.stock && newQty > item.product.stock) {
      alert('Đã vượt quá số lượng tồn kho!');
      return;
  }

  // 3. QUAN TRỌNG NHẤT: Cập nhật giao diện NGAY LẬP TỨC
  // Người dùng sẽ thấy số nhảy ngay, cảm giác cực mượt
  item.quantity = newQty; 

  // 4. Gửi API ngầm bên dưới
  try {
    await axios.put('/api/cart/' + item.id, { quantity: newQty });
    
    // ⚠️ LƯU Ý: Mình ĐÃ BỎ dòng fetchCart() ở đây.
    // Vì giao diện đã đúng rồi, gọi lại fetchCart làm gì cho lag thêm!
    
  } catch (e) {
    // 5. Nếu lỗi thật thì mới trả lại số cũ (Rollback)
    item.quantity = oldQty; 
    console.error(e);
    alert('Lỗi cập nhật, vui lòng thử lại');
  }
}

// const removeFromCart = async (id) => {
//   if(!confirm("Xóa nhé?")) return;
//   try { await axios.delete('/api/cart/' + id); fetchCart(); } 
//   catch (e) { alert('Lỗi xóa') }
// }
const removeFromCart = async (id) => {
  // 1. Hỏi cho chắc ăn
  if (!confirm("Bạn muốn xóa sản phẩm này?")) return;

  // 2. LƯU LẠI "MẠNG SỐNG" (Backup dữ liệu cũ)
  // Phải dùng [... ] để copy ra mảng mới, chứ không nó dính ref
  const backupCart = [...cartItems.value];

  // 3. XÓA NGAY LẬP TỨC TRÊN GIAO DIỆN
  // Lọc bỏ item có id trùng khớp. Vue sẽ tự cập nhật màn hình ngay tức khắc.
  cartItems.value = cartItems.value.filter(item => item.id !== id);

  // 4. Giờ mới âm thầm gọi API xóa
  try {
    await axios.delete('/api/cart/' + id);
    
    // ✅ THÀNH CÔNG: Không làm gì cả! 
    // Không gọi fetchCart() nữa vì giao diện đã đúng rồi.

  } catch (e) {
    // ❌ CÓ LỖI: Hoàn tác (Rollback)
    // Trả lại danh sách cũ cho người dùng
    cartItems.value = backupCart;
    
    alert('Lỗi hệ thống, không xóa được!');

    // Xử lý 401 (Hết phiên đăng nhập) giống hàm fetchCart của bạn
    if (e.response && e.response.status === 401) handleLogout();
  }
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
                <img src="logo/logo.png" alt="loading" @click="router.push('/')" style="cursor: pointer;">
                <h1 class="h3 m-0 text-primary " @click="router.push('/')" style="cursor: pointer;"> Shop Của Hậu</h1>
            
                  <router-link to="/products" class="btn  text-primary position-relative me-2" title="Yêu thích">
                      Shop
                  </router-link>
                  <router-link to="/wishlist" class="btn text-primary position-relative me-2" title="Yêu thích">
                      Wishlist
                  </router-link>
                  <router-link to="#" class="btn  text-primary position-relative me-2" title="Yêu thích">
                      Contact
                  </router-link>
          </div>

          
            <div class="d-flex align-items-center gap-3">
        
              <template v-if="!token">
                <button class="btn btn-primary" @click="router.push('/login')">Đăng nhập</button>
              </template>

              <template v-else>
                
                <button class="btn btn-outline-primary position-relative border-0 me-2" @click="router.push('/cart')">
                  <i class="bi bi-cart-fill"></i> <span v-if="cartItems.length > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    {{ cartItems.length }}
                  </span>
                </button>

                <div class="dropdown">
                  <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img v-if="user && user.avatar" :src="user.avatar" alt="Avatar" width="32" height="32" class="rounded-circle me-2 border">
                    <i v-else class="bi bi-person-circle fs-4 me-2"></i> 
                    
                    <span class="d-none d-sm-inline fw-bold">{{ user ? user.name : 'user' }}</span>
                  </a>

                  <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                    
                    <template v-if="user && user.role === 'admin'">
                      <li>
                        <router-link to="/admin/dashboard" class="dropdown-item text-danger fw-bold">
                          <i class="bi bi-speedometer2 me-2"></i> Trang Quản Trị
                        </router-link>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                    </template>

                    <template v-else>
                      <li>
                        <router-link to="/profile" class="dropdown-item">
                          <i class="bi bi-person-gear me-2"></i> Thông tin cá nhân
                        </router-link>
                      </li>
                      <li>
                        <router-link to="/orders" class="dropdown-item">
                          <i class="bi bi-box-seam me-2"></i> Lịch sử đơn hàng
                        </router-link>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                    </template>

                    <li>
                      <button class="dropdown-item" @click="handleLogout">
                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                      </button>
                    </li>

                  </ul>
                </div>

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
