<!doctype html>
<html>
<head>
    
</head>
<body>
	<p>Name : {{ $name }},</p>
	<p>Email Address : {{ $email }},</p> 
	<p>Mobile Number : {{ $mobile_number }},</p>
	<p>Description : {{ $description }},</p>
	<p>Image </p>
	<p><a href="{{ $image }}">
	<img src="{{ $image }}" alt="{{ $image }}" width="150" height="150">
	</a></p>
	<p>Prescription Image </p>
	<p><a href="{{ $pre_image }}">
	<img src="{{ $pre_image }}" alt="{{ $pre_image }}" width="150" height="150">
	</a></p>
</body>
</html>
