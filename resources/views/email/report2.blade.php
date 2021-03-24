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
	<p>Invoice Image </p>
	<p><a href="{{ $inv_image }}">
	<img src="{{ $inv_image }}" alt="{{ $inv_image }}" width="150" height="150">
	</a></p>
	<p>Pickup Image </p>
	<p><a href="{{ $pick_image }}">
	<img src="{{ $pick_image }}" alt="{{ $pick_image }}" width="150" height="150">
	</a></p>
	<p>Deliver Image </p>
	<p><a href="{{ $del_image }}">
	<img src="{{ $del_image }}" alt="{{ $del_image }}" width="150" height="150">
	</a></p>
</body>
</html>
