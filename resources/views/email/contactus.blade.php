<!doctype html>
<html>
<head>
    
</head>
<body>
	<p>Name : {{ $name }},</p>
	<p>Email Address : {{ $email }},</p> 
	<p>Mobile Number : {{ $mobile_number }},</p>
	<p>Description : {{ $description }},</p>
	<p><a href="{{ $image }}">
	<img src="{{ $image }}" alt="{{ $image }}" width="300" height="300">
	</a></p>
</body>
</html>
