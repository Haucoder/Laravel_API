import { createRouter, createWebHistory } from 'vue-router'

// Import các component
import ProductList from './components/ProductList.vue'
import ShoppingCart from './components/ShoppingCart.vue'
import Login from './components/Login.vue'
import Checkout from './components/Checkout.vue'
import OrderHistory from './components/OrderHistory.vue'
import Register from './components/Register.vue'
import AdminOrders from './components/admin/AdminOrders.vue'
import AdminProducts from './components/admin/AdminProducts.vue'
import AdminCategory from './components/admin/AdminCategory.vue'
import AdminDashboard from './components/admin/AdminDashBoard.vue'
import ProductDetail from './components/ProductDetail.vue'
import Wishlist from './components/Wishlist.vue'
import OrderDetail from './components/OrderDetail.vue'
import UserProfile from './components/user/UserProfile.vue'
import Home from './components/Home.vue'

const routes = [
    { path: '/products', component: ProductList, name: 'products' },
    { path: '/', component: Home, name: 'home' },
    { path: '/cart', component: ShoppingCart, name: 'cart' },
    { path: '/login', component: Login, name: 'login' },
    { path: '/checkout', component: Checkout, name: 'checkout' },   
    { path: '/orders', component: OrderHistory, name: 'orders' },
    { path: '/register', component: Register, name: 'register' },
    { path: '/admin/orders', component: AdminOrders, name: 'admin-orders' },
    {  path: '/admin/products',  component: AdminProducts},
    {  path: '/admin/categories',  component: AdminCategory},
    {  path: '/admin/dashboard',  component: AdminDashboard},
    {
    path: '/product/:id', 
    name: 'ProductDetail',  
    component: ProductDetail,
    },
    { path: '/wishlist', component: Wishlist, name: 'wishlist' },
    // user and admin can view order detail
        {
        path: '/admin/orders/:id',
        name: 'AdminOrderDetail',
        component: OrderDetail,
        meta: { role: 'admin' } // <--- Đánh dấu: Đây là Admin
    },

    // --- ĐƯỜNG DẪN CHO USER (KHÁCH) ---
    {
        path: '/my-orders/:id',
        name: 'UserOrderDetail',
        component: OrderDetail,
        meta: { role: 'user' } // <--- Đánh dấu: Đây là User
    },
     {
        path: '/profile',
        name: 'UserProfile',
        component: UserProfile
    }
        
   

]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router