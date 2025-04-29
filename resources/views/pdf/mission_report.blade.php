<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Mission Report</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin: 10px 0; }
        .info strong { width: 150px; display: inline-block; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Mission Report</h2>
    </div>

    <div class="info">
        <strong>Mission Created By:</strong> {{ $data['owner'] }}
    </div>
    <div class="info">
        <strong>Pilot:</strong> {{ $data['pilot'] }}
    </div>
    <div class="info">
        <strong>Region:</strong> {{ $data['region'] }}
    </div>
    <div class="info">
        <strong>Program:</strong> {{ $data['program'] }}
    </div>
    <div class="info">
        <strong>Location:</strong> {{ $data['location'] }}
    </div>
    <div class="info">
        <strong>Geo Coordinates:</strong> {{ $data['geo'] }}
    </div>

</body>
</html>
