<?php

namespace SeopressComposer\Components;

use SeopressComposer\Models\Taxonomy_Data;

/**
 * MultiStepForm Component
 *
 * Context-aware contact form with 3-4 steps depending on page context:
 *  - 'default'  : Step 1 shows hardcoded service cards, 4 steps total
 *  - 'category' : Step 1 shows real service_category terms (dynamic), 4 steps total
 *  - 'service'  : Step 1 is skipped (service already known), 3 steps total
 *  - 'location' : Step 1 shows service cards, location is pre-filled as hidden field, 4 steps
 *
 * @param string|null $preselected_service  Slug of the pre-selected service (for 'service' context)
 * @param string      $context              'default' | 'category' | 'service' | 'location'
 * @param string|null $location             Location name (Bezirk/Ort) to pre-fill hidden field
 */
class MultiStepForm
{
  private Taxonomy_Data $taxonomyData;
  private \SeopressComposer\Services\IconService $iconService;

  public function __construct(
    Taxonomy_Data $taxonomyData,
    \SeopressComposer\Services\IconService $iconService
  ) {
    $this->taxonomyData = $taxonomyData;
    $this->iconService = $iconService;
  }

  /**
   * Render the Multi-Step Contact Form
   *
   * @param string|null $preselected_service  Slug of the pre-selected service
   * @param string      $context              'default' | 'category' | 'service'
   * @return string HTML output
   */
  public function render(?string $preselected_service = null, string $context = 'default', ?string $location = null): string
  {
    $skip_step1 = ($context === 'service');
    $total_steps = $skip_step1 ? 3 : 4;

    // Build step-1 service cards depending on context
    $step1_cards = [];
    if (!$skip_step1) {
      if ($context === 'category') {
        // Dynamic: real service_category terms from WordPress
        $raw = $this->taxonomyData->get_all_service_categories();
        foreach ($raw as $cat) {
          $step1_cards[] = [
            'id'   => $cat['slug'],
            'name' => $cat['name'],
            'icon' => $cat['icon'] ?: $cat['slug'],
            'link' => get_term_link(get_term_by('slug', $cat['slug'], 'service_category')) ?: null,
          ];
        }
      } else {
        // Default: hardcoded list for generic pages
        $step1_cards = [
          ['id' => 'wohnung',      'name' => 'Wohnung',             'icon' => 'wohnungsaufloesung'],
          ['id' => 'haus',         'name' => 'Haus',                'icon' => 'haus'],
          ['id' => 'keller',       'name' => 'Keller',              'icon' => 'kellerraeumung'],
          ['id' => 'dachboden',    'name' => 'Dachboden',           'icon' => 'dachboden'],
          ['id' => 'buero',        'name' => 'Büro / Gewerbe',      'icon' => 'bueroraeumung'],
          ['id' => 'garage',       'name' => 'Garage / Garten',     'icon' => 'transport'],
          ['id' => 'messie',       'name' => 'Messie-Entrümpelung', 'icon' => 'messie'],
          ['id' => 'verlassenschaft', 'name' => 'Verlassenschaft',  'icon' => 'verlassenschaft_1'],
          ['id' => 'sonstiges',    'name' => 'Sonstiges',           'icon' => 'arrow_icon'],
        ];
      }
    }

    ob_start();
    ?>
    <style>
      #multi-step-form-component .steps .step {
        gap: 0.8rem;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--color-neutral-700, #4b5563);
        min-width: 120px;
      }

      #multi-step-form-component .steps .step:after {
        width: 2.5rem;
        height: 2.5rem;
        line-height: 2.5rem;
        font-size: 1rem;
        border: 2px solid #e5e7eb;
        background-color: white;
        color: #9ca3af;
        transition: all 0.3s ease;
      }

      #multi-step-form-component .steps .step:before {
        height: 0.2rem;
        background-color: #e5e7eb;
        top: 1.25rem;
      }

      #multi-step-form-component .steps .step-primary:after {
        background-color: var(--color-primary);
        border-color: var(--color-primary);
        color: white;
      }

      #multi-step-form-component .steps .step-primary:before {
        background-color: var(--color-primary);
      }

      #multi-step-form-component .steps .step-success:before,
      #multi-step-form-component .steps .step-success:after {
        background-color: var(--color-success) !important;
        border-color: var(--color-success) !important;
      }

      /* Premium Pulse Effect for Active Step */
      #multi-step-form-component .steps .step-primary.step-active:after {
        box-shadow: 0 0 0 4px rgba(var(--color-primary-rgb, 13, 110, 253), 0.2);
        animation: step-pulse 2s infinite;
      }

      @keyframes step-pulse {
        0%   { box-shadow: 0 0 0 0 rgba(var(--color-primary-rgb, 13, 110, 253), 0.4); }
        70%  { box-shadow: 0 0 0 8px rgba(var(--color-primary-rgb, 13, 110, 253), 0); }
        100% { box-shadow: 0 0 0 0 rgba(var(--color-primary-rgb, 13, 110, 253), 0); }
      }

      /* Stronger Selection State for Cards */
      #multi-step-form-component .service-card-input:checked + .service-card-content {
        border-color: var(--color-primary);
        background-color: rgba(var(--color-primary-rgb), 0.1);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      }

      #multi-step-form-component .service-card-input:checked + .service-card-content .selection-indicator {
        opacity: 1;
        transform: scale(1);
      }

      #multi-step-form-component .service-card-input:checked + .service-card-content .service-name {
        color: var(--color-primary);
      }

      #multi-step-form-component .service-card-content {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }

      /* Category-context: cards link to category page */
      #multi-step-form-component .service-cat-link {
        display: block;
        text-decoration: none;
        color: inherit;
      }
      #multi-step-form-component .service-cat-link:hover .service-card-content {
        border-color: var(--color-primary);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
      }
    </style>

    <section class="multi-step-form-component w-full mx-auto p-4" id="multi-step-form-component"
             data-context="<?= esc_attr($context) ?>"
             data-skip-step1="<?= $skip_step1 ? 'true' : 'false' ?>"
             data-total-steps="<?= $total_steps ?>">

      <?php if ($context !== 'category'): ?>
      <!-- Steps Indicator (only for default & service context) -->
      <div class="hidden md:flex justify-center mb-10 mt-4">
        <ul class="steps steps-horizontal w-full max-w-2xl">
          <?php if (!$skip_step1): ?>
            <li class="step step-primary step-active" data-step="1">Auswahl</li>
            <li class="step" data-step="2">Details</li>
            <li class="step" data-step="3">Kontakt</li>
            <li class="step" data-step="4">Kontaktaufnahme</li>
          <?php else: ?>
            <li class="step step-primary step-active" data-step="2">Details</li>
            <li class="step" data-step="3">Kontakt</li>
            <li class="step" data-step="4">Kontaktaufnahme</li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>

      <!-- Form Container -->
      <div class="card bg-base-100 shadow-xl border border-base-200">
        <div class="card-body">

          <?php if ($context === 'category' && !empty($step1_cards)): ?>
            <!-- ── CATEGORY CONTEXT: Clickable category cards (no form step) ── -->
            <h3 class="text-3xl font-heading font-medium mb-8 text-center text-primary">
              Welchen Bereich möchten Sie anfragen?
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <?php foreach ($step1_cards as $cat): ?>
                <?php
                  $icon_html = $this->iconService->getIconHtml(
                    $cat['icon'],
                    $cat['name'],
                    ['size' => 'sm', 'primary_background' => true, 'invert' => true]
                  );
                  $link = !empty($cat['link']) && !is_wp_error($cat['link']) ? $cat['link'] : '#';
                ?>
                <a href="<?= esc_url($link) ?>" class="service-cat-link group block">
                  <div class="service-card-content flex items-center gap-5 p-5 rounded-2xl bg-white border-2 border-base-200 hover:border-primary/40 hover:shadow-md transition-all duration-300">
                    <div class="flex-shrink-0 transition-transform duration-500 group-hover:scale-110">
                      <?= $icon_html ?>
                    </div>
                    <span class="service-name font-sans font-bold text-lg text-base-content transition-colors group-hover:text-primary">
                      <?= esc_html($cat['name']) ?>
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-auto h-5 w-5 text-primary/40 group-hover:text-primary transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 20px; height: 20px;">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                  </div>
                </a>
              <?php endforeach; ?>
            </div>
            <div class="mt-8 text-center">
              <p class="text-sm font-medium text-base-content/50">
                Wählen Sie einen Bereich, um direkt zur passenden Anfrageseite zu gelangen.
              </p>
            </div>

          <?php else: ?>
            <!-- ── DEFAULT / SERVICE CONTEXT: Multi-Step Form ── -->
            <form class="multi-step-form space-y-6" onsubmit="event.preventDefault();">

              <?php if (!$skip_step1): ?>
              <!-- Step 1: Service Selection -->
              <div class="form-step" data-step="1">
                <h3 class="text-3xl font-heading font-medium mb-8 text-center text-primary">Was darf entrümpelt werden?</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <?php foreach ($step1_cards as $service):
                    $icon_html = $this->iconService->getIconHtml(
                      $service['icon'],
                      $service['name'],
                      ['size' => 'sm', 'primary_background' => true, 'invert' => true]
                    );
                    $checked = ($preselected_service && strpos($preselected_service, $service['id']) !== false) ? 'checked' : '';
                  ?>
                    <label class="relative cursor-pointer group block">
                      <input type="checkbox" name="services[]" value="<?= esc_attr($service['id']) ?>" class="service-card-input hidden" <?= $checked ?> />
                      <div class="service-card-content flex items-center gap-6 p-5 rounded-2xl bg-white border-2 border-base-200 hover:border-primary/40 hover:shadow-md transition-all duration-300">
                        <div class="flex-shrink-0 transition-transform duration-500 group-hover:scale-110">
                          <?= $icon_html ?>
                        </div>
                        <span class="service-name font-sans font-bold text-lg text-base-content transition-colors">
                          <?= esc_html($service['name']) ?>
                        </span>
                        <div class="selection-indicator absolute top-3 right-3 opacity-0 scale-50 transition-all duration-300 pointer-events-none">
                          <div class="bg-primary text-white rounded-full p-1.5 shadow-lg border-2 border-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;">
                              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                          </div>
                        </div>
                      </div>
                    </label>
                  <?php endforeach; ?>
                </div>
                <div class="mt-8 text-center">
                  <p class="text-sm font-medium text-base-content/50">Bitte wählen Sie einen oder mehrere Bereiche aus.</p>
                </div>
              </div>
              <?php endif; // end !skip_step1 ?>

              <!-- Step 2: Details & Images -->
              <div class="form-step <?= $skip_step1 ? '' : 'hidden' ?>" data-step="2">
                <?php if ($skip_step1 && $preselected_service): ?>
                  <input type="hidden" name="services[]" value="<?= esc_attr($preselected_service) ?>" />
                <?php endif; ?>
                <?php if (!empty($location)): ?>
                  <input type="hidden" name="location" value="<?= esc_attr($location) ?>" />
                <?php endif; ?>

                <h3 class="text-3xl font-heading font-medium mb-4 text-center text-primary">Details zur Räumung</h3>

                <?php if (!empty($location)): ?>
                <!-- Location Badge -->
                <div class="flex items-center justify-center gap-2 mb-8">
                  <span class="inline-flex items-center gap-2 bg-primary/10 text-primary font-semibold text-sm px-4 py-2 rounded-full border border-primary/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px;">
                      <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    <?= esc_html($location) ?>
                  </span>
                </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div class="form-control w-full">
                    <label class="label"><span class="label-text">Ungefähre Fläche (m²)</span></label>
                    <input type="number" name="area" class="input input-bordered w-full" placeholder="z.B. 80" />
                  </div>
                  <div class="form-control w-full">
                    <label class="label"><span class="label-text">Etage / Stockwerk</span></label>
                    <select name="floor" class="select select-bordered w-full">
                      <option value="" disabled selected>Bitte wählen...</option>
                      <option value="eg">Erdgeschoss</option>
                      <option value="1">1. Stock</option>
                      <option value="2">2. Stock</option>
                      <option value="3">3. Stock+</option>
                      <option value="dach">Dachboden</option>
                      <option value="keller">Keller</option>
                    </select>
                  </div>
                </div>

                <div class="form-control w-full">
                  <label class="label"><span class="label-text">Besonderheiten / Beschreibung (Optional)</span></label>
                  <textarea name="description" class="textarea textarea-bordered h-32" placeholder="Gibt es schwere Möbel, Sondermüll oder andere Besonderheiten?"></textarea>
                </div>

                <div class="form-control w-full mt-4">
                  <label class="label"><span class="label-text">Fotos hochladen (Optional - hilft bei der Schätzung)</span></label>
                  <input type="file" name="images[]" multiple class="file-input file-input-bordered w-full" accept="image/*" />
                  <label class="label"><span class="label-text-alt">Max. 5 MB pro Datei. Formate: JPG, PNG.</span></label>
                </div>
              </div>

              <!-- Step 3: Contact Info -->
              <div class="form-step hidden" data-step="3">
                <h3 class="text-3xl font-heading font-medium mb-8 text-center text-primary">Ihre Kontaktdaten</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="form-control w-full">
                    <label class="label"><span class="label-text">Vorname *</span></label>
                    <input type="text" name="firstname" required class="input input-bordered w-full" />
                  </div>
                  <div class="form-control w-full">
                    <label class="label"><span class="label-text">Nachname *</span></label>
                    <input type="text" name="lastname" required class="input input-bordered w-full" />
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                  <div class="form-control w-full">
                    <label class="label"><span class="label-text">E-Mail Adresse *</span></label>
                    <input type="email" name="email" required class="input input-bordered w-full" />
                  </div>
                  <div class="form-control w-full">
                    <label class="label"><span class="label-text">Telefonnummer *</span></label>
                    <input type="tel" name="phone" required class="input input-bordered w-full" />
                  </div>
                </div>

                <div class="bg-base-200/50 p-8 rounded-2xl mt-8 has-[:checked]:bg-primary/5 transition-all duration-300 border border-transparent has-[:checked]:border-primary/20">
                  <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-4">
                      <input type="checkbox" name="request_viewing" class="checkbox checkbox-primary peer" />
                      <span class="label-text font-sans font-bold text-lg text-primary">Kostenlose Besichtigung erwünscht?</span>
                    </label>
                    <p class="text-sm opacity-70 ml-10 font-sans">Für ein fixes Festpreisangebot empfehlen wir eine kurze Vor-Ort-Besichtigung.</p>
                  </div>
                  <div class="form-control w-full mt-6 ml-10 hidden [.peer:checked~&]:block">
                    <label class="label"><span class="label-text font-sans font-medium">Wunschtermin (Optional)</span></label>
                    <input type="text" name="preferred_date" class="input input-bordered w-full focus:border-primary" placeholder="z.B. Nächsten Dienstag Vormittag" />
                  </div>
                </div>

                <div class="form-control mt-8">
                  <label class="label cursor-pointer justify-start gap-4">
                    <input type="checkbox" required class="checkbox checkbox-primary" />
                    <span class="label-text font-sans">Ich stimme der Verarbeitung meiner Daten gemäß der <a href="/datenschutz" class="link link-primary font-medium" target="_blank">Datenschutzerklärung</a> zu. *</span>
                  </label>
                </div>
              </div>

              <!-- Step 4: Success -->
              <div class="form-step hidden" data-step="4">
                <div class="text-center py-12">
                  <div class="text-7xl mb-6">✅</div>
                  <h3 class="text-4xl font-heading font-bold mb-4 text-success">Vielen Dank!</h3>
                  <p class="text-xl font-sans opacity-80">Wir haben Ihre Anfrage erhalten und werden uns in Kürze bei Ihnen melden.</p>
                  <button type="button" class="btn btn-outline btn-primary mt-10 px-8" onclick="location.reload()">Neue Anfrage</button>
                </div>
              </div>

              <!-- Navigation -->
              <div class="card-actions justify-between items-center mt-8 form-navigation">
                <button type="button" class="btn btn-ghost prev-btn" style="visibility: hidden;">Zurück</button>
                <span class="text-sm text-base-content/50 font-medium form-progress">
                  Schritt 1 von <?= $total_steps - 1 ?>
                </span>
                <button type="button" class="btn btn-primary next-btn">Weiter</button>
                <button type="submit" class="btn btn-success hidden submit-btn">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                  Kostenlos absenden
                </button>
              </div>

            </form>
          <?php endif; // end category vs form context ?>

        </div>
      </div>
    </section>
<?php
    return ob_get_clean();
  }
}
