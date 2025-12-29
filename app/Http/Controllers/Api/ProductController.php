<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\load;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Models\Comment;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{

   
 public function index(Request $request)
{
    // Load category để hiển thị tên danh mục
    $query = Product::with('category'); 

    // 1. Lọc theo Danh mục (Chỉ chạy khi có chọn)
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    // 2. Tìm kiếm tên (keyword)
    if ($request->filled('keyword')) {
        $query->where('name', 'LIKE', '%' . $request->keyword . '%');
    }

    // 3. Lọc giá (min - max)
    if ($request->filled('price_min')) {
        $query->where('price', '>=', $request->price_min);
    }
    
    if ($request->filled('price_max')) {
        $query->where('price', '<=', $request->price_max);
    }

    // 4. Sắp xếp & Phân trang
    $query->orderBy('created_at', 'desc');
    $products = $query->paginate(8); 

    return response()->json([
        'status' => true,
        'message' => 'Lấy danh sách thành công',
        'data' => $products
    ], 200);
}
    public function store(Request $request){
        $validation=Validator::make($request->all(),[
            'name'=>'required|string|max:255',
            'price'=>'required|numeric|min:0',
            'stock'=>"required|integer|min:0",
             'description'=>'nullable|string',
             'category_id'=>'required|exists:categories,id',
             'image'=>'nullable|image|mimes:jpeg,png,jpg,gif|max:5048'
        ]);
        if($validation->fails()){
            return response()->json([
                'status'=>false,
                'message'=>'loi nhap du lieu',
                'errors'=>$validation->errors(),
            ],422);
        }
        $imagepath=null;
        if($request->hasFile('image')){
            $imagepath=$request->file('image')->store('uploads','public');
        }
        $product=Product::create([
            'name'=>$request->name,
            'price'=>$request->price,
            'stock'=>$request->stock,
            'description'=>$request->description,
            'category_id'=>$request->category_id,
            'image'=>$imagepath
        ]);
        $product->image_url=$imagepath ? asset('storage/'.$imagepath) : null;

        return response()->json([
            'status'=>true,
            'message'=>'success create product',
            'data'=>$product
        ],201);
    }
    public function show($id){
        $product=Product::find($id);
        if(!$product){
            return response()->json([
                'message'=>"khong tim thay"
            ],404);
        }
        $product->increment('views');

        $product->load('category','comments.user');

        $wishlist=false;
        if(auth('sanctum')->check()){
            $user_id=auth('sanctum')->id();
            $wishlist=Wishlist::where('user_id',$user_id)->where('product_id',$id)->exists();
        }


        
        return response()->json([
            'status'=>true,
            'message'=>'success',
            'product'=>$product,
            'is_wishlist'=>$wishlist
        ],200);
    }
    public function getTrendingProducts() {
    $products = Product::orderBy('views', 'desc') // Sắp xếp giảm dần theo view
                ->take(8) // Chỉ lấy 8 sản phẩm
                ->get();

    return response()->json([
        'status' => true,
        'data' => $products
    ]);
}
 public function getFeatured() {
    // Lấy 4 sản phẩm mới nhất dựa theo ngày tạo
    $products = Product::orderBy('created_at', 'desc')->take(4)->get();
    
    // Hoặc cách 2: Lấy ngẫu nhiên
    // $products = Product::inRandomOrder()->take(4)->get();

    return response()->json($products);
}

    public function update($id, Request $request) {
    $product = Product::find($id);
    if (!$product) {
        return response()->json(['status' => false, 'message' => 'Không tìm thấy'], 404);
    }

    $validation = Validator::make($request->all(), [
        'name' => 'sometimes|string|max:255',
        'price' => 'sometimes|numeric|min:0',
        'stock' => 'sometimes|integer|min:0',
        'description' => 'nullable|string',
        'category_id' => 'sometimes|exists:categories,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Sửa mines -> mimes, pgn -> png
    ]);

    if ($validation->fails()) {
        return response()->json(['status' => false, 'errors' => $validation->errors()], 422);
    }

    // Lấy toàn bộ dữ liệu cần update
    $data = $request->except(['image']);

    // Nếu có up ảnh mới thì thêm đường dẫn ảnh vào mảng data luôn
    if ($request->hasFile('image')) {
        $imagepath = $request->file('image')->store('uploads', 'public');
        $data['image'] = $imagepath;
    }

    // Update một phát ăn ngay
    $product->update($data);

    return response()->json([
        'status' => true,
        'message' => 'Update thành công',
        'data' => $product
    ], 200);
}
    public function destroy($id){
        $product=Product::find($id);
        if(!$product){
            return response()->json([
                'status'=>false,
                'message'=>'khong tim thay'
            ],404);
        }
        $product->delete();
        return response()->json([
            'status'=>true,
            'message'=>'xoa thanh cong',
            'data'=>$product
        ],200);

    }
    public function toggleWishlist(Request $request){
        $user=$request->user();
        $product_id=$request->product_id;
        $wishlist=Wishlist::where('user_id',$user->id)->where('product_id',$product_id)->first();
        if($wishlist){
            $wishlist->delete();
            return response()->json([
                'status' =>'removed',
                'message'=>'removed from wishlist']);
        } else{
            Wishlist::Create([
                'user_id'=>$user->id,
                'product_id'=>$product_id
            ]);
            return response()->json([
                'status'=>'added',
                'message'=>'add to wishlist'
            ]);
        }
    }

    public function StoreComment(Request $request){
        $request->validate([
            'content'=>"required",
            'rating'=>"required|integer|min:1|max:5",
            'product_id'=>"required|exists:products,id"
        ]);
        $comment=Comment::create([
            'user_id'=>$request->user()->id,
            'product_id'=>$request->product_id,
            'content'=>$request->content,
            'rating'=>$request->rating
        ]);
        return response()->json($comment->load('user'));
    }
    // Route::get('/wishlist', [ProductController::class, 'getWishlist'])->middleware('auth:sanctum');

public function getWishlist(Request $request) {
    $user = $request->user();
    
    // Lấy wishlist kèm thông tin sản phẩm
    $wishlists = \App\Models\Wishlist::where('user_id', $user->id)
                    ->with('product') // Quan trọng: Phải load thông tin sản phẩm
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
    return response()->json([
        'status' => true,
        'data' => $wishlists
    ]);
}
}