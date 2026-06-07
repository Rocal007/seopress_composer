<?php

/**
 * Template part for displaying the footer widgets
 */

// Get Services
$components = seopress_components();
$container = seopress_container();

// Get model instances
$contactDataModel = $container->get(\SeopressComposer\Models\Contact_Data::class);
$taxonomyDataModel = $container->get(\SeopressComposer\Models\Taxonomy_Data::class);

// Get contact links
$contactLinks = $contactDataModel->get_data()['contact_links'] ?? [];

// Get footer link color from settings
$footerLinkColor = get_field('footer_link_color', 'option') ?: '#e5e5e5';

// Get Menu Data Model
$menuDataModel = $container->get(\SeopressComposer\Models\Menu_Data::class);

$categories = $menuDataModel->get_service_categories();
$mainMenu = array_map(function($cat) {
  return [
    'name' => $cat['title'] ?? '',
    'link' => $cat['url'] ?? '#',
    'icon' => $cat['icon'] ?? ''
  ];
}, $categories);

$aboutMenu = $taxonomyDataModel->get_pages_by_menu_category('ratgeber');
if (empty($aboutMenu)) {
  $aboutMenu = $taxonomyDataModel->get_pages_by_menu_category('advice'); // Fallback
}

// Get IconService for rendering icons
$iconService = $container->get(\SeopressComposer\Services\IconService::class);

// Get opening hours from options
$openingHours = [
  'mo_fr' => get_field('opening_hours_mo_fr', 'option') ?: '08:00-20:00',
  'sa_so' => get_field('opening_hours_sa_so', 'option') ?: '10:00-18:00'
];

// Build Column 1: Contact
ob_start();
$contactData = $contactDataModel->get_data();


if (!empty($contactData)) {
  $contactFooter = $components->getContactFooter();
  echo $contactFooter->render($contactData);
}
?>

<?php
$col1 = ob_get_clean();


// Build Column 2: Services Part 1
ob_start();

$chunkSize = ceil(count($mainMenu) / 2);
$servicesSplit = $chunkSize > 0 ? array_chunk($mainMenu, $chunkSize) : [];
$servicesCol1 = $servicesSplit[0] ?? [];
$servicesCol2 = $servicesSplit[1] ?? [];

if (!empty($servicesCol1)) {
  $linkList = $components->getLinkList();
  echo $linkList->render($servicesCol1, true, 'space-y-2', 'Services', 'text-lg font-bold mb-4 text-white', $footerLinkColor);
}
$col2 = ob_get_clean();

// Build Column 3: Services Part 2
ob_start();
if (!empty($servicesCol2)) {
  $linkList = $components->getLinkList();
  // Pass plain text title and 'sr-only' as titleClass
  echo $linkList->render($servicesCol2, true, 'space-y-2', 'Weitere Services', 'sr-only', $footerLinkColor);
}
$col3 = ob_get_clean();

// Build Column 4: Öffnungszeiten
ob_start();
?>
<?php
if (!empty($aboutMenu)) {
  $linkList = $components->getLinkList();
?>
  <h3 class="text-lg font-bold mb-4">
    <a href="/ratgeber/" class="hover:underline text-white">Ratgeber</a>
  </h3>
<?php
  echo $linkList->render($aboutMenu, false, 'space-y-2', null, 'text-lg font-bold mb-4', $footerLinkColor);
}
?>

<div class="mt-8 pt-6 border-t border-white/10 text-sm opacity-90">
  <div class="font-bold text-lg mb-4 text-white">Öffnungszeiten</div>
  <div class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2">
    <div class="whitespace-nowrap opacity-80 font-medium">Mo-Fr:</div>
    <div><?= esc_html($openingHours['mo_fr'] ?? '08:00-20:00') ?></div>
    <div class="whitespace-nowrap opacity-80 font-medium">Sa-So:</div>
    <div><?= esc_html($openingHours['sa_so'] ?? '10:00-18:00') ?></div>
  </div>
</div>

<div class="mt-8 pt-6 border-t border-white/10">
  <div class="font-bold text-lg mb-4 text-white">Folgen Sie uns</div>
  <div class="flex gap-4">
    <?php
    $whatsappLink = '';
    if (!empty($contactData['contact_links'])) {
        foreach ($contactData['contact_links'] as $link) {
            if ($link['type'] === 'whatsapp') {
                $whatsappLink = $link['href'];
                break;
            }
        }
    }
    if (!empty($whatsappLink)): ?>
      <a href="<?= esc_url($whatsappLink) ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-primary transition-colors hover:scale-110" title="WhatsApp">
        <?= $iconService->getIcon('Whatsapp', ['inner_class' => 'fill-current w-5 h-5 text-white']) ?>
      </a>
    <?php endif; ?>
    <a href="#" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-[#1877F2] transition-colors hover:scale-110" title="Facebook">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
        </svg>
    </a>
    <a href="#" target="_blank" rel="noopener noreferrer" class="w-10 h-10 flex items-center justify-center rounded-full bg-white/10 hover:bg-[#E4405F] transition-colors hover:scale-110" title="Instagram">
        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
        </svg>
    </a>
  </div>
</div>
<?php
$col4 = ob_get_clean();

// Render using FourColumns layout
echo $components->getFourColumns()->render($col1, $col2, $col3, $col4, [
  'wrapper_class' => 'bg-secondary text-white py-24 lg:py-32',
  'container_class' => 'container mx-auto px-4',
  'grid_class' => 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8'
]);
