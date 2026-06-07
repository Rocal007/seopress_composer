<?php
// api-options.php
// Options page for global words & lists (no ACF). PHP 7.4+ compatible.

if (!defined('ABSPATH')) exit;

// --- Initialisierung aller Worte und Listen ---
function aso_initialize_api_options() {
    $current_options = get_option('aso_api_words_lists', false);

    // Initialisiere nur, wenn noch keine Optionen gespeichert sind
    if ($current_options === false) {
        // Leere Initialdaten
        $initial_words = array();
        $initial_lists = array();

        // Nur speichern, wenn mindestens ein Eintrag vorhanden ist
        if (!empty($initial_words) || !empty($initial_lists)) {
            update_option('aso_api_words_lists', array(
                'words' => $initial_words,
                'lists' => $initial_lists
            ));
        }
    }
}
add_action('admin_init', 'aso_initialize_api_options');

// Menü hinzufügen
add_action('admin_menu', function () {
    add_menu_page(
        'API Optionen',
        'API Optionen',
        'manage_options',
        'api-options',
        'aso_render_api_options_page',
        'dashicons-admin-generic',
        60
    );
});

// Hauptfunktion für die Options-Seite
function aso_render_api_options_page() {
    // Sicherheitscheck
    if (!current_user_can('manage_options')) {
        wp_die('Unzureichende Berechtigungen.');
    }

    // Verarbeite Formular-Aktionen
    aso_handle_form_actions();

    // Lade gespeicherte Daten
    $saved = get_option('aso_api_words_lists', array('words' => array(), 'lists' => array()));
    
    ?>
    <style>
        .aso-instructions-toggle {
            cursor: pointer;
            color: #0073aa;
            text-decoration: underline;
            margin-top: 10px;
            display: block;
        }
        .aso-instructions {
            background:#fff;
            border-left:4px solid #0073aa;
            padding:15px 20px;
            margin:20px 0;
            font-size:15px;
            line-height:1.6;
        }
        .aso-instructions pre {
            background:#f7f7f7;
            padding:10px;
        }
        .aso-export-import {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
        }
        .aso-delete-group {
            color: #a00;
            text-decoration: none;
            font-size: 12px;
            margin-left: 10px;
        }
        .aso-delete-group:hover {
            color: #dc3232;
        }
        .aso-search {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
        .aso-collapsible-section {
            border: 1px solid #ccc;
            margin: 10px 0;
            border-radius: 4px;
        }
        .aso-collapsible-header {
            background: #f1f1f1;
            padding: 12px 15px;
            cursor: pointer;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
        }
        .aso-collapsible-header:hover {
            background: #e5e5e5;
        }
        .aso-collapsible-header::after {
            content: '▶';
            font-size: 12px;
            transition: transform 0.3s ease;
        }
        .aso-collapsible-header.expanded::after {
            transform: rotate(90deg);
        }
        .aso-collapsible-content {
            padding: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .aso-collapsible-content.expanded {
            max-height: 400px;
            overflow-y: auto;
        }
        .aso-section-count {
            background: #0073aa;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 12px;
            margin-left: 10px;
        }
        .aso-quick-stats {
            background: #f0f0f1;
            border-left: 4px solid #0073aa;
            padding: 10px 15px;
            margin: 15px 0;
            font-size: 14px;
        }
        .aso-add-new-section {
            background: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .aso-add-new-section h3 {
            margin-top: 0;
            color: #23282d;
        }
        .form-table.scrollable-table {
            margin-bottom: 0;
        }
        .form-table.scrollable-table tbody {
            display: block;
            max-height: 350px;
            overflow-y: auto;
        }
        .form-table.scrollable-table tr {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
    </style>
    <div class="wrap">
        <h1>API Optionen — Globale Wörter &amp; Listen</h1>

        <div class="aso-instructions" id="aso-instructions-content" style="display: none;">
            <h2>📘 Bedienungshinweise</h2>
            <p><strong>Wörter (Word-Sets)</strong><br>
                Lege Gruppen an (z. B. <code>PROFESSIONELLWORTE</code>). Trage in das Feld mehrere Varianten – jede Variante in einer neuen Zeile oder kommasepariert.
            </p>
            <pre>professionell, fachgerecht, ordentlich
oder
professionell
fachgerecht
ordentlich</pre>

            <p>Im Content verwendest du den Platzhalter wie folgt:</p>
            <pre>[PROFESSIONELLWORTE]</pre>

            <hr>

            <p><strong>Listen</strong><br>
                Listen enthalten mehrere Einträge (z. B. Ort, Objektarten). Trage je Eintrag eine neue Zeile ein.</p>
            <pre>Goldbarren
Münzen
Schmuck</pre>

            <p>Im Content verwendest du:</p>
            <pre>[GOLDWARENLISTE]</pre>

            <hr>

            <p><strong>Dienstleistungen ([DL])</strong><br>
                Dienstleistungen werden auf der Parent-Seite verwaltet und über die Page-REST-API geliefert (z. B. <code>/wp-json/wp/v2/pages/{id}</code>).
                Diese werden nicht in der Options-Page verwaltet.</p>

            <hr>
            <p><strong>API-Format</strong> (wird vom Endpoint zurückgegeben):</p>
            <pre>
{
  "words": {
    "PROFESSIONELLWORTE": ["professionell","fachgerecht","ordentlich"]
  },
  "lists": {
    "GOLDWARENLISTE": ["Goldbarren","Münzen","Schmuck"]
  }
}
            </pre>
        </div>
        <a class="aso-instructions-toggle" id="aso-toggle-instructions">Anleitungen einblenden</a>

        <?php aso_render_export_import_section(); ?>
        <?php aso_render_quick_stats($saved); ?>
        <?php aso_render_api_options_form($saved); ?>
    </div>
    <script>
    (function(){
        // Funktion zum Aus-/Einblenden der Anleitungen
        var instructions = document.getElementById('aso-instructions-content');
        var toggle = document.getElementById('aso-toggle-instructions');

        // Starte mit ausgeblendeten Anleitungen
        instructions.style.display = 'none';
        toggle.textContent = 'Anleitungen einblenden';

        toggle.addEventListener('click', function(){
            var isHidden = instructions.style.display === 'none';
            instructions.style.display = isHidden ? 'block' : 'none';
            toggle.textContent = isHidden ? 'Anleitungen ausblenden' : 'Anleitungen einblenden';
        });

        // Suchfunktion
        var searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Suchen...';
        searchInput.className = 'aso-search';
        
        var form = document.querySelector('form');
        if (form) {
            form.parentNode.insertBefore(searchInput, form);

            // Zeige Suchfeld nur wenn Einträge vorhanden sind
            var hasEntries = document.querySelectorAll('.form-table.scrollable-table tbody tr').length > 0;
            if (hasEntries) {
                searchInput.style.display = 'block';
                
                searchInput.addEventListener('input', function(e) {
                    var searchTerm = e.target.value.toLowerCase();
                    var tables = document.querySelectorAll('.form-table.scrollable-table');
                    
                    tables.forEach(function(table) {
                        var rows = table.querySelectorAll('tbody tr');
                        var visibleCount = 0;
                        
                        rows.forEach(function(row) {
                            var key = row.querySelector('th') ? row.querySelector('th').textContent.toLowerCase() : '';
                            var textarea = row.querySelector('textarea');
                            var values = textarea ? textarea.value.toLowerCase() : '';
                            
                            if (key.includes(searchTerm) || values.includes(searchTerm)) {
                                row.style.display = '';
                                visibleCount++;
                            } else {
                                row.style.display = 'none';
                            }
                        });
                        
                        // Update section count badge
                        var sectionHeader = table.closest('.aso-collapsible-section').querySelector('.aso-section-count');
                        if (sectionHeader) {
                            sectionHeader.textContent = visibleCount;
                        }
                    });
                });
            }
        }

        // Ausklappbare Sektionen für Wörter und Listen
        document.querySelectorAll('.aso-collapsible-header').forEach(function(header) {
            header.addEventListener('click', function() {
                var content = this.nextElementSibling;
                var isExpanded = content.classList.contains('expanded');
                
                // Schließe alle anderen Inhalte
                document.querySelectorAll('.aso-collapsible-content').forEach(function(c) {
                    if (c !== content) {
                        c.classList.remove('expanded');
                    }
                });
                document.querySelectorAll('.aso-collapsible-header').forEach(function(h) {
                    if (h !== header) {
                        h.classList.remove('expanded');
                    }
                });
                
                // Toggle aktuellen Inhalt
                content.classList.toggle('expanded');
                header.classList.toggle('expanded');
            });
        });

        // Logik für "Neue Gruppe hinzufügen"
        document.getElementById('aso-add-word-group')?.addEventListener('click', function(){
            var keyInput = document.querySelector('input[name="new_word_key"]');
            var valuesTextarea = document.querySelector('textarea[name="new_word_values"]');

            var key = keyInput.value.trim().toUpperCase();
            var values = valuesTextarea.value.trim();

            if (!key) {
                alert('Bitte geben Sie einen Key für die neue Wort-Gruppe ein.');
                return;
            }

            // Prüfe ob Key bereits existiert
            var existingKeys = Array.from(document.querySelectorAll('.form-table:first-child th')).map(th => th.textContent.trim());
            if (existingKeys.includes(key)) {
                alert('Dieser Key existiert bereits. Bitte wählen Sie einen anderen Namen.');
                keyInput.focus();
                return;
            }

            // Füge neue Zeile zur Tabelle hinzu
            var tbody = document.querySelector('.form-table.scrollable-table:first-child tbody');
            var tr = document.createElement('tr');
            tr.innerHTML = '<th scope="row">'+key+'</th><td><textarea name="words['+key+']" rows="3" style="width:100%;">'+values+'</textarea></td>';

            tbody.appendChild(tr);

            // Update count badge
            var wordsCount = document.querySelector('.aso-collapsible-section:first-child .aso-section-count');
            if (wordsCount) {
                var currentCount = parseInt(wordsCount.textContent);
                wordsCount.textContent = currentCount + 1;
            }

            // Felder leeren
            keyInput.value = '';
            valuesTextarea.value = '';
        });

        // Logik für "Neue Liste hinzufügen"
        document.getElementById('aso-add-list')?.addEventListener('click', function(){
            var keyInput = document.querySelector('input[name="new_list_key"]');
            var valuesTextarea = document.querySelector('textarea[name="new_list_values"]');

            var key = keyInput.value.trim().toUpperCase();
            var values = valuesTextarea.value.trim();

            if (!key) {
                alert('Bitte geben Sie einen Key für die neue Liste ein.');
                return;
            }

            // Prüfe ob Key bereits existiert
            var existingKeys = Array.from(document.querySelectorAll('.form-table:nth-child(2) th')).map(th => th.textContent.trim());
            if (existingKeys.includes(key)) {
                alert('Dieser Key existiert bereits. Bitte wählen Sie einen anderen Namen.');
                keyInput.focus();
                return;
            }

            // Füge neue Zeile zur Tabelle hinzu
            var listTable = document.querySelectorAll('.form-table.scrollable-table')[1];
            var tbody = listTable.querySelector('tbody');
            var tr = document.createElement('tr');

            tr.innerHTML = '<th scope="row">'+key+'</th><td><textarea name="lists['+key+']" rows="4" style="width:100%;">'+values+'</textarea></td>';

            tbody.appendChild(tr);

            // Update count badge
            var listsCount = document.querySelector('.aso-collapsible-section:nth-child(2) .aso-section-count');
            if (listsCount) {
                var currentCount = parseInt(listsCount.textContent);
                listsCount.textContent = currentCount + 1;
            }

            // Felder leeren
            keyInput.value = '';
            valuesTextarea.value = '';
        });

        // Lösch-Buttons
        document.querySelectorAll('.aso-delete-group').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Sind Sie sicher, dass Sie diesen Eintrag löschen möchten?')) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    var typeInput = document.createElement('input');
                    typeInput.name = 'delete_type';
                    typeInput.value = this.dataset.type;
                    form.appendChild(typeInput);
                    
                    var keyInput = document.createElement('input');
                    keyInput.name = 'delete_key';
                    keyInput.value = this.dataset.key;
                    form.appendChild(keyInput);
                    
                    var nonceInput = document.createElement('input');
                    nonceInput.name = '_wpnonce';
                    nonceInput.value = '<?php echo wp_create_nonce("aso_save_options"); ?>';
                    form.appendChild(nonceInput);
                    
                    var actionInput = document.createElement('input');
                    actionInput.name = 'aso_delete';
                    actionInput.value = '1';
                    form.appendChild(actionInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        // Export-Funktionalität
        document.getElementById('aso-export-data')?.addEventListener('click', function() {
            var form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            var nonceInput = document.createElement('input');
            nonceInput.name = '_wpnonce';
            nonceInput.value = '<?php echo wp_create_nonce("aso_save_options"); ?>';
            form.appendChild(nonceInput);
            
            var actionInput = document.createElement('input');
            actionInput.name = 'aso_export';
            actionInput.value = '1';
            form.appendChild(actionInput);
            
            document.body.appendChild(form);
            form.submit();
        });

        // Import-Funktionalität
        document.getElementById('aso-import-data')?.addEventListener('click', function() {
            var fileInput = document.getElementById('aso-import-file');
            if (!fileInput.files.length) {
                alert('Bitte wählen Sie eine JSON-Datei aus.');
                return;
            }

            var file = fileInput.files[0];
            var reader = new FileReader();
            
            reader.onload = function(e) {
                try {
                    var data = JSON.parse(e.target.result);
                    
                    // Validiere die Datenstruktur
                    if (!data.words || !data.lists) {
                        throw new Error('Ungültiges Dateiformat. Erwartet werden "words" und "lists" Objekte.');
                    }
                    
                    // Sende Daten zum Server
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.style.display = 'none';
                    
                    var dataInput = document.createElement('input');
                    dataInput.name = 'import_data';
                    dataInput.value = JSON.stringify(data);
                    form.appendChild(dataInput);
                    
                    var nonceInput = document.createElement('input');
                    nonceInput.name = '_wpnonce';
                    nonceInput.value = '<?php echo wp_create_nonce("aso_save_options"); ?>';
                    form.appendChild(nonceInput);
                    
                    var actionInput = document.createElement('input');
                    actionInput.name = 'aso_import';
                    actionInput.value = '1';
                    form.appendChild(actionInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                    
                } catch (error) {
                    alert('Fehler beim Lesen der Datei: ' + error.message);
                }
            };
            
            reader.readAsText(file);
        });
    })();
    </script>
    <?php
}

// Formular-Aktionen verarbeiten
function aso_handle_form_actions() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'aso_save_options')) {
        wp_die('Sicherheitsüberprüfung fehlgeschlagen.');
    }

    // Daten speichern
    if (isset($_POST['aso_save'])) {
        aso_save_data();
    }
    
    // Eintrag löschen
    if (isset($_POST['aso_delete'])) {
        aso_delete_entry();
    }
    
    // Exportieren
    if (isset($_POST['aso_export'])) {
        aso_export_data();
    }
    
    // Importieren
    if (isset($_POST['aso_import'])) {
        aso_import_data();
    }
}

// Daten speichern
function aso_save_data() {
    $words_raw = $_POST['words'] ?? array();
    $lists_raw = $_POST['lists'] ?? array();

    $words = array();
    foreach ($words_raw as $key => $val) {
        $key = sanitize_text_field(strtoupper($key));
        $items = aso_clean_text_array($val);
        if (!empty($items)) $words[$key] = $items;
    }

    $lists = array();
    foreach ($lists_raw as $key => $val) {
        $key = sanitize_text_field(strtoupper($key));
        $items = aso_clean_text_array($val);
        if (!empty($items)) $lists[$key] = $items;
    }

    // Neue Einträge hinzufügen
    if (!empty($_POST['new_word_key']) && !empty($_POST['new_word_values'])) {
        $new_key = sanitize_text_field(strtoupper($_POST['new_word_key']));
        $items = aso_clean_text_array($_POST['new_word_values']);
        if (!empty($items)) $words[$new_key] = $items;
    }

    if (!empty($_POST['new_list_key']) && !empty($_POST['new_list_values'])) {
        $new_key = sanitize_text_field(strtoupper($_POST['new_list_key']));
        $items = aso_clean_text_array($_POST['new_list_values']);
        if (!empty($items)) $lists[$new_key] = $items;
    }

    if (update_option('aso_api_words_lists', array('words' => $words, 'lists' => $lists))) {
        echo '<div class="updated"><p>Einstellungen wurden gespeichert.</p></div>';
    } else {
        echo '<div class="error"><p>Fehler beim Speichern der Einstellungen.</p></div>';
    }
}

// Eintrag löschen
function aso_delete_entry() {
    $delete_type = $_POST['delete_type'] ?? '';
    $delete_key = $_POST['delete_key'] ?? '';
    
    if (!$delete_type || !$delete_key) {
        echo '<div class="error"><p>Fehler beim Löschen: Ungültige Parameter.</p></div>';
        return;
    }
    
    $saved = get_option('aso_api_words_lists', array('words' => array(), 'lists' => array()));
    
    if ($delete_type === 'words' && isset($saved['words'][$delete_key])) {
        unset($saved['words'][$delete_key]);
        echo '<div class="updated"><p>Wort-Gruppe "' . esc_html($delete_key) . '" wurde gelöscht.</p></div>';
    } elseif ($delete_type === 'lists' && isset($saved['lists'][$delete_key])) {
        unset($saved['lists'][$delete_key]);
        echo '<div class="updated"><p>Liste "' . esc_html($delete_key) . '" wurde gelöscht.</p></div>';
    }
    
    update_option('aso_api_words_lists', $saved);
}

// Daten exportieren
function aso_export_data() {
    $data = get_option('aso_api_words_lists', array('words' => array(), 'lists' => array()));
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="api-words-lists-export-' . date('Y-m-d') . '.json"');
    header('Pragma: no-cache');
    
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Daten importieren
function aso_import_data() {
    $import_data = $_POST['import_data'] ?? '';
    
    if (!$import_data) {
        echo '<div class="error"><p>Fehler beim Import: Keine Daten empfangen.</p></div>';
        return;
    }
    
    try {
        $data = json_decode(stripslashes($import_data), true);
        
        if (!is_array($data) || !isset($data['words']) || !isset($data['lists'])) {
            throw new Exception('Ungültiges Datenformat.');
        }
        
        // Daten bereinigen
        $cleaned_data = array(
            'words' => array(),
            'lists' => array()
        );
        
        foreach ($data['words'] as $key => $items) {
            $key = sanitize_text_field(strtoupper($key));
            $cleaned_data['words'][$key] = aso_clean_text_array($items);
        }
        
        foreach ($data['lists'] as $key => $items) {
            $key = sanitize_text_field(strtoupper($key));
            $cleaned_data['lists'][$key] = aso_clean_text_array($items);
        }
        
        if (update_option('aso_api_words_lists', $cleaned_data)) {
            echo '<div class="updated"><p>Daten wurden erfolgreich importiert.</p></div>';
        } else {
            echo '<div class="error"><p>Fehler beim Speichern der importierten Daten.</p></div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="error"><p>Fehler beim Import: ' . esc_html($e->getMessage()) . '</p></div>';
    }
}

// Hilfsfunktion zum Bereinigen von Text-Arrays
function aso_clean_text_array($input) {
    if (is_array($input)) {
        return array_map('sanitize_text_field', $input);
    }
    
    $items = preg_split('/[\r\n,]+/', trim($input));
    $items = array_map('sanitize_text_field', $items);
    $items = array_filter($items, function($v) { 
        return !empty(trim($v)); 
    });
    
    return array_values($items);
}

// Export/Import Section rendern
function aso_render_export_import_section() {
    ?>
    <div class="aso-export-import">
        <h2>📤 Export / 📥 Import</h2>
        <p>
            <button type="button" class="button button-primary" id="aso-export-data">📤 Daten exportieren</button>
            <span style="margin-left: 20px;">
                <input type="file" id="aso-import-file" accept=".json">
                <button type="button" class="button" id="aso-import-data">📥 Daten importieren</button>
            </span>
        </p>
        <p class="description">Exportieren Sie Ihre aktuellen Daten als JSON-Datei oder importieren Sie zuvor exportierte Daten.</p>
    </div>
    <?php
}

// Schnell-Statistiken anzeigen
function aso_render_quick_stats($saved) {
    $words_count = count($saved['words'] ?? []);
    $lists_count = count($saved['lists'] ?? []);
    $total_items = $words_count + $lists_count;
    
    $total_words_items = 0;
    foreach ($saved['words'] ?? [] as $items) {
        $total_words_items += count($items);
    }
    
    $total_lists_items = 0;
    foreach ($saved['lists'] ?? [] as $items) {
        $total_lists_items += count($items);
    }
    
    ?>
    <div class="aso-quick-stats">
        <strong>📊 Schnell-Statistik:</strong> 
        <?php echo $total_items; ?> Kategorien 
        (<?php echo $words_count; ?> Wort-Gruppen mit <?php echo $total_words_items; ?> Einträgen, 
        <?php echo $lists_count; ?> Listen mit <?php echo $total_lists_items; ?> Einträgen)
    </div>
    <?php
}

// Haupt-Formular rendern
function aso_render_api_options_form($saved) {
    $words = $saved['words'] ?? array();
    $lists = $saved['lists'] ?? array();
    ?>
    <form method="post">
        <?php wp_nonce_field('aso_save_options'); ?>
        <input type="hidden" name="aso_save" value="1">

        <!-- Wörter Section -->
        <div class="aso-collapsible-section">
            <div class="aso-collapsible-header">
                <span>Wörter (Gruppen) <span class="aso-section-count"><?php echo count($words); ?></span></span>
            </div>
            <div class="aso-collapsible-content">
                <table class="form-table scrollable-table">
                    <tbody>
                    <?php foreach ($words as $key => $items): ?>
                        <tr>
                            <th scope="row">
                                <?php echo esc_html($key); ?>
                                <br>
                                <small>
                                    <a href="#" class="aso-delete-group" data-type="words" data-key="<?php echo esc_attr($key); ?>">Löschen</a>
                                </small>
                            </th>
                            <td>
                                <textarea name="words[<?php echo esc_attr($key); ?>]" rows="3" style="width:100%;"><?php 
                                    echo esc_textarea(implode("\n", $items)); 
                                ?></textarea>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Listen Section -->
        <div class="aso-collapsible-section">
            <div class="aso-collapsible-header">
                <span>Listen <span class="aso-section-count"><?php echo count($lists); ?></span></span>
            </div>
            <div class="aso-collapsible-content">
                <table class="form-table scrollable-table">
                    <tbody>
                    <?php foreach ($lists as $key => $items): ?>
                        <tr>
                            <th scope="row">
                                <?php echo esc_html($key); ?>
                                <br>
                                <small>
                                    <a href="#" class="aso-delete-group" data-type="lists" data-key="<?php echo esc_attr($key); ?>">Löschen</a>
                                </small>
                            </th>
                            <td>
                                <textarea name="lists[<?php echo esc_attr($key); ?>]" rows="4" style="width:100%;"><?php 
                                    echo esc_textarea(implode("\n", $items)); 
                                ?></textarea>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add New Sections -->
        <div class="aso-add-new-section">
            <h3>➕ Neue Wort-Gruppe hinzufügen</h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">Neue Gruppe</th>
                        <td>
                            <input type="text" name="new_word_key" placeholder="NEUEKEYINCAPS" style="width:30%;"><br>
                            <textarea name="new_word_values" rows="3" placeholder="je Eintrag eine Zeile (oder kommasepariert)" style="width:60%;"></textarea><br>
                            <button type="button" class="button" id="aso-add-word-group">+ Gruppe hinzufügen</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="aso-add-new-section">
            <h3>➕ Neue Liste hinzufügen</h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">Neue Liste</th>
                        <td>
                            <input type="text" name="new_list_key" placeholder="NEUEKEYINCAPS" style="width:30%;"><br>
                            <textarea name="new_list_values" rows="4" placeholder="je Eintrag eine Zeile" style="width:60%;"></textarea><br>
                            <button type="button" class="button" id="aso-add-list">+ Liste hinzufügen</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php submit_button('Alle Änderungen speichern'); ?>
    </form>
    <?php
}