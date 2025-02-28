<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー登録</title>
</head>
<body>
    <h2>ユーザー登録</h2>

    @if (session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form action="{{ route('register') }}" method="POST">
        @csrf
        <label>名前:</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
        @error('name') <p style="color: red;">{{ $message }}</p> @enderror
        <br>
        <label>メールアドレス:</label>
        <input type="email" name="email" value="{{ old('email') }}">
        @error('email') <p style="color: red;">{{ $message }}</p> @enderror
        <br>
        <label>パスワード:</label>
        <input type="password" name="password" required>
        @error('password') <p style="color: red;">{{ $message }}</p> @enderror
        <br>
        <label>パスワード確認:</label>
        <input type="password" name="password_confirmation" required>

        <button type="submit">登録</button>
    </form>
</body>
</html>
