<?php
namespace SeopressComposer\Components;

/**
 * Slider Component
 * Enhanced with DaisyUI Cards and Buttons
 */
class Slider
{
    public function render(array $media_items = [], array $options = []): string
    {
        if (empty($media_items))
            return '';

        $defaults = [
            'id' => 'media-slider-' . uniqid(),
            'type' => 'video',
            'per_view' => 4,
            'per_view_mobile' => 1,
            'per_view_tablet' => 2,
            'autoplay' => false,
            'autoplay_delay' => 5000,
            'loop' => true,
            'show_navigation' => true,
            'show_pagination' => true,
            'theme' => 'cards',
            'button_text' => 'mehr erfahren',
            'container_classes' => ''
        ];

        $options = array_merge($defaults, $options);
        $slider_id = $options['id'];
        $total_items = count($media_items);

        ob_start();
        ?>
        <section id="<?= esc_attr($slider_id) ?>"
            class="media-slider-component py-24 lg:py-32 <?= esc_attr($options['container_classes']) ?>" data-media-slider
            data-slider-config='<?= json_encode([
                'id' => $slider_id,
                'autoplay' => $options['autoplay'],
                'delay' => $options['autoplay_delay'],
                'loop' => $options['loop'],
                'perView' => [
                    'mobile' => $options['per_view_mobile'],
                    'tablet' => $options['per_view_tablet'],
                    'desktop' => $options['per_view']
                ],
                'totalItems' => $total_items
            ]) ?>'>
            <div class="container mx-auto px-4 relative group">
                <div class="slider-track overflow-hidden">
                    <div class="slider-wrapper flex transition-transform duration-300 ease-in-out">
                        <?php foreach ($media_items as $index => $item): ?>
                            <div class="slider-slide flex-shrink-0 px-2" data-slide-index="<?= $index ?>" style="width: 100%;">
                                <!-- Width is handled by JS but fallback 100% just in case -->
                                <?= $this->render_media_card($item, $options, $index); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($options['show_navigation'] && $total_items > $options['per_view_mobile']): ?>
                    <div
                        class="absolute top-1/2 -ml-4 -mr-4 left-0 right-0 flex justify-between transform -translate-y-1/2 pointer-events-none">
                        <button class="btn btn-circle glass pointer-events-auto slider-btn-prev shadow-lg"
                            aria-label="Vorherige Medien">❮</button>
                        <button class="btn btn-circle glass pointer-events-auto slider-btn-next shadow-lg"
                            aria-label="Nächste Medien">❯</button>
                    </div>
                <?php endif; ?>

                <?php if ($options['show_pagination']): ?>
                    <div class="flex justify-center w-full py-4 gap-2 slider-pagination">
                        <?php for ($i = 0; $i < ceil($total_items / $options['per_view_mobile']); $i++): ?>
                            <button class="btn btn-xs btn-circle slider-dot <?= $i === 0 ? 'btn-active' : 'btn-ghost' ?>"
                                data-slide="<?= $i ?>" aria-label="Slide <?= $i + 1 ?>"></button>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }

    private function render_media_card(array $media, array $options, int $index): string
    {
        $media = wp_parse_args($media, [
            'video' => '',
            'image' => '',
            'category_name' => '',
            'content' => '',
            'title' => '',
            'url' => '#',
            'icon' => '',
            'duration' => ''
        ]);

        $title = $media['title'] ?: strip_tags($media['content']);
        $excerpt = $media['content'] && !$media['title'] ? substr(strip_tags($media['content']), 0, 120) . '...' : '';

        ob_start();
        ?>
        <article class="card bg-base-100 shadow-xl h-full hover:shadow-2xl transition-all duration-300">
            <figure class="relative aspect-video">
                <?php if ($options['type'] === 'video' && $media['video']): ?>
                    <img src="https://img.youtube.com/vi/<?= esc_attr($media['video']); ?>/mqdefault.jpg"
                        alt="Video Vorschau: <?= esc_attr($title); ?>" title="Video abspielen: <?= esc_attr($title); ?>"
                        class="w-full h-full object-cover">
                    <!-- Play Overlay -->
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 hover:bg-black/20 transition-colors">
                        <div class="w-12 h-12 rounded-full bg-base-100/90 flex items-center justify-center pl-1">
                            <span class="text-primary text-xl">▶</span>
                        </div>
                    </div>
                    <a href="https://www.youtube.com/watch?v=<?= esc_attr($media['video']); ?>" class="absolute inset-0 z-10"
                        aria-label="Play Video" title="Video abspielen: <?= esc_attr($title); ?>"></a>
                <?php elseif ($options['type'] === 'image' && $media['image']): ?>
                    <img src="<?= esc_url($media['image']); ?>" alt="<?= esc_attr($title); ?>" title="<?= esc_attr($title); ?>"
                        class="w-full h-full object-cover">
                    <a href="<?= esc_url($media['url']); ?>" class="absolute inset-0 z-10"
                        title="<?= esc_attr($title); ?> - Details"></a>
                <?php else: ?>
                    <div class="w-full h-full bg-base-200 flex items-center justify-center">
                        <span class="text-base-content/30">No Media</span>
                    </div>
                <?php endif; ?>

                <?php if ($media['category_name']): ?>
                    <div class="absolute top-2 left-2 badge badge-secondary">
                        <?= esc_html($media['category_name']); ?>
                    </div>
                <?php endif; ?>
            </figure>

            <div class="card-body p-5">
                <h3 class="card-title text-base line-clamp-2 min-h-[3rem] text-secondary">
                    <a href="<?= esc_url($media['url']); ?>" class="hover:text-primary transition-colors"
                        title="<?= esc_attr($title); ?> Details">
                        <?= esc_html($title); ?>
                    </a>
                </h3>
                <?php if ($excerpt): ?>
                    <p class="text-sm text-base-content/70 line-clamp-3"><?= esc_html($excerpt); ?></p>
                <?php endif; ?>

                <div class="card-actions justify-end mt-4">
                    <a href="<?= esc_url($media['url']); ?>" class="btn btn-primary btn-sm btn-outline rounded-full"
                        title="<?= esc_attr($title); ?> erfahren">
                        <?= esc_html($options['button_text']); ?>
                    </a>
                </div>
            </div>
        </article>
        <?php
        return ob_get_clean();
    }
}
