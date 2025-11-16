<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Baptism Certificate</title>
    <style>
        @page { margin: 0; }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-image: url('{{ $background }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            position: relative;
            height: 100%;
            width: 100%;
        }

        .overlay {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .name {
            position: absolute;
            top: 27%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 45pt;
            font-weight: bold;
            color: red;
            text-align: center;
        }

        .salvation-date {
            position: absolute;
            top: 54%;
            left: 34%;
            transform: translate(-50%, -50%);
            font-size: 25pt;
             color: blue;
        }

        .baptism-date {
            position: absolute;
            top: 54%;
            left: 67%;
            transform: translate(-50%, -50%);
            font-size: 25pt;
            color: blue;
        }
    </style>
</head>
<body>
    <div class="overlay">
        <div class="name">{{ $name }}</div>
        <div class="salvation-date">{{ \Carbon\Carbon::parse($salvation_date)->format('F d, Y') }}</div>
        <div class="baptism-date">{{ \Carbon\Carbon::parse($baptism_date)->format('F d, Y') }}</div>
    </div>
</body>
</html>
