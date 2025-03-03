<?php

namespace App\Http\Controllers\Admin\ContentManagement\Blog;

use App\Http\Controllers\Admin\Controller;

class CategoryBlogController extends Controller
{
 public function index()
 {
  return view('admin.content.content-management.category-blog.index');
 }

 public function create()
 {
  return view('admin.content.content-management.category-blog.create');
 }

 public function edit($id)
 {
  return view('admin.content.content-management.category-blog.edit', compact('id'));
 }
}