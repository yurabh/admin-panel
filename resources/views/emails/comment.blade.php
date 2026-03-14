<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 5px;
        }

        blockquote {
            background: #f9f9f9;
            border-left: 5px solid #ccc;
            padding: 10px 20px;
            margin: 20px 0;
        }
    </style>
    <title>Hello</title>
</head>
<body>
<div class="container">
    <h2>Новий коментар до вашого поста</h2>
    <p>Користувач <strong>{{ $comment->user->name }}</strong> залишив коментар до поста "{{ $comment->post->title }}":
    </p>

    <blockquote>
        {{ $comment->content }}
    </blockquote>

    <hr>
    <p>Дякуємо,<br>{{ config('app.name') }}</p>
</div>
</body>
</html>
