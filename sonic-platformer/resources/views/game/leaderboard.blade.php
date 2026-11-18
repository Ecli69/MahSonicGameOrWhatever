@extends('layouts.app')
@section('content')
    <h1>🏆 Таблица лидеров</h1>
    
    <!-- Фильтры -->
    <div style="margin-bottom: 20px; background: #112d63; padding: 10px; border-radius: 5px; display: flex; gap: 10px; align-items: center;">
        <strong>Сортировать по:</strong>
        <a href="{{ route('game.leaderboard', ['sort' => 'time']) }}" style="color: {{ $sort == 'time' ? '#ffcc00' : '#fff' }}; text-decoration: none;">Времени (быстрее)</a> |
        <a href="{{ route('game.leaderboard', ['sort' => 'rings']) }}" style="color: {{ $sort == 'rings' ? '#ffcc00' : '#fff' }}; text-decoration: none;">Кольцам (больше)</a> |
        <a href="{{ route('game.leaderboard', ['sort' => 'attempts']) }}" style="color: {{ $sort == 'attempts' ? '#ffcc00' : '#fff' }}; text-decoration: none;">Попыткам (больше)</a>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #112d63; color: #ffcc00;">
                <th style="padding: 10px;">Место</th>
                <th style="padding: 10px;">Игрок</th>
                <th style="padding: 10px;">Время</th>
                <th style="padding: 10px;">Кольца</th>
                <th style="padding: 10px;">Попытки</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaders as $index => $leader)
                <tr style="background: {{ $index % 2 == 0 ? '#1a3366' : '#112d63' }};">
                    <td style="padding: 10px; text-align: center;">{{ $index + 1 }}</td>
                    <td style="padding: 10px; display: flex; align-items: center; gap: 10px;">
                        <img src="/images/{{ $leader->avatar }}" style="width: 40px; height: 40px; border-radius: 5px; border: 3px solid {{ $leader->color }};">
                        <span style="color: {{ $leader->color }}; font-weight: bold;">{{ $leader->login }}</span>
                    </td>
                    <td style="padding: 10px; color: #00ff00; font-weight: bold;">{{ $leader->best_time }} с</td>
                    <td style="padding: 10px; color: #ffcc00;">{{ $leader->total_rings }}</td>
                    <td style="padding: 10px; color: #fff;">{{ $leader->attempts }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding: 20px; text-align: center;">Пока нет результатов.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection