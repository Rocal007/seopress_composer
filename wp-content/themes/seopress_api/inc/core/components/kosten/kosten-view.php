<?php function kosten(){
    include(get_template_directory() . '/inc/core/components/kosten/kosten-model.php'); 
    if (isset($kosten)) {?>
    <div id="pricing-table" class="container-fluid primary-background-color">
        <div class="container">
			<div class="invert-text-color text-center pdtb-default"><?php echo $kosten_heading; ?></div>
            <div class="pricing row pdtb-default">
                <?php if ($kosten_count) { ?>
                    <?php foreach ($kosten as $kostet) { ?>
                        <div class="col-md-<?php echo $kosten_count; ?>">
                            <div class="pricing-item text-center pdtb-default <?php echo $mobil; ?>">
                                <div class="pricing-divider accent-background-color text-center">
                                    <p class="invert-text-color pd10"><?php echo $kostet["art_kosten"]; ?></p>
                                </div>
                                <div class="card-body bg-white shadow">
                                    <ul class="list-unstyled mrb50">
                                        <li>wenig Hausrat<br>
                                            <span class="h3">ab € <?php echo $kostet["wenig_kosten"]; ?>.-</span>
                                            <hr>
                                        </li>
                                        <li>normaler Hausrat<br>
                                            <span class="h3">ab € <?php echo $kostet["normal_kosten"]; ?>.-</span>
                                            <hr>
                                        </li>
                                        <li>viel Hausrat <br>
                                            <span class="h3">ab € <?php echo $kostet["viel_kosten"]; ?>.-</span>
                                            <hr>
                                        </li>
                                        <li>Messie <br>
                                            <span class="h3">ab € <?php echo $kostet["messie_kosten"]; ?>.-</span>
                                        </li>
                                    </ul>
                                    <a href="/kontakt" title="Kontakt aufnehmen für eine <?php echo $kostet["art_kosten"]; ?>">
                                        <div class="pricing-read-more accent-background-color pdtb20 invert-text-color mrt20">Anfragen</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
    }
}

function kosten_flip(){
    include(get_template_directory() . '/inc/core/components/kosten/kosten-model.php'); 
    if ($kosten) {?>
    <div id="pricing-table" class="container-fluid primary-background-color">
        <div class="container pdtb-default">
            <div class="pricing row">
                 <div class="pricing-heading invert-text-color h4 text-center"><?php echo $kosten_heading; ?></div>
                <?php if ($kosten_count) { ?>
                    <?php foreach ($kosten as $kostet) { ?>
                        <div class="col-md-<?php echo $kosten_count; ?>">
                            <div class="dbox">
                                <div class="flip-container">
                                    <div class="flip-front">
                                        <div class="pricing-item text-center">
                                            <div class="pricing-divider accent-background-color text-center">
                                                <p class="invert-text-color"><?php echo $kostet["art_kosten"]; ?></p>
                                            </div>
                                            <div class="card-body bg-white mt-0 shadow">
                                                <ul class="list-unstyled">
                                                    <li>wenig Hausrat<br>
                                                       <span class="h5">ab € <?php echo $kostet["wenig_kosten"]; ?>.-</span>
                                                       <hr>
                                                    </li>
                                                    <li>normaler Hausrat<br>
                                                        <span class="h5">ab € <?php echo $kostet["normal_kosten"]; ?>.-</span>
                                                        <hr>
                                                    </li>
                                                    <li>viel Hausrat <br>
                                                        <span class="h5">ab € <?php echo $kostet["viel_kosten"]; ?>.-</span>
                                                        <hr>
                                                    </li>
                                                    <li>Messie <br>
                                                        <span class="h5">ab € <?php echo $kostet["messie_kosten"]; ?>.-</span>
                                                    </li>
                                                </ul>
                                                <a href="/kontakt">
                                                    <div class="pricing-read-more accent-background-color">Anfragen</div>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="/kontakt">
                                        <div class="pricing-read-more accent-background-color flip-back">
                                            <div>Anfragen</div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
<?php }}