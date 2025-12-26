<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    
    <div v-if="loading" class="text-center py-10">Đang tải...</div>
    <div v-else-if="error" class="text-red-500 text-center py-10">{{ error }}</div>

    <div v-else-if="order" class="max-w-6xl mx-auto">
      
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
           <h1 class="text-2xl font-bold text-gray-800">
             {{ isAdmin ? 'Quản lý đơn hàng' : 'Chi tiết đơn hàng' }} #{{ order.id }}
          </h1>
          <p class="text-sm text-gray-500">Ngày đặt: {{ formatDate(order.created_at) }}</p>
        </div>
        
        <div class="flex items-center gap-3">
            <span :class="getStatusColor(order.status)" class="px-4 py-2 rounded-full font-semibold text-sm border uppercase">
                {{ formatStatus(order.status) }}
            </span>
            
            <button @click="goBack" class="px-4 py-2 bg-white border rounded hover:bg-gray-100 text-sm font-medium">
                &larr; Quay lại
            </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden h-fit">
          <table class="w-full text-left">
             <thead class="bg-gray-50 text-gray-500 text-sm uppercase">
              <tr>
                <th class="px-6 py-3">Sản phẩm</th>
                <th class="px-6 py-3">Giá</th>
                <th class="px-6 py-3">SL</th>
                <th class="px-6 py-3 text-right">Tổng</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in order.order_items" :key="item.id">
                <td class="px-6 py-4">
                  <div class="flex items-start gap-3">
                    <img :src="item.product.image_url" class="w-16 h-16 object-cover rounded border">
                    <div>
                      <p class="font-medium text-gray-900 line-clamp-2 text-sm">{{ item.product.name }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-gray-500 whitespace-nowrap">{{ formatCurrency(item.price) }}</td>
                <td class="px-6 py-4 text-gray-500">x{{ item.quantity }}</td>
                <td class="px-6 py-4 text-right font-medium text-gray-900 whitespace-nowrap">
                  {{ formatCurrency(item.price * item.quantity) }}
                </td>
              </tr>
            </tbody>
          </table>

          <div class="p-6 border-t flex justify-end">
             <div class="w-full md:w-1/2 space-y-2">
                <div class="flex justify-between text-xl font-bold text-red-600 pt-2 border-t">
                    <span>Tổng cộng:</span>
                    <span>{{ formatCurrency(order.total_price) }}</span>
                </div>
             </div>
          </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
          
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-lg mb-4 text-gray-800">Thông tin nhận hàng</h3>
            <div class="mb-4">
                <p class="text-sm text-gray-500">Mã khách hàng: #{{ order.user_id }}</p>
                <p class="text-gray-800 font-medium">SĐT: {{ order.phone }}</p>
            </div>
            <hr class="my-4">
            <h4 class="font-medium text-sm text-gray-500 uppercase mb-2">Địa chỉ</h4>
            <p class="text-gray-800">{{ order.shipping_address }}</p>
          </div>

          <div v-if="isAdmin" class="bg-blue-50 border border-blue-200 rounded-lg shadow p-6">
            <h3 class="font-bold text-blue-800 mb-3">🛠 Thao tác Admin</h3>
            
            <div class="space-y-3">
                <label class="block text-sm">Cập nhật trạng thái:</label>
                <select class="w-full p-2 border rounded bg-white">
                    <option>Chờ xử lý</option>
                    <option>Đang giao</option>
                    <option>Hoàn thành</option>
                    <option>Hủy đơn</option>
                </select>
                <button class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                    Lưu thay đổi
                </button>
            </div>
          </div>

          <div v-else class="bg-white rounded-lg shadow p-6 border border-gray-100">
             <h3 class="font-semibold text-gray-800 mb-3">Hỗ trợ</h3>
             <button 
                v-if="order.status === 'pending'"
                class="w-full border border-red-500 text-red-600 py-2 rounded hover:bg-red-50 mb-3"
             >
                Yêu cầu hủy đơn
             </button>
             <button class="w-full bg-gray-800 text-white py-2 rounded hover:bg-gray-700">
                Mua lại đơn này
             </button>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router'; // Import thêm useRouter

const route = useRoute();
const router = useRouter(); // Dùng để điều hướng nút Back

const order = ref(null);
const loading = ref(true);
const error = ref(null);

// --- KIỂM TRA ROLE ---
// Lấy giá trị từ meta đã khai báo ở router/index.js
const isAdmin = computed(() => route.meta.role === 'admin');

const fetchOrder = async () => {
    try {
        loading.value = true;
        // Logic API: 
        // Nếu là Admin thì có thể cần endpoint khác user (ví dụ: /api/admin/orders/...)
        // Nhưng ở đây giả sử dùng chung endpoint
        const response = await axios.get(`/api/orders/${route.params.id}`);
        
        if (response.data.status) {
            order.value = response.data.data;
        } else {
            error.value = response.data.message;
        }
    } catch (err) {
        error.value = "Lỗi tải dữ liệu";
        console.error(err);
    } finally {
        loading.value = false;
    }
};

// Hàm quay lại trang danh sách tương ứng
const goBack = () => {
    if (isAdmin.value) {
        router.push('/admin/orders'); // Quay về danh sách Admin
    } else {
        router.push('/orders'); // Quay về lịch sử mua hàng User
    }
};

// Các hàm format giữ nguyên
const formatCurrency = (value) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
const formatDate = (dateString) => new Date(dateString).toLocaleString('vi-VN');
const formatStatus = (status) => {
    const map = { 'pending': 'Chờ xử lý', 'canceled': 'Đã hủy', 'completed': 'Hoàn thành' };
    return map[status] || status;
};
const getStatusColor = (status) => {
    const map = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'canceled': 'bg-red-100 text-red-800',
        'completed': 'bg-green-100 text-green-800'
    };
    return map[status] || 'bg-gray-100';
};

onMounted(() => {
    fetchOrder();
});
</script>