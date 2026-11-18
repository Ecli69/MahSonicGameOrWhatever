@extends('layouts.app')
@section('content')
    <h1>Green Hill Zone</h1>
    <p>Стрелки / A, D — бег. Пробел — прыжок. Вниз + Пробел (стоя) — Спин-дэш. Собери кольца и добеги до флага!</p>
    
    <div style="text-align: center;">
        <canvas id="gameCanvas" width="900" height="450" style="border: 3px solid #ffcc00; background: #5c94fc; border-radius: 10px;"></canvas>
        <p>Нажми <b>Enter</b>, чтобы начать</p>
    </div>
    <script src="{{ asset('js/game.js') }}"></script>
@endsection

{{-- Добавляем CSRF-токен в head (через секцию, если она есть, или просто перед скриптом) --}}
@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

{{-- Подключаем скрипт --}}
@push('scripts')
    <script src="{{ asset('js/game.js') }}"></script>
@endpush