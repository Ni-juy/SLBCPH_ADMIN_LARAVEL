<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Verification Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 450px;
            width: 100%;
        }
        h2 {
            color: #2d89ef;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #333;
        }
        ul {
            list-style: none;
            padding: 0;
            margin-bottom: 20px;
        }
        ul li {
            font-size: 15px;
            margin: 5px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #2d89ef;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #1b5fbd;
        }
        .error {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
    @if($success)
        <h2>🎉 Email Verified!</h2>
        <p>{{ $message }}</p>

        @if(isset($user) && isset($password))
            <p>Here are your login credentials:</p>
            <ul>
                <li><strong>Username:</strong> {{ $user->username }}</li>
                <li><strong>Password:</strong> {{ $password }}</li>
            </ul>
            <p style="color:#e74c3c; font-weight:bold;">
                ⚠️ Important: Please take a screenshot or write down your credentials now. 
                Once you leave this page, you will not be able to view them again.
            </p>
        @endif

        <a href="http://localhost:5173/login" class="btn">Go to Login</a>
    @else
        <h2 class="error">Email Verification Failed</h2>
        <p>{{ $message }}</p>
    @endif
</div>

</body>
</html>
