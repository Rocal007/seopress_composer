<?php
function main_text_hs_clean(){	
	include(get_template_directory() .'/inc/core/components/main_text/main_text-model.php');?>
		<div class="container-fluid" id="main-text-container">
			<div class="container">
				    <section>
						<div class="row">
								<div class="col-md-12">
									<div class="haupttext-heading pd20">
										<h2>
											<?php echo $haupttext['haupttext_heading'];?>
										</h2>
									</div>
									<div class="haupttext-content">
										<?php echo $haupttext["haupttext_content"];?>
									</div>
								</div>
							</div>
					</section>            
			</div>
		</div>
<?php }

function main_text_sub_clean(){	
	include(get_template_directory() .'/inc/core/components/main_text/main_text-model.php');?>
		<div class="container-fluid pdtb-default" id="main-text-container">
			<div class="container">
				
				    <section>
						<div class="row">
								<div class="col-md-12">
									<div class="haupttext-heading text-center pd20">
										<h2>
											<?php echo $hauptext_subpage['haupttext_heading'];?>
										</h2>
									</div>
									<div class="haupttext-content">
										<div class="col-md-6">
											<p><?php echo $hauptext_subpage["haupttext_content"][0];?></p>
										</div>
										<div class="col-md-6">
											<p><?php echo $hauptext_subpage["haupttext_content"][1];?></p>
										</div>
									</div>
								</div>
							</div>
					</section>            
					
			</div>
		</div>
<?php }

function main_text_cat_clean(){	
	include(get_template_directory() .'/inc/core/components/main_text/main_text-model.php');?>
		<div class="container-fluid" id="main-text-container">
			<div class="container">
				<div class="haupttext-heading text-center pd20">	
					<h2><?php echo $haupttext_category["haupttext-heading"];?></h2>		
				</div>
				<div class="col-md-6">
					<div class="haupttext-content">
						<?php echo $haupttext_category["p_1"];?>
						<?php echo $haupttext_category["p_2"];?>
					</div>
				</div>
				<div class="col-md-6">
					<div class="haupttext-content">
						<?php echo $haupttext_category["p_3"];?>
						<?php echo $haupttext_category["p_4"];?>
					</div>
				</div>
			</div>       
		</div>
<?php }