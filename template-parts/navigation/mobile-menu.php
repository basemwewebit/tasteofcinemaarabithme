<?php
declare(strict_types=1);

$delight_menu_exclude = (is_singular('post') && get_the_ID()) ? [(int) get_the_ID()] : [];
$delight_menu_picks = get_posts([
    'numberposts' => 3,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'no_found_rows' => true,
    'fields' => 'ids',
    'post__not_in' => $delight_menu_exclude,
]);
?>
<div id="menu-overlay" class="delight-menu-overlay fixed inset-0 z-50 hidden" aria-hidden="true"></div>
<aside
    id="mobile-menu"
    class="delight-menu"
    role="dialog"
    aria-modal="true"
    aria-hidden="true"
    inert
    aria-label="<?php esc_attr_e('قائمة مذاق السينما', 'mazaq'); ?>"
>
    <div class="delight-menu__stage" aria-hidden="true"></div>

    <div class="delight-menu__header">
        <a
            href="<?php echo esc_url(home_url('/')); ?>"
            class="delight-menu__brand"
            aria-label="<?php esc_attr_e('الرئيسية', 'mazaq'); ?>"
        >
            <img
                src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo.webp'); ?>"
                alt="<?php bloginfo('name'); ?>"
                class="delight-menu__logo dark:brightness-125"
                width="474"
                height="460"
                data-no-lazy="1"
            >
        </a>

        <button
            id="close-menu"
            type="button"
            class="delight-menu__close"
            aria-label="<?php esc_attr_e('إغلاق القائمة', 'mazaq'); ?>"
        >
            <svg class="w-5 h-5" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <div class="delight-menu__body">
        <nav aria-label="<?php esc_attr_e('أبواب المجلة', 'mazaq'); ?>" class="delight-menu__nav">
            <p class="delight-menu__eyebrow"><?php esc_html_e('أبواب المجلة', 'mazaq'); ?></p>
            <?php
            wp_nav_menu([
                'theme_location' => 'primary-menu',
                'container' => false,
                'menu_class' => 'delight-menu__nav-list',
                'fallback_cb' => false,
                'depth' => 1,
            ]);
            ?>
        </nav>

        <?php if (!empty($delight_menu_picks)) : ?>
            <section class="delight-menu__picks-section" aria-labelledby="delight-menu-picks-title">
                <p id="delight-menu-picks-title" class="delight-menu__eyebrow">
                    <?php esc_html_e('قراءات مختارة', 'mazaq'); ?>
                </p>
                <ol class="delight-menu__picks">
                    <?php foreach ($delight_menu_picks as $delight_pick_index => $delight_pick_id) :
                        $delight_pick_id = (int) $delight_pick_id;
                        $delight_pick_categories = get_the_category($delight_pick_id);
                        $delight_pick_category = !empty($delight_pick_categories) ? $delight_pick_categories[0] : null;
                        $delight_pick_title = (string) get_the_title($delight_pick_id);
                        $delight_pick_thumb_id = (int) get_post_thumbnail_id($delight_pick_id);
                        $delight_pick_alt = function_exists('mazaq_get_post_thumbnail_alt')
                            ? mazaq_get_post_thumbnail_alt($delight_pick_id, $delight_pick_title)
                            : $delight_pick_title;
                        $delight_pick_number = str_pad((string) ($delight_pick_index + 1), 2, '0', STR_PAD_LEFT);
                    ?>
                        <li class="delight-menu__pick">
                            <a href="<?php echo esc_url(get_permalink($delight_pick_id)); ?>" class="delight-menu__pick-link">
                                <span class="delight-menu__pick-index" aria-hidden="true"><?php echo esc_html($delight_pick_number); ?></span>
                                <span class="delight-menu__pick-media">
                                    <?php if ($delight_pick_thumb_id > 0) : ?>
                                        <?php echo wp_get_attachment_image(
                                            $delight_pick_thumb_id,
                                            [128, 128],
                                            false,
                                            [
                                                'class' => 'delight-menu__pick-image',
                                                'alt' => $delight_pick_alt,
                                                'loading' => 'lazy',
                                                'decoding' => 'async',
                                            ]
                                        ); ?>
                                    <?php else : ?>
                                        <span class="delight-menu__pick-fallback" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M4 5h16v14H4z" stroke-linejoin="round"/>
                                                <path d="M4 9h2M4 13h2M4 17h2M18 9h2M18 13h2M18 17h2" stroke-linecap="round"/>
                                            </svg>
                                        </span>
                                    <?php endif; ?>
                                </span>
                                <span class="delight-menu__pick-body">
                                    <?php if ($delight_pick_category) : ?>
                                        <span class="delight-menu__pick-cat"><?php echo esc_html($delight_pick_category->name); ?></span>
                                    <?php endif; ?>
                                    <strong class="delight-menu__pick-title"><?php echo esc_html($delight_pick_title); ?></strong>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </section>
        <?php endif; ?>
    </div>

    <div class="delight-menu__footer">
        <?php get_template_part('template-parts/ads/ad-mobile-menu'); ?>
    </div>
</aside>
