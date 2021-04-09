<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TimeBlockRequest;
use App\Models\Category;
use App\Models\TimeBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TimeBlockResource;

class TimeBlockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $timeBlocks = TimeBlock::forUser(Auth::id())
                ->with('user:id,name')
                ->with('category:id,name')
                ->orderBy('block_date', 'desc')->get();
        return response([ 
            'timeBlocks' => TimeBlockResource::collection($timeBlocks), 
            'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TimeBlockRequest $request, Category $category)
    {
        $data = $request->only('description', 'block_length', 'block_date');
        $data['user_id'] = Auth::id();
        $data['category_id'] = $category->id;
        
        if ($category->user_id != Auth::id()) {
            abort(403);
        }
        
        $timeBlock = TimeBlock::create($data);
        return response([
            'timeBlock' => new TimeBlockResource($timeBlock), 
            'message' => 'Created successfully'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TimeBlock  $timeBlock
     * @return \Illuminate\Http\Response
     */
    public function show(TimeBlock $timeBlock)
    {
        if ($timeBlock->user_id != Auth::id()) {
            abort(404);
        }
        $timeBlock->load(['user:id,name', 'category:id,name']);
        return response([
            'timeBlock' => new TimeBlockResource($timeBlock), 
            'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TimeBlock  $timeBlock
     * @return \Illuminate\Http\Response
     */
    public function update(TimeBlockRequest $request, TimeBlock $timeBlock)
    {
        if ($timeBlock->user_id != Auth::id()) {
            abort(404);
        }
        if ($request->has('category_id')) {
            $category = Category::forUser(Auth::id())
                    ->findOrFail($request->input('category_id'));
            $timeBlock->update(['category_id' => $category->id]);
        }
        $timeBlock->update($request->only(
                'description', 'block_length', 'block_date'));
        return response([
            'timeBlock' => new TimeBlockResource($timeBlock), 
            'message' => 'Update successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TimeBlock  $timeBlock
     * @return \Illuminate\Http\Response
     */
    public function destroy(TimeBlock $timeBlock)
    {
        if ($timeBlock->user_id != Auth::id()) {
            abort(404);
        }
        $timeBlock->delete();
        return response(['message' => 'Deleted']);
    }
}
