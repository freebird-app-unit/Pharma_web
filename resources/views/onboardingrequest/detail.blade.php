@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-sm-12">
		<h4 class="page-title">{{ $page_title }}</h4>
			<ol class="breadcrumb">
				<li><a href="{{ url('/') }}">Dashboard</a></li>
				<li class="active">{{ $page_title }}</li>
			</ol>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
	
		<div class="card-box">
			<div class="row">
				<div class="col-sm-6">
					<div>
						Name:&nbsp;&nbsp;<?php echo $onboardingrequest->name; ?><br><br>
						First Name:&nbsp;&nbsp;<?php echo $onboardingrequest->first_name; ?><br><br>
						Last Name:&nbsp;&nbsp;<?php echo $onboardingrequest->last_name; ?><br><br>
						Email:&nbsp;&nbsp;<?php echo $onboardingrequest->email; ?><br><br>
						Phone:&nbsp;&nbsp;<?php echo $onboardingrequest->phone; ?><br><br>
						Address:&nbsp;&nbsp;<?php echo $onboardingrequest->address; ?><br><br>
						City:&nbsp;&nbsp;<?php echo $onboardingrequest->city; ?><br><br>
						State:&nbsp;&nbsp;<?php echo $onboardingrequest->state; ?><br><br>
					</div>
					<br><br>
					<div>
						
					</div>
				</div>
				<div class="col-sm-6">
					<div>
						Country:&nbsp;&nbsp;<?php echo $onboardingrequest->country; ?><br><br>
						Pincode:&nbsp;&nbsp;<?php echo $onboardingrequest->pincode; ?><br><br>
						<?php 
						if($onboardingrequest->name_of_partner!=''){
						?>
						Partner:&nbsp;&nbsp;<?php echo $onboardingrequest->name_of_partner; ?><br><br>
						<?php
						}
						?>
						Open Time:&nbsp;&nbsp;<?php echo $onboardingrequest->open_time; ?><br><br>
						Close Time:&nbsp;&nbsp;<?php echo $onboardingrequest->close_time; ?><br><br>
						Provide Delivery:&nbsp;&nbsp;<?php echo ($onboardingrequest->provide_delivery == 1)?'Yes':'No'; ?><br><br>
						<?php 
						if($onboardingrequest->provide_delivery == 1){
						?>
						Delivery Range:&nbsp;&nbsp;<?php echo $onboardingrequest->delivery_range; ?><br><br>
						<?php
						}
						?>
						Sunday Open:&nbsp;&nbsp;<?php echo ($onboardingrequest->sunday_open == 1)?'Yes':'No'; ?><br><br>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-6">
					<div>
						<strong>Adhar of the registering person</strong><br><br>
						<?php
							$adhar_card_url = '';
							$adhar_card_path = storage_path('app/public/uploads/new_pharmacy/'.$onboardingrequest->adhar_card);
							$adhar_card_path = str_replace('\pharma\\','\\',$adhar_card_path);
							if($onboardingrequest->adhar_card!=''){
								if (file_exists($adhar_card_path)){
									$adhar_card_url = asset('storage/app/public/uploads/new_pharmacy/' . $onboardingrequest->adhar_card);
									$adhar_card_url = str_replace('/pharma/','/',$adhar_card_url);
								}else{
									$adhar_card_url = url('/').'/uploads/placeholder.png';
								}
							}else{
								$adhar_card_url = url('/').'/uploads/placeholder.png';
							}
						?>
						<img src="<?php echo $adhar_card_url; ?>" width=""/>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection