@extends('layouts.app')
@section('content')
    <h1>Таблица лидеров</h1>
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background: #112d63; color: #ffcc00;">
                <th style="padding: 10px;">Место</th>
                <th style="padding: 10px;">Игрок</th>
                <th style="padding: 10px;">Время</th>
                <th style="padding: 10px;">Кольца</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaders as $index => $leader)
                <tr style="background: {{ $index % 2 == 0 ? '#1a3366' : '#112d63' }};">
                    <td style="padding: 10px; text-align: center;">{{ $index + 1 }}</td>
                    <td style="padding: 10px; display: flex; align-items: center; gap: 10px;">
                        <!-- Аватарка с цветной рамкой -->
                        <img src="/images/{{ $leader->avatar }}" style="width: 40px; height: 40px; border-radius: 5px; border: 3px solid {{ $leader->color }};">
                        <!-- Ник с цветом -->
                        <span style="color: {{ $leader->color }}; font-weight: bold;">{{ $leader->login }}</span>
                    </td>
                    <td style="padding: 10px; color: #00ff00; font-weight: bold;">{{ $leader->best_time }} сек</td>
                    <td style="padding: 10px; color: #ffcc00;">{{ $leader->total_rings }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="padding: 20px;">Пока никто не прошел игру.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection