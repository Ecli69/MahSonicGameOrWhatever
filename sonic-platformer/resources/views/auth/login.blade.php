@extends('layouts.app')
@section('content')
    <h1>Вход</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div><input type="text" name="login" placeholder="Логин" required></div>
        <div><input type="password" name="password" placeholder="Пароль" required></div>
        @error('login') <p style="color:red">{{ $message }}</p> @enderror
        <button type="submit">Войти</button>
    </form>
@endsection