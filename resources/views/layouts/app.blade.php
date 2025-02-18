<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tin tức Bóng Đá')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-500 text-white p-4">
        <div class="container mx-auto">
            <a href="{{ route('articles.index') }}" class="text-lg font-bold">Trang Chủ</a>
        </div>
    </nav>

    <div class="p-4">
        @yield('content')
    </div>
</body>
</html>
