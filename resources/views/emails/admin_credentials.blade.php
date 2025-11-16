<h1>Hello, {{ $admin->first_name }}!</h1>

<p>Your admin account has been created. Here are your login credentials:</p>

<ul>
    <li><strong>Username:</strong> {{ $admin->username }}</li>
    <li><strong>Password:</strong> {{ $password }}</li>
</ul>

<p>Please log in and change your password immediately.</p>

<p>Thank you!</p>