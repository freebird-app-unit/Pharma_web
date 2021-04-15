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
				<div class="col-sm-6">
					<div>
						<strong>Pan card of the registering person</strong><br><br>
						<?php
							$pan_card_url = '';
							$pan_card_path = storage_path('app/public/uploads/new_pharmacy/'.$onboardingrequest->pan_card);
							$pan_card_path = str_replace('\pharma\\','\\',$pan_card_path);
							if($onboardingrequest->pan_card!=''){
								if (file_exists($pan_card_path)){
									$pan_card_url = asset('storage/app/public/uploads/new_pharmacy/' . $onboardingrequest->pan_card);
									$pan_card_url = str_replace('/pharma/','/',$pan_card_url);
								}else{
									$pan_card_url = url('/').'/uploads/placeholder.png';
								}
							}else{
								$pan_card_url = url('/').'/uploads/placeholder.png';
							}
						?>
						<img src="<?php echo $pan_card_url; ?>"/>
					</div>
				</div>
				<div class="col-sm-6">
					<div>
						<strong>Photo</strong><br><br>
						<?php
							$photo_url = '';
							$photo_path = storage_path('app/public/uploads/new_pharmacy/'.$onboardingrequest->photo);
							$photo_path = str_replace('\pharma\\','\\',$photo_path);
							if($onboardingrequest->photo!=''){
								if (file_exists($photo_path)){
									$photo_url = asset('storage/app/public/uploads/new_pharmacy/' . $onboardingrequest->photo);
									$photo_url = str_replace('/pharma/','/',$photo_url);
								}else{
									$photo_url = url('/').'/uploads/placeholder.png';
								}
							}else{
								$photo_url = url('/').'/uploads/placeholder.png';
							}
						?>
						<img src="<?php echo $photo_url; ?>"/>
					</div>
				</div>
				<div class="col-sm-6">
					<div>
						<strong>Drug License</strong><br><br>
						<?php
							$drug_license_url = '';
							$drug_license_path = storage_path('app/public/uploads/new_pharmacy/'.$onboardingrequest->drug_license);
							$drug_license_path = str_replace('\pharma\\','\\',$drug_license_path);
							if($onboardingrequest->drug_license!=''){
								if (file_exists($drug_license_path)){
									$drug_license_url = asset('storage/app/public/uploads/new_pharmacy/' . $onboardingrequest->drug_license);
									$drug_license_url = str_replace('/pharma/','/',$drug_license_url);
								}else{
									$drug_license_url = url('/').'/uploads/placeholder.png';
								}
							}else{
								$drug_license_url = url('/').'/uploads/placeholder.png';
							}
						?>
						<img src="<?php echo $drug_license_url; ?>"/>
					</div>
				</div>
				<?php 
				if($onboardingrequest->propreitor == 0){
				?>
				<div class="col-sm-6">
					<div>
						<strong>Partnership deed</strong><br><br>
						<?php
							$partnership_deed_url = '';
							$partnership_deed_path = storage_path('app/public/uploads/new_pharmacy/'.$onboardingrequest->partnership_deed);
							$partnership_deed_path = str_replace('\pharma\\','\\',$partnership_deed_path);
							if($onboardingrequest->partnership_deed!=''){
								if (file_exists($partnership_deed_path)){
									$partnership_deed_url = asset('storage/app/public/uploads/new_pharmacy/' . $onboardingrequest->partnership_deed);
									$partnership_deed_url = str_replace('/pharma/','/',$partnership_deed_url);
								}else{
									$partnership_deed_url = url('/').'/uploads/placeholder.png';
								}
							}else{
								$partnership_deed_url = url('/').'/uploads/placeholder.png';
							}
						?>
						<img src="<?php echo $partnership_deed_url; ?>"/>
					</div>
				</div>
				<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
@endsection