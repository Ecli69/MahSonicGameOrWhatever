<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Соник: Платформер</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #0b1c3c; color: white; text-align: center; margin: 0; padding: 0; }
        nav { background: #112d63; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; text-decoration: none; margin: 0 15px; font-weight: bold; }
        nav a:hover { color: #ffcc00; }
        .container { max-width: 900px; margin: 20px auto; padding: 20px; background: rgba(255,255,255,0.1); border-radius: 15px; }
        input, button { padding: 10px; margin: 5px; border-radius: 5px; border: none; font-size: 16px; }
        button { cursor: pointer; background: #ffcc00; color: #000; font-weight: bold; }
        button:hover { background: #ffdd44; }
    </style>
</head>
<body>
    <nav>
        <div>
            <a href="{{ route('game.index') }}">Играть</a>
            <a href="{{ route('game.leaderboard') }}">Лидеры</a>
        </div>
        <div>
            @auth
                <a href="{{ route('account.index') }}">Аккаунт</a>
                <span style="margin-right: 15px;">Игрок: <strong>{{ auth()->user()->login }}</strong> | Рекорд: {{ auth()->user()->best_time ? auth()->user()->best_time . 'с' : 'Нет' }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" style="background: #ff4444; color: white; padding: 5px 10px;">Выйти</button>
                </form>
            @else
                <a href="{{ route('login') }}">Вход</a>
                <a href="{{ route('register') }}">Регистрация</a>
            @endauth
        </div>
    </nav>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>