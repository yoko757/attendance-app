<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>マイ勤怠レポート</title>
</head>
<body>
    <h1>マイ勤怠レポート</h1>
    <h2>過去6か月の勤怠記録</h2>
    @php
    $monthlyReports = [];

    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i)->format('Y-m');

        $monthlyReports[$month] = [
            'total_minutes' => 0,
            'overtime_minutes' => 0,
            'work_days' => 0,
            'average_minutes' => 0,
            'late_count' => 0,
            'early_count' => 0,
            'long_work_count' => 0,
        ];
    }

    foreach ($attendances as $attendance) {
        if (!$attendance->clock_in || !$attendance->clock_out) {
            continue;
        }

        $month = \Carbon\Carbon::parse($attendance->date)->format('Y-m');

        if (!isset($monthlyReports[$month])) {
            continue;
        }

        $minutes = max(0, $attendance->total_time);

        $monthlyReports[$month]['total_minutes'] += $minutes;
        $monthlyReports[$month]['overtime_minutes'] += max(0, $minutes - 480);
        $monthlyReports[$month]['work_days']++;
        // 9:00より後に出勤したら遅刻
        if (\Carbon\Carbon::parse($attendance->clock_in)->format('H:i:s') > '09:00:00') {
    $monthlyReports[$month]['late_count']++;
}

// 18:00より前に退勤したら早退
        if (\Carbon\Carbon::parse($attendance->clock_out)->format('H:i:s') < '18:00:00') {
    $monthlyReports[$month]['early_count']++;
}

// 実働時間が10時間を超えたら長時間勤務
        if ($minutes > 600) {
    $monthlyReports[$month]['long_work_count']++;
}
    }

    foreach ($monthlyReports as &$report) {
        if ($report['work_days'] > 0) {
            $report['average_minutes'] = (int) round(
                $report['total_minutes'] / $report['work_days']
            );
        }
    }
    unset($report);
@endphp
<h2>月別勤怠集計</h2>

<table border="1" cellpadding="10">
    <thead>
        <tr>
            <th>年月</th>
            <th>総勤務時間</th>
            <th>残業時間</th>
            <th>平均勤務時間</th>
            <th>遅刻回数</th>
            <th>早退回数</th>
            <th>長時間勤務回数</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($monthlyReports as $month => $report)
            <tr>
                <td>{{ $month }}</td>

                <td>
                    {{ sprintf('%d:%02d',
                        intdiv($report['total_minutes'], 60),
                        $report['total_minutes'] % 60) }}
                </td>

                <td>
                    {{ sprintf('%d:%02d',
                        intdiv($report['overtime_minutes'], 60),
                        $report['overtime_minutes'] % 60) }}
                </td>

                <td>
                    {{ sprintf('%d:%02d',
                        intdiv($report['average_minutes'], 60),
                        $report['average_minutes'] % 60) }}
                </td>
                <td>{{ $report['late_count'] }}</td>
                <td>{{ $report['early_count'] }}</td>
                <td>{{ $report['long_work_count'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>勤務時間</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->date }}</td>
                    <td>{{ $attendance->clock_in }}</td>
                    <td>{{ $attendance->clock_out }}</td>
                    <td>
                        {{ sprintf('%d:%02d', intdiv($attendance->total_break_time, 60), $attendance->total_break_time % 60) }}
                    </td>
                    <td>
                        {{ sprintf('%d:%02d', intdiv($attendance->total_time, 60), $attendance->total_time % 60) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">勤怠記録がありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>