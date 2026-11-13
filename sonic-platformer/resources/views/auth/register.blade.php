@extends('layouts.app')
@section('content')
    <h1>Регистрация</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div><input type="text" name="login" placeholder="Логин" required></div>
        <div><input type="password" name="password" placeholder="Пароль" required></div>
        <div><input type="password" name="password_confirmation" placeholder="Повторите пароль" required></div>
        @error('login') <p style="color:red">{{ $message }}</p> @enderror
        @error('password') <p style="color:red">{{ $message }}</p> @enderror
        <button type="submit">Зарегистрироваться</button>
    </form>
@endsection