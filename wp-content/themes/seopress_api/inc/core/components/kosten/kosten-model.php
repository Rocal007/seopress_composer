<?php

    $remote_url = get_field('remote_page', check_page_id());
    if (isset($remote_url)){
        //var_dump($remote_url);
        $response = wp_remote_get($remote_url);
        $all = json_decode(wp_remote_retrieve_body($response));
        $posten_kosten_acf = $all->acf->posten_kosten;
		$uberschrift_kosten = $all->acf->uberschrift_kosten;
			
        //var_dump($posten_kosten_acf);

        if (!empty($posten_kosten_acf)){
            $kosten_count = count($posten_kosten_acf);
            $kosten_heading = $uberschrift_kosten;
			$kosten_heading = apply_filters( 'the_content', $kosten_heading );

            if ($kosten_count === 3) {
                $kosten_count = 4;
            } elseif ($kosten_count === 4) {
                $kosten_count = 3;
            }

            foreach ($posten_kosten_acf as $kosten_acf) {
                $kosten[] = array(
                    "art_kosten" => $kosten_acf-> art_kosten,
                    "wenig_kosten" => $kosten_acf-> wenig_hausrat_kosten,
                    "normal_kosten" => $kosten_acf-> normaler_hausrat_kosten,
                    "viel_kosten" => $kosten_acf-> viel_hausrat_kosten,
                    "messie_kosten" => $kosten_acf-> messie_kosten,
                );
            }
        }
    }