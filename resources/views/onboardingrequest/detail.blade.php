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
							if($onboardingrequest->adhar_card!=''){
								$adhar_card_url = $onboardingrequest->adhar_card;
							}else{
								$adhar_card_url = url('/').'/uploads/placeholder.png';
							}
						$arr = explode('.',$adhar_card_url);
						$ext = end($arr);
						if($ext=='pdf'){
							$arr2 = explode('/',$adhar_card_url);
							echo '<a href="'.$adhar_card_url.'">'.end($arr2).'</a>';	
						}else{
						?>
						<img src="<?php echo $adhar_card_url; ?>" width=""/>
						<?php 
						}
						?>
					</div>
				</div>
				<div class="col-sm-6">
					<div>
						<strong>Pan card of the registering person</strong><br><br>
						<?php
							$pan_card_url = '';
							if($onboardingrequest->pan_card!=''){
								$pan_card_url = $onboardingrequest->pan_card;
							}else{
								$pan_card_url = url('/').'/uploads/placeholder.png';
							}
						$arr = explode('.',$pan_card_url);
						$ext = end($arr);
						if($ext=='pdf'){
							$arr2 = explode('/',$pan_card_url);
							echo '<a href="'.$pan_card_url.'">'.end($arr2).'</a>';	
						}else{
						?>
						<img src="<?php echo $pan_card_url; ?>"/>
						<?php 
						}
						?>
					</div>
				</div>
				<div class="col-sm-6">
					<div>
						<strong>Photo</strong><br><br>
						<?php
							$photo_url = '';
							if($onboardingrequest->photo!=''){
								$photo_url = $onboardingrequest->photo;
							}else{
								$photo_url = url('/').'/uploads/placeholder.png';
							}
						$arr = explode('.',$photo_url);
						$ext = end($arr);
						if($ext=='pdf'){
							$arr2 = explode('/',$photo_url);
							echo '<a href="'.$photo_url.'">'.end($arr2).'</a>';	
						}else{
						?>
						<img src="<?php echo $photo_url; ?>"/>
						<?php
						}
						?>
					</div>
				</div>
				<div class="col-sm-6">
					<div>
						<strong>Drug License</strong><br><br>
						<?php
							$drug_license_url = '';
							if($onboardingrequest->drug_license!=''){
								$drug_license_url = $onboardingrequest->drug_license;
							}else{
								$drug_license_url = url('/').'/uploads/placeholder.png';
							}
						$arr = explode('.',$drug_license_url);
						$ext = end($arr);
						if($ext=='pdf'){
							$arr2 = explode('/',$drug_license_url);
							echo '<a href="'.$drug_license_url.'">'.end($arr2).'</a>';	
						}else{
						?>
						<img src="<?php echo $drug_license_url; ?>"/>
						<?php
						}
						?>
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
							if($onboardingrequest->partnership_deed!=''){
								$partnership_deed_url = $onboardingrequest->partnership_deed;
							}else{
								$partnership_deed_url = url('/').'/uploads/placeholder.png';
							}
						$arr = explode('.',$partnership_deed_url);
						$ext = end($arr);
						if($ext=='pdf'){
							$arr2 = explode('/',$partnership_deed_url);
							echo '<a href="'.$partnership_deed_url.'">'.end($arr2).'</a>';	
						}else{
						?>
						<img src="<?php echo $partnership_deed_url; ?>"/>
						<?php
						}
						?>
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