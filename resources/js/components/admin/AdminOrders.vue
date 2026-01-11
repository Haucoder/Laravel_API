<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import AdminSidebar from './AdminSidebar.vue'
import { useToast } from "vue-toastification";
const toast = useToast();
const orders = ref([])
const router = useRouter()

// Lấy danh sách đơn hàng
const fetchOrders = async () => {
    try {
        const res = await axios.get('/api/admin/orders')
        orders.value = res.data.data.data // Laravel paginate trả về object có data
    } catch (err) {
        console.error(err)
        alert('Bạn không có quyền hoặc lỗi hệ thống!')
        router.push('/') // Đá về trang chủ nếu lỗi
    }
}

// Hàm đổi màu badge cho đẹp
const getStatusBadge = (status) => {
    return {
        'pending': 'bg-warning text-dark',
        'paid': 'bg-primary',
        'shipping': 'bg-info text-dark',
        'completed': 'bg-success',
        'canceled': 'bg-secondary'
    }[status] || 'bg-light text-dark'
}

// Hàm cập nhật trạng thái
const updateStatus = async (order, newStatus) => {
    if(!confirm(`Đổi trạng thái đơn #${order.id} sang [${newStatus}]?`)) return;
    
    try {
        await axios.put(`/api/admin/orders/${order.id}/status`, { status: newStatus })
        toast.success(`✅ Đã cập nhật trạng thái đơn #${order.id} thành [${newStatus}]`)
        fetchOrders() // Load lại danh sách
    } catch (e) {
        alert('❌ Lỗi cập nhật' + (e.response?.data?.message || e.message))
    }
}
// cleanup orders
const runCleanup = async () => {
    if(confirm('Bạn có muốn dọn dẹp và hoàn kho các đơn hàng quá hạn không?')) {
        try {
            const res = await axios.post('/api/admin/orders/cleanup');
             toast.success(res.data.message);
            fetchOrders(); // Load lại danh sách đơn hàng để cập nhật trạng thái mới
        } catch (error) {
            console.error("Lỗi dọn dẹp:", error);
        }
    }
}
onMounted(() => {
    fetchOrders()
})
</script>

<template>
  <div class="container-fluid mt-4">
      <div class="row">
          
          <div class="col-md-3 col-lg-2 px-0">
              <AdminSidebar />
          </div>

          <div class="col-md-9 col-lg-10">
              
              <div class="card shadow border-0">
                  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                      <h5 class="mb-0">👑 Quản Lý Đơn Hàng</h5>
                    <!--  <button class="btn btn-sm btn-light text-primary" @click="fetchOrders">🔄 Làm mới</button>-->
                      <button @click="runCleanup" class="btn btn-sm btn-warning shadow-sm">
                            <i class="bi bi-trash"></i> Dọn dẹp đơn treo (quá 30p)
                        </button>
                  </div>
                  
                  <div class="card-body p-0">
                      <div class="table-responsive">
                          <table class="table table-hover align-middle mb-0">
                              <thead class="bg-light">
                                  <tr>
                                      <th>Mã đơn</th>
                                      <th>Khách hàng</th>
                                      <th>Tổng tiền</th>
                                      <th>Trạng thái</th>
                                      <th>Hành động</th>
                                  </tr>
                              </thead>
                              <tbody>
                                    <tr v-for="order in orders" :key="order.id">
                                        <td>
                                            <router-link 
                                                :to="{ name: 'AdminOrderDetail', params: { id: order.id } }"
                                                class="text-decoration-none fw-bold text-primary"
                                            >
                                                #{{ order.id }}
                                            </router-link>
                                            <br>
                                            <small class="text-muted">{{ new Date(order.created_at).toLocaleString() }}</small>
                                        </td>

                                        <td>
                                            <div class="fw-bold text-dark">{{ order.user ? order.user.name : 'Khách vãng lai' }}</div>
                                            <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ order.shipping_address }}</small><br>
                                            <small class="text-muted"><i class="bi bi-telephone"></i> {{ order.phone }}</small>
                                        </td>

                                        <td class="fw-bold text-danger">{{ Number(order.total_price || 0).toLocaleString() }} đ</td>

                                        <td>
                                            <span class="badge" :class="getStatusBadge(order.status)">
                                                {{ (order.status || "unknown").toUpperCase() }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <router-link 
                                                    :to="{ name: 'AdminOrderDetail', params: { id: order.id } }" 
                                                    class="btn btn-sm btn-outline-info"
                                                    title="Xem chi tiết"
                                                >
                                                    <i class="bi bi-eye"></i> </router-link>

                                                <select 
                                                    class="form-select form-select-sm" 
                                                    style="width: 130px"
                                                    :value="order.status"
                                                    @change="updateStatus(order, $event.target.value)"
                                                    :disabled="order.status === 'canceled' || order.status === 'completed'"
                                                >
                                                    <option value="pending">⏳ Chờ xử lý</option>
                                                    <option value="shipping">🚚 Đang giao</option>
                                                    <option value="completed">✅ Hoàn thành</option>
                                                    <option value="canceled">❌ Hủy đơn</option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                          </table>
                      </div>
                  </div>
              </div>
              </div>
      </div>
  </div>
</template>
