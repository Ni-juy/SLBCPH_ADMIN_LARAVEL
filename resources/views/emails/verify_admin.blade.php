<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify Your Admin Account</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
   <h2>Hello {{ $admin->first_name }} {{ $admin->last_name }},</h2>

<p>Thank you for registering as an Admin. Please verify your email to activate your account.</p>

<p style="margin: 20px 0;">
    <a href="{{ $verifyUrl }}" 
       style="display:inline-block; padding:12px 25px; background-color:#2d89ef; color:#fff; 
              text-decoration:none; border-radius:5px; font-weight:bold;">
        Verify My Account
    </a>
</p>

<p>If you did not create this account, please ignore this email.</p>

    <hr style="margin: 20px 0;">

    <p>Thank you,<br>
    Church Management System</p>
</body>
</html>
