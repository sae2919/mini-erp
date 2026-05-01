@extends('layouts.app')
@section('title', 'Edit Category')
@section('heading', 'Edit Category')
@section('header-actions')
    <a href="{{ route('categories.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-lg">
<form method="POST" action="{{ route('categories.update', $category) }}">
@csrf @method('PUT')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                          @error('name') border-red-400 @enderror">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-600">
            This category has <strong>{{ $category->products()->count() }}</strong> product(s) assigned to it.
        </div>

    </div>
    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('categories.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
            Update Category
        </button>
    </div>
</div>
</form>
</div>
@endsection
