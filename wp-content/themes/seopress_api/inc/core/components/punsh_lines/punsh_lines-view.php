<?php function punsh_line_clean($index) {
include(get_template_directory() .'/inc/core/components/punsh_lines/punsh_lines-model.php');?>
        <div class="container-fluid punshline-container text-center secondary-background-color pdtb30">
            <div class="container">
                <div class="row punshline">
                    <?php echo $punshlines[$index]["punshline_content"];?>
                </div>
            </div>
        </div>
<?php }