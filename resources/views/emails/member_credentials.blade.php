<h2>Welcome, {{ $member->first_name }}!</h2>

<p>Your church member account has been created. Here are your login credentials:</p>

<ul>
    <li>Username: <strong>{{ $member->username }}</strong></li>
    <li>Password: <strong>{{ $password }}</strong></li>
</ul>

<p>Please log in and change your password immediately.</p>
<p>Thank you!</p>
