<?php 
function banner() {
    include(get_template_directory() .'/inc/core/components/banner/banner-model.php');
    if ($banner["img_title"]){?>
    <div class="contaier-fluid">
        <div class="container pdtb-default text-center">
            <a href="<?php echo $banner['link']; ?>" target="_blank">
                <img src="<?php echo $banner["img_url"];?>" title="<?php echo  $banner["img_title"] ?>" alt="<?php $banner["img_alt"]?>">
            </a>
        </div>
    </div>
<?php }}