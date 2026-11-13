@extends('layouts.app')

@section('content')
    <h1>Личный кабинет</h1>

    @if(session('success'))
        <p style="color: lightgreen;">{{ session('success') }}</p>
    @endif

    <div style="display: flex; gap: 40px;">
        <!-- Форма настроек -->
        <form action="{{ route('account.update') }}" method="POST" style="flex: 1; background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
            @csrf
            <h2>Настройки профиля</h2>
            
            <p><strong>Логин:</strong> {{ auth()->user()->login }}</p>
            <p><strong>Рекорд:</strong> {{ auth()->user()->best_time ? auth()->user()->best_time . ' сек' : 'Нет' }}</p>

            <div style="margin-top: 20px;">
                <label>Выберите аватарку:</label>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 10px;">
                    @for($i = 1; $i <= 8; $i++)
                        <label style="cursor: pointer;">
                            <input type="radio" name="avatar" value="icon{{ $i }}.png" 
                                {{ auth()->user()->avatar == "icon{$i}.png" ? 'checked' : '' }}>
                            <img src="/images/icon{{ $i }}.png" style="width: 50px; height: 50px; border-radius: 5px;">
                        </label>
                    @endfor
                </div>
            </div>

            <div style="margin-top: 20px;">
                <label for="color">Выберите цвет (ник и рамка):</label>
                <input type="color" name="color" id="color" value="{{ auth()->user()->color }}">
            </div>

            <button type="submit" style="margin-top: 20px;">Сохранить</button>
        </form>

        <!-- Удаление рекорда -->
        <div style="flex: 1; background: rgba(255,0,0,0.2); padding: 20px; border-radius: 10px; border: 1px solid #ff4444;">
            <h2>Опасная зона</h2>
            <p>Удаление рекорда обнулит ваше лучшее время и общее количество колец.</p>
            <form action="{{ route('account.deleteRecord') }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить свой рекорд? Это действие нельзя отменить.');">
                @csrf
                <button type="submit" style="background: #ff4444; color: white;">Удалить мой рекорд</button>
            </form>
        </div>
    </div>
@endsection