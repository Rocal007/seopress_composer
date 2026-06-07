<?php

namespace SeopressComposer\Components;

/**
 * Accordion Component
 * Enhanced with DaisyUI Collapse component
 */
class Accordion
{
    use \SeopressComposer\Components\Traits\ModelRenderable;

    private SectionTitle $sectionTitle;
    private \SeopressComposer\Services\IconService $iconService;

    public function __construct(SectionTitle $sectionTitle, \SeopressComposer\Services\IconService $iconService)
    {
        $this->sectionTitle = $sectionTitle;
        $this->iconService = $iconService;
    }

    public function render(array $data = [], ?int $count = null, array $options = [])
    {
        if (empty($data))
            return '';

        // Detect structured data
        $heading = '';
        $items = [];

        if (isset($data['items']) && is_array($data['items'])) {
            // Structured Data
            $heading = $data['heading'] ?? '';
            $items = $data['items'];
        } else {
            // Flat List (Legacy support)
            $items = $data;
            $heading = $options['title'] ?? ''; // Fallback to option
        }

        if (empty($items)) {
            return '';
        }

        if ($count !== null) {
            $items = array_slice($items, 0, $count);
        }

        $options = array_merge([
            'multiple' => false,
            'section' => true,
        ], $options);

        $group_name = 'accordion-' . uniqid('', true);

        $bgClass = $options['bg_class'] ?? 'bg-base-100';

        ob_start(); ?>

        <?php if ($options['section']): ?>
            <section class="pt-24 lg:pt-32 <?= esc_attr($bgClass) ?>">
                <?= $this->sectionTitle->render($heading, '', 'h2', 'text-center mb-12') ?>
                <div class="container mx-auto px-4 max-w-3xl space-y-4 pb-24 lg:pb-32">
                <?php else: ?>
                    <div class="space-y-4">
                    <?php endif; ?>

                    <?php foreach ($items as $index => $item): ?>
                        <?= $this->renderOne($item, $index, $group_name, $options['multiple']); ?>
                    <?php endforeach; ?>

                    </div>
                    <?php if ($options['section']): ?>
            </section>
        <?php endif; ?>

    <?php
        return ob_get_clean();
    }

    /**
     * Render a single accordion item
     */
    public function renderOne(array $item, int $index = 0, string $group_name = 'accordion-group', bool $multiple = false): string
    {
        $title = $item['title'] ?? $item['faq_heading'] ?? '';
        $content = $item['content'] ?? $item['faq_content'] ?? '';

        ob_start();
    ?>
        <details class="bg-base-200 border border-base-300 group rounded-box mb-2" <?= $multiple ? '' : 'name="' . esc_attr($group_name) . '"' ?> <?= $index === 0 ? 'open' : '' ?>>
            <summary class="text-xl font-medium cursor-pointer list-none p-4 flex justify-between items-center outline-none">
                <div class="flex items-center gap-3">
                    <?php if (!empty($item['icon'])): ?><div class="w-8 h-8 shrink-0 text-primary inline-flex items-center justify-center" aria-hidden="true"><?= $this->iconService->getIcon($item['icon'], ['class' => 'w-full h-full fill-current']) ?></div><?php endif; ?><span class="flex-1 m-0 p-0"><?= wp_kses_post($title); ?></span>
                </div>
                <div class="w-6 h-6 flex-shrink-0 text-secondary transition-transform duration-200 group-open:rotate-180">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-full h-full"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                </div>
            </summary>
            <div class="p-4 pt-0">
                <div class="prose max-w-none">
                    <?= wpautop($content); ?>
                </div>
            </div>
        </details>
<?php
        return ob_get_clean();
    }

    /**
     * Render from model using index
     */
    public function renderFromModel(array $data, int $index, array $options = []): string
    {
        $items = isset($data['items']) ? $data['items'] : $data;

        if (!isset($items[$index])) {
            return '';
        }

        $options = array_merge([
            'group_name' => 'accordion-' . uniqid('', true),
            'multiple' => false,
            'checked_index' => -1
        ], $options);

        $effectiveIndex = ($options['checked_index'] === $index) ? 0 : 1;

        return $this->renderOne($items[$index], $effectiveIndex, $options['group_name'], $options['multiple']);
    }

    /**
     * Override indices getter for complex data structure
     */
    protected function getModelIndices(array $data): array
    {
        $items = isset($data['items']) ? $data['items'] : $data;
        return array_keys($items);
    }
}
