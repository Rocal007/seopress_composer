<?php 
	 $inhaber = get_field_object("inhaber", get_option('page_on_front')); 
	 $strasse = get_field_object("strasse", get_option('page_on_front'));
     $plz = get_field_object("plz", get_option('page_on_front'));
     $homepage = get_field_object("homepage", get_option('page_on_front'));
     $email = get_field_object("e-mail", get_option('page_on_front'));
	 $phone_number = get_field_object("angezeigte_telefonnummer", get_option('page_on_front'));
	 $gerichtsstand = get_field_object("gerichtsstand", get_option('page_on_front'));
	 $gln = get_field_object("gln", get_option('page_on_front'));
	 $uid = get_field_object("uid", get_option('page_on_front'));
	 $berechtigungen = get_field_object("berechtigungen", get_option('page_on_front'));
	 $urheberrecht = get_field_object("urheberrecht", get_option('page_on_front'));
	 $blog_title = get_bloginfo( 'name' ); 
?>
<div class="container">
	<div class="row impressum">
		<h1>Impressum von <?php echo $blog_title; ?></h1>
	
				<div class="col-md-6">


							<div class="col-md-4">
								<?php echo $inhaber['label']; ?>:
							</div>
							<div class="col-md-8">
								<?php echo $inhaber['value']; ?>
							</div>

							<div class="col-md-4">
								<?php echo $strasse['label']; ?>:
							</div>
							<div class="col-md-8">
								<?php echo $strasse['value']; ?>
							</div>

							<div class="col-md-4">
								<?php echo $plz['label']; ?>:
							</div>
							<div class="col-md-8">
								<?php echo $plz['value']; ?>
							</div>

							<div class="col-md-4">
								Telefonnummer:
							</div>
							<div class="col-md-8">
								<?php echo $phone_number['value']; ?>
							</div>

							
							<div class="col-md-4">
								<?php echo $homepage['label']; ?>:
							</div>
							<div class="col-md-8">
								<?php echo $homepage['value']; ?>
							</div>

				</div>
				<div class="col-md-6">

							<div class="col-md-4">
								<?php echo $gerichtsstand['label']; ?>:
							</div>
							<div class="col-md-8">
								<?php echo $gerichtsstand['value']; ?>
							</div>

							<div class="col-md-4">
								<?php echo $gln['label']; ?>:
							</div>
							<div class="col-md-8">
								<?php echo $gln['value']; ?>
							</div>

							<div class="col-md-4">
								<?php echo $uid['label']; ?>:
							</div>
							<div class="col-md-8">
								<?php echo $uid['value']; ?>
							</div>
					
							<div class="col-md-4">
								<?php echo $email['label']; ?>:
							</div>
							<div class="col-md-8">
								<?php echo $email['value']; ?>
							</div>

				</div>
	</div>
	
	<div class="row impressum">
		<div class="col-md-12">
							<div class="col-md-2">
								<?php echo $berechtigungen['label']; ?>:
							</div>
							<div class="col-md-10">
								<?php echo $berechtigungen['value']; ?>
							</div>
		</div>
	</div>
	
	<div class="row impressum">
		<div class="col-md-12">
							<div class="col-md-2">
								<?php echo $urheberrecht['label']; ?>:
							</div>
							<div class="col-md-10">
								<?php echo $urheberrecht['value']; ?>
							</div>
		</div>
	</div>
</div>
