<?php

declare(strict_types=1);

function mazaq_get_ad_slot(string $slot_name): string
{
    if (!function_exists('get_field')) {
        return '';
    }
    return (string) get_field($slot_name, 'option');
}

function mazaq_render_ad(string $slot_name, string $format = 'responsive', string $classes = ''): void
{
    $slot_id = mazaq_get_ad_slot($slot_name);

    $class_attr = trim('ad-container ' . $classes);
    echo '<div class="' . esc_attr($class_attr) . '" data-slot-name="' . esc_attr($slot_name) . '">';

    $dummy_ads_enabled = false; // Set to false to disable dummy ads and show empty box or AdSense

    if ($slot_id && !$dummy_ads_enabled) {
        echo '<ins class="adsbygoogle" style="display:block" data-ad-client="' . esc_attr((string) get_field('adsense_publisher_id', 'option')) . '" data-ad-slot="' . esc_attr($slot_id) . '" data-ad-format="' . esc_attr($format) . '" data-full-width-responsive="true"></ins>';
        echo '<script>(adsbygoogle=window.adsbygoogle||[]).push({});</script>';
    } else {
        if ($dummy_ads_enabled) {
            // Display purely HTML dummy banner for testing layout (no external images)
            $text = ($format === 'horizontal') ? 'بانر إعلاني أسفل المقال' : 'إعلان مربع';
            $height_class = ($format === 'horizontal') ? 'min-h-[90px]' : 'min-h-[250px]';
            
            if ($format === 'fluid') { // For in-article ads typically fluid/responsive
                $text = 'إعلان داخل المقال';
                $height_class = 'min-h-[150px]';
            }

            echo '<div class="flex items-center justify-center w-full ' . esc_attr($height_class) . ' bg-slate-100 dark:bg-[#1A1F30] border-2 border-dashed border-slate-300 dark:border-[#FDB813]/30 rounded-xl relative group transition-colors hover:bg-slate-200 dark:hover:bg-[#1A1F30]/80">';
            echo '<span class="text-slate-400 dark:text-[#FDB813]/80 font-bold text-lg md:text-xl text-center px-4 group-hover:dark:text-[#FDB813] transition-colors">' . esc_html($text) . '</span>';
            echo '<span class="absolute top-2 right-2 bg-slate-300 dark:bg-[#FDB813]/20 text-slate-700 dark:text-[#FDB813] text-[10px] font-bold px-2 py-0.5 rounded tracking-widest uppercase">Ad</span>';
            echo '</div>';
        } else {
            // Default empty placeholder
            echo '<div class="flex items-center justify-center w-full h-full bg-slate-50 dark:bg-[#1A1F30] border-2 border-dashed border-slate-200 dark:border-white/10 rounded-xl text-slate-400 dark:text-slate-500 font-medium tracking-wide min-h-[100px]">مساحة إعلانية</div>';
        }
    }

    echo '</div>';
}

function mazaq_adsense_head_script(): void
{
    if (!function_exists('get_field')) {
        return;
    }

    $publisher = (string) get_field('adsense_publisher_id', 'option');
    if (!$publisher) {
        return;
    }

    echo '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . esc_attr($publisher) . '" crossorigin="anonymous"></script>';
}
add_action('wp_head', 'mazaq_adsense_head_script');

function mazaq_inject_in_article_ads(string $content): string
{
    if (!is_single() || is_admin()) {
        return $content;
    }

    // Optional: remove early exit so the ad placeholders are ALWAYS shown in admin/preview.
    // if (!mazaq_get_ad_slot('ad_slot_in_article')) {
    //    return $content;
    // }

    $parts = explode('</p>', $content);
    if (count($parts) < 4) {
        return $content;
    }

    $new_content = '';
    foreach ($parts as $index => $part) {
        if (trim($part) === '') {
            continue;
        }
        $new_content .= $part . '</p>';
        if (($index + 1) % 3 === 0) {
            ob_start();
            get_template_part('template-parts/ads/ad-in-article');
            $new_content .= (string) ob_get_clean();
        }
    }

    return $new_content;
}
add_filter('the_content', 'mazaq_inject_in_article_ads', 20);
