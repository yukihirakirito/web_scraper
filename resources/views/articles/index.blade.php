@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Tin Tức Bóng Đá</h1>

    <form method="GET" action="{{ route('articles.index') }}" class="mb-4">
        <input type="text" name="search" placeholder="Tìm kiếm bài báo..." class="border p-2 rounded w-full" value="{{ request('search') }}">
    </form>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($articles as $article)
            <div class="border p-4 rounded shadow-lg">
                <h2 class="text-lg font-semibold">{{ $article->title }}</h2>
                <p class="text-sm text-gray-500">Từ khóa: {{ implode(', ', json_decode($article->keywords, true)) }}</p>
                <a href="{{ $article->url }}" target="_blank" class="text-blue-500">Xem bài báo</a>
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $articles->links() }} <!-- Phân trang -->
    </div>
</div>
@endsection
