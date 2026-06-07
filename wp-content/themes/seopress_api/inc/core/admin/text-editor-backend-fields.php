<?php 
if( function_exists('acf_add_local_field_group') ):

acf_add_local_field_group(array(
	'key' => 'group_63d80abd36918',
	'title' => 'Textbearbeitung',
	'fields' => array(
		array(
			'key' => 'field_63d80ad2fb917',
			'label' => 'Texteditor',
			'name' => 'texteditor',
			'type' => 'wysiwyg',
			'instructions' => '<div style="font-size: larger">

<h1>Allgemeine Variablen</h1>

[DL] = <b>auf der Seite eingegebene Dienstleistungen</b><br>
[BL] = <b>das Bundesland</b><br>
[ORT] = <b>der Ort der Seite auf der sich der User befindet</b><br>
[SITE_TITLE] = <b>zeigt den jeweiligen Titel der Seite auf der sich der User befindet</b><br><br>
<h1>1 Wort aus der Liste wird ausgegeben</h1>
[UNDWORTE] = <b>["und", "&"]</b><br>
[SOWIEWORTE] = <b>["und", "sowie", "wie auch"]</b><br>
[SCHNELLWORTE] = <b>["schnell", "rasch", "baldig", "ehest möglich", "zügig"]</b><br>
[GRATISWORTE] = <b>["kostenlos", "kostenfrei", "unentgeltlich"]</b><br>
[ORDENTLICHWORTE] = <b>[\'sauber\', \'aufgeräumt\', \'akkurat\' ]</b><br>
[KOMPETENTWORTE] = <b>["kompetent", "ordentlich", "gewissenhaft", "verlässlich", "vertrauenswürdig", "professionell", "seriös", "sorgsam"]</b><br><br>

[PROFIWORTE] = <b>["Profis", "Experten", "Spezialisten"]</b><br>
[EXPERTENWORTE] = <b>["Profi, Experte", "Spezialist", "Fachmann"]</b><br>
[PARTNERWORTE] = <b>["Partner", "Begleiter", "Helfer"]</b><br><br>

[RAUMWORTE] = <b>["Raum", "Bereich"]</b><br>
[WERTAUSGLEICHWORTE] = <b>["Wertanrechnung", "Wertgegenrechnung", "Sachwertanrechnung", "Sachwertgegenrechnung", "Antquitäten Wertanrechnung"]</b><br>
[AUFLOESUNGWORTE] = <b>["Auflösung", "Inventar-Auflösung", "Immobilienauflösung"]</b><br>
[DIEFIRMAWORTE] = <b>["Entrümpelungsfirma", "Räumungsfirma", "Auflösungsfirma"]</b><br>
[DERDASUNTERNEHMENWORTE] = <b>["Räumungsunternehmen", "Räumungsdienst" "Räumungssservice", "Entrümpelungsunternehmen", "Entrümpelungsdienst", "Entrümpelungsservice", "Räumung-Unternehmen", "Räumung-Dienst", "Räumung-Service", "Entrümpelung-Unternehmen", "Entrümpelung-Dienst", "Entrümpelung-Service"]</b><br><br>


<h1>Listen werden gemischt und komplett als Aufzählung wiedergegeben</h1>

[KUNDENLISTE] = <b>["Privatpersonen", "Gemeinden", "Vereine", "Firmen"]</b><br>
[ANKAUFLISTE] = <b>["Uhren-", "Schmuck-", "Gemälde-", "Keramik-", "Rahmen-", "Design-", "Silber-"]</b><br>
[ANTIKLISTE] = <b>["Uhren", "Schmuck", "Gemälde", "Keramik", "Rahmen", "Militaria", "Münzen"]</b><br>
[ANTIKLISTE2] = <b>["Gemälde", "Möbel", "Porzelan", "Militaria", "Schmuck", "Teppiche", "Uhren" ]</b><br>
[HANDWERKLEISTUNGENLISTE] = <b>["Maler-", "Elektriker-", "Installateur-", "Fliesenleger-", "Bodenleger-"]</b><br>
[IMMOLISTE] = <b>["Immobilien", "Liegenschaften", "Häuser", "Wohnungen", "Lager", "Schuppen", "Nebengebäude"]</b><br>
[IMMOLISTE2] = <b>["Wohnung", "Haus", "Keller", "Garage", "Storage", "Immobilie", "Verlassenschaft"]</b>]
</div>',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => 'editor',
				'id' => '',
			),
			'default_value' => '',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 0,
			'delay' => 0,
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'template-hauptseiten.php',
			),
		),
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'template-startseite.php',
			),
		),
	),
	'menu_order' => 1,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'field',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
));

endif;