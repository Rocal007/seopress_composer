<?php

namespace SeopressComposer\Components;

/**
 * Card Component
 * Enhanced with DaisyUI Card component
 */
class Card
{
    use \SeopressComposer\Components\Traits\ModelRenderable;

    public function render(array $cards = [], string $bg_class = 'bg-base-100'): string
    {
        if (empty($cards))
            return '';

        ob_start(); ?>
        <section class="py-24 lg:py-32 <?= esc_attr($bg_class) ?>">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($cards as $card): ?>
                        <?= $this->renderOne($card); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php
        return ob_get_clean();
    }

    /**
     * Render a single card
     */
    public function renderOne(array $card): string
    {
        ob_start();
    ?>
        <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300 border border-base-200 h-full flex flex-col">
            <?php
            $badges = $card['badges'] ?? [];
            $icon = $card['icon'] ?? '';
            $imgUrl = $card['image'] ?? $card['image_url'] ?? '';
            $iconService = seopress_container()->get(\SeopressComposer\Services\IconService::class);

            if (!empty($imgUrl) && strpos($imgUrl, 'http') === 0) {
                $clean_title = wp_strip_all_tags(seopress_container()->get(\SeopressComposer\Services\TextReplacementService::class)->clean_title($card['title'] ?? 'Card'));
                $imgUrl = seopress_container()->get(\SeopressComposer\Services\ImageDownloadService::class)->get_local_image_url($imgUrl, $clean_title . ' card', $clean_title . ' card');
            }

            if ($imgUrl): ?>
                <figure class="relative">
                    <img src="<?= esc_url($imgUrl) ?>" alt="<?= esc_attr($card['title'] ?? 'Card Image') ?>"
                        title="<?= esc_attr($card['title'] ?? 'Card Image') ?>" class="w-full h-48 object-cover" />
                    <?php if (!empty($badges) && is_array($badges)): ?>
                        <div class="absolute top-3 right-3 flex flex-col gap-1 items-end z-10">
                            <?php foreach ($badges as $badge): ?>
                                <span class="badge bg-white text-secondary badge-sm uppercase text-[10px] font-bold shadow-lg border-none backdrop-blur-sm">
                                    <?= esc_html($badge) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </figure>
            <?php elseif ($icon): ?>
                <figure class="relative pt-8 flex justify-center">
                    <?php
                    $defaultOptions = ['size' => 'xl', 'invert' => true];
                    $cardOptions = $card['icon_options'] ?? [];
                    $iconOptions = array_merge($defaultOptions, $cardOptions);
                    ?>
                    <?= $iconService->getIconHtml($icon, $card['title'] ?? '', $iconOptions) ?>
                    <?php if (!empty($badges) && is_array($badges)): ?>
                        <div class="absolute top-3 right-3 flex flex-col gap-1 items-end z-10">
                            <?php foreach ($badges as $badge): ?>
                                <span class="badge bg-white text-secondary badge-sm uppercase text-[10px] font-bold shadow-lg border-none backdrop-blur-sm">
                                    <?= esc_html($badge) ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </figure>
            <?php endif; ?>

            <div class="card-body">
                <div class="flex flex-col gap-1 mb-2">
                    <?php if (!empty($card['supertitle'])): ?>
                        <span class="text-[10px] uppercase opacity-60 font-bold tracking-wider leading-none">
                            <?= esc_html($card['supertitle']); ?>
                        </span>
                    <?php endif; ?>

                    <div class="flex items-start gap-2">
                        <?php if ($imgUrl && $icon): ?>
                            <?= $iconService->getIconHtml($icon, $card['title'] ?? '', ['size' => 'md', 'invert' => true]) ?>
                        <?php endif; ?>

                        <div class="flex-1">
                            <h3 class="card-title text-xl m-0 leading-tight"><?= wp_kses_post($card['title'] ?? 'Card'); ?></h3>
                        </div>

                        <?php if (!empty($card['claim'])): ?>
                            <div class="group relative flex-shrink-0">
                                <button class="btn btn-circle btn-ghost btn-xs text-primary/50 hover:text-primary transition-colors"
                                    aria-label="Informationen zu <?= esc_attr($card['title'] ?? 'diesem Bereich') ?> anzeigen">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="w-4 h-4 stroke-current">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                                <!-- Speech Bubble Tooltip -->
                                <div class="absolute bottom-full right-0 mb-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 pointer-events-none">
                                    <div class="relative bg-primary text-primary-content px-4 py-3 rounded-xl shadow-2xl max-w-xs text-sm">
                                        <?= esc_html($card['claim']) ?>
                                        <!-- Speech bubble arrow -->
                                        <div class="absolute top-full right-4 w-0 h-0 border-l-8 border-r-8 border-t-8 border-l-transparent border-r-transparent border-t-primary"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="text-base-content/80"><?= esc_html($card['content'] ?? ''); ?></p>
                <?php if (!empty($card['link'])): ?>
                    <div class="card-actions justify-end mt-4">
                        <a href="<?= esc_url($card['link']) ?>" class="btn btn-primary btn-sm"
                            title="<?= esc_attr($card['title'] ?? 'Card') ?> ansehen">Mehr erfahren</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?php
        return ob_get_clean();
    }

    /**
     * Render multiple cards
     *
     * @param array $cards Array of cards
     * @return array Array of rendered HTML strings
     */
    public function renderMany(array $cards): array
    {
        return array_map([$this, 'renderOne'], $cards);
    }

    /**
     * Render from model using index
     */
    public function renderFromModel(array $data, int $index): string
    {
        $items = isset($data['items']) ? $data['items'] : $data;
        if (!isset($items[$index])) {
            return '';
        }
        return $this->renderOne($items[$index]);
    }

    protected function getModelIndices(array $data): array
    {
        $items = isset($data['items']) ? $data['items'] : $data;
        return array_keys($items);
    }
}
