<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\load;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(Request $request) 
{
    // 1. Khởi tạo query
    $query = Product::with('category'); 

    // 2. Lọc theo Danh mục
    if ($request->has('category_id')) {
        $query->where('category_id', $request->category_id);
    }

    // 3. Tìm kiếm theo tên
    if ($request->has('keyword')) {
        $query->where('name', 'LIKE', '%' . $request->keyword . '%');
    }

    // --- 4. ĐOẠN BẠN ĐANG THIẾU ĐÂY (LỌC GIÁ) ---
    if ($request->has('price_min')) {
        $query->where('price', '>=', $request->price_min);
    }
    
    if ($request->has('price_max')) {
        $query->where('price', '<=', $request->price_max);
    }
    // ----------------------------------------------

    // 5. Sắp xếp và Phân trang
    $query->orderBy('created_at', 'desc');
    $products = $query->paginate(10); 

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
             'description'=>'nullable|string',
             'category_id'=>'required|exists:categories,id',
             'image'=>'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
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
            $imagepath=$request->file('image')->store('upload','public');
        }
        $product=Product::create([
            'name'=>$request->name,
            'price'=>$request->price,
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
        $product->load('category');
        
        return response()->json([
            'status'=>true,
            'message'=>'success',
            'data'=>$product
        ],200);
    }
    public function update($id, Request $request) {
    $product = Product::find($id);
    if (!$product) {
        return response()->json(['status' => false, 'message' => 'Không tìm thấy'], 404);
    }

    $validation = Validator::make($request->all(), [
        'name' => 'sometimes|string|max:255',
        'price' => 'sometimes|numeric|min:0',
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

}