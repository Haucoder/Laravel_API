<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useToast } from "vue-toastification";

const route = useRoute();
const toast = useToast();

const product = ref(null);
const comments = ref([]);
const isWishlist = ref(false);
const isLoading = ref(true);
const emit = defineEmits(['addToCart']);
// Biến cho form bình luận
const newComment = ref({ content: '', rating: 5 });

// 1. Lấy dữ liệu khi vào trang
const fetchProductDetail = async () => {
    try {
        const res = await axios.get(`/api/products/${route.params.id}`);
        product.value = res.data.product;
        comments.value = res.data.product.comments; // Laravel đã trả về kèm comments
        isWishlist.value = res.data.is_wishlist;
    } catch (e) {
        toast.error("Không tìm thấy sản phẩm!");
    } finally {
        isLoading.value = false;
    }
};

// 2. Xử lý Yêu thích
const toggleWishlist = async () => {
    try {
        const res = await axios.post('/api/wishlist/toggle', { product_id: product.value.id });
        
        // Cập nhật trạng thái trái tim ngay lập tức
        if (res.data.status === 'added') {
            isWishlist.value = true;
            toast.success("❤️ Đã thích sản phẩm!");
        } else {
            isWishlist.value = false;
            toast.info("💔 Đã bỏ thích.");
        }
    } catch (e) {
        if(e.response && e.response.status === 401) {
            toast.warning("Vui lòng đăng nhập để lưu sản phẩm!");
        } else {
            toast.error("Lỗi kết nối server");
        }
    }
};

// 3. Gửi bình luận
const submitComment = async () => {
    if (!newComment.value.content) return toast.warning("Vui lòng nhập nội dung!");

    try {
        const res = await axios.post('/api/comments', {
            product_id: product.value.id,
            content: newComment.value.content,
            rating: newComment.value.rating
        });

        // Thêm bình luận mới vào đầu danh sách ngay lập tức
        comments.value.unshift(res.data);
        
        // Reset form
        newComment.value.content = '';
        toast.success("Cảm ơn đánh giá của bạn!");
    } catch (e) {
        toast.error("Lỗi khi gửi bình luận (Bạn đã đăng nhập chưa?)" + e.message);
    }
};

// Emit sự kiện thêm vào giỏ (Nếu Hậu dùng props/emit từ App.vue thì sửa lại nhé)
// Ở đây mình giả lập gọi API trực tiếp luôn cho tiện
const addToCart = async () => {
     emit('addToCart', product.value);
     toast.success("Đã thêm vào giỏ hàng!");
}

onMounted(() => {
    fetchProductDetail();
});
</script>

<template>
    <div class="container mt-4" v-if="!isLoading && product">
        <div class="row shadow-sm bg-white p-4 rounded">
            <div class="col-md-5">
                <img :src="product.image_url || 'https://placehold.co/400'" class="img-fluid rounded border" alt="Product Image">
            </div>
            <div class="col-md-7">
                <h2 class="fw-bold">{{ product.name }}</h2>
                <div class="text-warning mb-2">
                    <i class="bi bi-star-fill" v-for="n in 5" :key="n"></i> 
                    <span class="text-muted ms-2">({{ comments.length }} đánh giá)</span>
                </div>
                
                <h3 class="text-danger fw-bold my-3">{{ Number(product.price).toLocaleString() }} đ</h3>
                <div class="text-muted mb-3">
                    <i class="bi bi-eye"></i> {{ product.views }} lượt xem
                </div>
                <p class="text-muted">{{ product.description || 'Chưa có mô tả chi tiết cho sản phẩm này.' }}</p>
                <p>Tồn kho: <strong>{{ product.stock }}</strong></p>

                <div class="d-flex gap-3 mt-4">
                    <button class="btn btn-primary btn-lg " @click="addToCart" :disabled="product.stock <= 0">
                        <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                    </button>

                        <button class="btn btn-lg border" 
                            :class="isWishlist ? 'btn-danger text-white' : 'btn-outline-danger'"
                            @click="toggleWishlist">
                            <i class="bi" :class="isWishlist ? 'bi-heart-fill' : 'bi-heart'"></i>
                        </button>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <h4 class="mb-3">Đánh giá & Bình luận</h4>
                
                <div class="card mb-4 bg-light">
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="fw-bold me-2">Đánh giá:</label>
                            <select v-model="newComment.rating" class="form-select d-inline-block w-auto">
                                <option value="5">⭐⭐⭐⭐⭐ (Tuyệt vời)</option>
                                <option value="4">⭐⭐⭐⭐ (Tốt)</option>
                                <option value="3">⭐⭐⭐ (Bình thường)</option>
                                <option value="2">⭐⭐ (Tệ)</option>
                                <option value="1">⭐ (Rất tệ)</option>
                            </select>
                        </div>
                        <textarea v-model="newComment.content" class="form-control" rows="3" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..."></textarea>
                        <button class="btn btn-success mt-2" @click="submitComment">Gửi đánh giá</button>
                    </div>
                </div>

                <div v-if="comments.length > 0">
                    <div v-for="comment in comments" :key="comment.id" class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <strong>{{ comment.user ? comment.user.name : 'Người dùng ẩn danh' }}</strong>
                            <span class="text-warning">
                                <span v-for="n in comment.rating">★</span>
                            </span>
                        </div>
                        <p class="mb-1">{{ comment.content }}</p>
                        <small class="text-muted">{{ new Date(comment.created_at).toLocaleString() }}</small>
                    </div>
                </div>
                <div v-else class="text-center text-muted py-3">
                    Chưa có đánh giá nào. Hãy là người đầu tiên!
                </div>
            </div>
        </div>
    </div>
    
    <div v-else class="text-center mt-5">
        <div class="spinner-border text-primary" role="status"></div>
        <p>Đang tải chi tiết...</p>
    </div>
</template>