<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::forUser(Auth::id())->with('user:id,name')->orderBy('name')->get();
        
        return response([ 
            'categories' => CategoryResource::collection($categories), 
            'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        $data = $request->only('name', 'target_percentage');
        $data['user_id'] = Auth::id();
        if (CategoryService::checkCategoryNameExists(
                $data['user_id'], $data['name'])) {
            return response([
                'message' => 'Категория с указанным именем уже существует'], 400);
        }
        $category = Category::create($data);
        return response([
            'category' => new CategoryResource($category), 
            'message' => 'Created successfully'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $category->load(['user:id,name']);
        if ($category->user_id != Auth::id()) {
            abort(404);
        }
        return response([
            'category' => new CategoryResource($category), 
            'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryRequest $request, Category $category)
    {
        if ($category->user_id != Auth::id()) {
            abort(404);
        }

        if ($request->has('name') 
                && ($request->input('name') !== $category->name)) {                
            $isNameExists = CategoryService::checkCategoryNameExists(
                    $category->user_id, $request->input('name'));
            if ($isNameExists) {
                return response([
                    'message' => 'Категория с указанным именем уже существует'], 400);
            }
        }
        
        $category->update($request->only('name', 'target_percentage'));
        return response([
            'category' => new CategoryResource($category), 
            'message' => 'Update successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        if ($category->user_id != Auth::id()) {
            abort(404);
        }
        try {
            CategoryService::deleteCategory($category);
        } catch (\App\Exceptions\RemovingCategoryHasChildTimeBlocksException $e) {
            return response([
                'message' => 'Удаление категории невозможно, так как существуют зависимые от нее записи журнала'], 403);
        }
        
        return response(['message' => 'Deleted'], 200);
    }
}
