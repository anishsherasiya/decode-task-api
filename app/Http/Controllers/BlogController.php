<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogImage;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BlogController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $blogs = Blog::whereUserId(Auth::id())->get();
        return $this->sendResponse($blogs, "Blog fetched successfully.");
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|unique:blogs,title',
            'description' => 'required|min:10',
            'images.*'    => 'image|mimes:jpg,png|max:5120',
        ]);
        
        if ($validator->fails()) {
            return $this->sendError('Blog Create Error.', $validator->errors());     
        }   

        if (Auth::user()->credits == 0) {
            return $this->sendError("You need credits to create a blog.", [], 403);
        }
        
        try {
            $blog = Blog::create([
                'user_id'    => Auth::id(),
                'title'      => $request->title,
                'description'=> $request->description,
            ]);
    
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('blog_images', 'public');
                    BlogImage::create([
                        'blog_id' => $blog->id,
                        'image_path' => $path
                    ]);
                }
            }

            Purchase::create([
                'user_id'   => Auth::id(),
                'amount'    => 1,
                'type' => 'debit',
            ]); 

            $user = User::findOrFail(Auth::id());
            $user->decrement('credits', 1);
            
        } catch (\Throwable $e) {
            return $this->sendError("Blog creation failed.", $e->getMessage(), 500);
        }

        return $this->sendResponse($blog, "Blog created successfully");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $blog = Blog::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$blog) {
            return $this->sendError('Blog not found');
        }
        return $this->sendResponse($blog, "Blog fetched successfully.");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $blog = Blog::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$blog) {
            return $this->sendError('Blog not found');
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'required|unique:blogs,title,' . $id,
            'description' => 'required|min:10',
            'images.*'    => 'sometimes|image|mimes:jpg,png|max:5120',
        ]);
        
        if ($validator->fails()) {
            return $this->sendError('Blog Update Error.', $validator->errors());
        }

        try{
            if ($request->hasFile('images')) {
                foreach ($blog->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }

                foreach ($request->file('images') as $image) {
                    $path = $image->store('blog_images', 'public');
                    BlogImage::create([
                        'blog_id'    => $blog->id,
                        'image_path' => $path,
                    ]);
                }
            }
            $blog->title = $request->title;
            $blog->description = $request->description;
            $blog->save();
        } catch (\Throwable $e) {
            return $this->sendError("Blog update failed.", $e->getMessage());
        }

        return $this->sendResponse($blog, "Blog updated successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $blog = Blog::with('images')->where('id', $id)->where('user_id', Auth::id())->first();
        if (!$blog) {
            return response()->json(['error' => 'Blog not found'], 404);
        }

        $BlogImages = BlogImage::where('blog_id', $blog->id)->get();
        if($BlogImages) {
            foreach ($BlogImages as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        $blog->delete();
        return $this->sendResponse($blog, "Blog deleted successfully");
    }
}
