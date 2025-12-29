<template>
  <div class="container mt-5">
    <div class="row">
      
      <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-header bg-white">
            <h5 class="mb-0 text-primary"><i class="bi bi-person-lines-fill"></i> Thông tin cá nhân</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="updateInfo">
              
              <div class="text-center mb-4">
                <img src="https://ui-avatars.com/api/?name=User&background=random" class="rounded-circle" width="80">
              </div>

              <div class="mb-3">
                <label class="form-label">Email (Không thể sửa)</label>
                <input type="email" class="form-control bg-light" :value="user.email" disabled>
              </div>

              <div class="mb-3">
                <label class="form-label">Họ và tên</label>
                <input v-model="user.name" type="text" class="form-control" required>
              </div>

              <button type="submit" class="btn btn-primary w-100" :disabled="loadingInfo">
                <span v-if="loadingInfo" class="spinner-border spinner-border-sm"></span>
                <span v-else>💾 Lưu thay đổi</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-danger h-100"> <div class="card-header bg-white">
            <h5 class="mb-0 text-danger"><i class="bi bi-shield-lock"></i> Đổi mật khẩu</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="changePassword">
              
              <div class="mb-3">
                <label class="form-label">Mật khẩu hiện tại</label>
                <input v-model="passForm.current_password" type="password" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Mật khẩu mới</label>
                <input v-model="passForm.new_password" type="password" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Nhập lại mật khẩu mới</label>
                <input v-model="passForm.new_password_confirmation" type="password" class="form-control" required>
              </div>

              <button type="submit" class="btn btn-danger w-100 mt-3" :disabled="loadingPass">
                 <span v-if="loadingPass" class="spinner-border spinner-border-sm"></span>
                 <span v-else>🔒 Cập nhật mật khẩu</span>
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
// Nếu ông có dùng sweetalert2 thì import vào cho đẹp, không thì dùng alert thường
import Swal from 'sweetalert2'; 

const user = ref({
    name: '',
    email: '',
    // phone: ''
});

const passForm = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
});

const loadingInfo = ref(false);
const loadingPass = ref(false);

// 1. Route GET /profile (Lấy thông tin khi vào trang)
const fetchProfile = async () => {
    try {
        const response = await axios.get('/api/profile');
        user.value = response.data.user; // Đổ dữ liệu vào form
    } catch (error) {
        console.error("Lỗi tải profile:", error);
    }
};

// 2. Route PUT /profile (Cập nhật thông tin)
const updateInfo = async () => {
    loadingInfo.value = true;
    try {
        await axios.put('/api/profile', user.value);
        Swal.fire('Thành công', 'Cập nhật thông tin thành công!', 'success');
    } catch (error) {
        Swal.fire('Lỗi', 'Không thể cập nhật thông tin.', 'error');
    } finally {
        loadingInfo.value = false;
    }
};

// 3. Route POST /profile/change-password (Đổi mật khẩu)
const changePassword = async () => {
    // Validate đơn giản ở Client
    if (passForm.value.new_password !== passForm.value.new_password_confirmation) {
        Swal.fire('Lỗi', 'Mật khẩu xác nhận không khớp!', 'warning');
        return;
    }

    loadingPass.value = true;
    try {
        await axios.post('/api/profile/change-password', passForm.value);
        
        Swal.fire('Thành công', 'Đổi mật khẩu thành công!', 'success');
        
        // Reset form sau khi đổi xong
        passForm.value = {
            current_password: '',
            new_password: '',
            new_password_confirmation: ''
        };
    } catch (error) {
        // Lấy lỗi từ Backend trả về (ví dụ: Mật khẩu cũ sai)
        const msg = error.response?.data?.message || 'Đổi mật khẩu thất bại.';
        Swal.fire('Lỗi', msg, 'error');
    } finally {
        loadingPass.value = false;
    }
};

// Chạy hàm lấy thông tin ngay khi trang load
onMounted(() => {
    fetchProfile();
});
</script>