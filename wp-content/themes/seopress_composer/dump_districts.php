<?php
require 'c:/laragon/www/Entruempelung-live/wp-load.php';
$districts = get_terms(['taxonomy' => 'district', 'hide_empty' => false]);
$output = [];
foreach($districts as $d) {
    $output[] = $d->name;
}
file_put_contents(__DIR__ . '/district_dump.json', json_encode($output));
echo "Done";
