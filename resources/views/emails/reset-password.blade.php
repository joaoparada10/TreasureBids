<h3>Hi {{ $mailData['name'] ?? 'User' }},</h3>
<h4>Welcome to TreasureBids</h4>
<h5>Your password has been reset. Please use the following password to log in:</h5>
<p><strong>Password:</strong> {{ $mailData['password'] }}</p>
<h5>--- TreasureBids Staff ---</h5>