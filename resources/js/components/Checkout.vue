<script setup>
import { ref } from 'vue'

const props = defineProps(['cartItems', 'totalAmount', 'user','isloading'])
const emit = defineEmits(['submit-order', 'cancel'])

// Dữ liệu form (Tự điền sẵn nếu đã login)
const form = ref({
  name: props.user?.name || '',
  phone: '',
  address: '',
  payment_method: 'cod' // Mặc định: Thanh toán khi nhận hàng
})

const submitOrder = () => {
  // Gửi dữ liệu ra App cha để gọi API
  emit('submit-order', form.value)
}
</script>

<template>
  <div class="row position-relative">
    <div v-if="props.isloading" class="loading-overlay">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status"></div>
                                <div class="mt-2 fw-bold text-primary">Đang tải dữ liệu...</div>
                            </div>
            </div>
    <div class="col-md-7">
      <div class="card shadow-sm p-4 mb-4  ">
         
        <h4 class="mb-3 text-primary">🚚 Thông tin giao hàng</h4>
        <form @submit.prevent="submitOrder">
          <div class="mb-3">
            <label class="form-label">Họ và tên</label>
            <input v-model="form.name" type="text" class="form-control" required placeholder="Nguyễn Văn A">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Số điện thoại</label>
            <input v-model="form.phone" type="tel" class="form-control" required placeholder="0912xxx...">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Địa chỉ nhận hàng</label>
            <textarea v-model="form.address" class="form-control" rows="2" required placeholder="Số 123, Đường ABC..."></textarea>
          </div>
          
          <h5 class="mb-3 mt-4">Phương thức thanh toán</h5>
          <div class="form-check mb-2">
            <input v-model="form.payment_method" value="cod" class="form-check-input" type="radio" id="cod">
            <label class="form-check-label" for="cod">💵 Thanh toán khi nhận hàng (COD)</label>
          </div>
          <div class="form-check mb-4">
            <input v-model="form.payment_method" value="vnpay" class="form-check-input" type="radio" id="vnpay">
            <label class="form-check-label" for="vnpay">💳 Thanh toán qua VNPAY</label>
          </div>

          <div class="d-flex gap-2">
            <button type="button" class="btn btn-secondary" @click="$emit('cancel')">⬅️ Quay lại</button>
            <button type="submit" class="btn btn-success grow" >✅ ĐẶT HÀNG NGAY</button>
          </div>
        </form>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card bg-light p-3">
        <h5 class="mb-3">Đơn hàng của bạn</h5>
        <ul class="list-group mb-3">
          <li v-for="item in cartItems" :key="item.id" class="list-group-item d-flex justify-content-between lh-sm">
            <div>
              <h6 class="my-0">{{ item.product?.name }}</h6>
              <small class="text-muted">SL: {{ item.quantity }}</small>
            </div>
            <span class="text-muted">{{ Number(item.product?.price * item.quantity).toLocaleString() }} đ</span>
          </li>
          <li class="list-group-item d-flex justify-content-between bg-white">
            <span class="fw-bold">Tổng cộng (VND)</span>
            <strong class="text-danger fs-5">{{ Number(totalAmount).toLocaleString() }} đ</strong>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
<style scoped>
  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10;
}
  </style>