<?php
$hero_post_ids = mazaq_get_hero_post_ids();

if (empty($hero_post_ids)) {
    return;
}

if (count($hero_post_ids) === 1) :
    /* SINGLE POST PATH */
    $hero_post_id = $hero_post_ids[0];
?>
<section class="max-w-7xl mx-auto px-4 py-8">
    <a href="<?php echo esc_url(get_permalink($hero_post_id)); ?>" class="relative block w-full h-[60vh] md:h-[70vh] rounded-3xl overflow-hidden group cursor-pointer shadow-2xl">
        <?php if (has_post_thumbnail($hero_post_id)) : ?>
            <?php echo get_the_post_thumbnail($hero_post_id, 'hero-image', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-700']); ?>
        <?php else : ?>
            <div class="w-full h-full bg-gradient-to-t from-slate-900 to-slate-700"></div>
        <?php endif; ?>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/50 to-transparent z-10"></div>
        <div class="absolute bottom-0 left-0 w-full p-8 md:p-16 z-20">
            <?php $cats = get_the_category($hero_post_id); ?>
            <?php if (!empty($cats)) : ?>
                <span class="inline-block bg-primary text-slate-900 text-sm font-bold px-4 py-1.5 rounded-full mb-6"><?php echo esc_html($cats[0]->name); ?></span>
            <?php endif; ?>
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-[1.22] max-w-4xl group-hover:text-primary transition-colors"><?php echo esc_html(get_the_title($hero_post_id)); ?></h1>
            <p class="text-slate-300 text-lg md:text-xl max-w-2xl hidden md:block leading-relaxed mb-6"><?php echo esc_html(wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($hero_post_id)), 30)); ?></p>
            <div class="flex items-center gap-4 text-sm text-slate-400 font-mono">
                <span><?php echo esc_html(get_the_author_meta('display_name', (int) get_post_field('post_author', $hero_post_id))); ?></span>
                <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                <span><?php echo esc_html(get_the_date('j F Y', $hero_post_id)); ?></span>
            </div>
        </div>
    </a>
</section>
<?php else : ?>
    <?php /* SINGLE POST PATH fallback if for some reason count becomes less than 2 here (unlikely) */ ?>
    <?php if (count($hero_post_ids) < 2) : ?>
        <?php $hero_post_id = $hero_post_ids[0]; ?>
        <section class="max-w-7xl mx-auto px-4 py-8">
            <a href="<?php echo esc_url(get_permalink($hero_post_id)); ?>" class="relative block w-full h-[60vh] md:h-[70vh] rounded-3xl overflow-hidden group cursor-pointer shadow-2xl">
                <?php if (has_post_thumbnail($hero_post_id)) : ?>
                    <?php echo get_the_post_thumbnail($hero_post_id, 'hero-image', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-700']); ?>
                <?php else : ?>
                    <div class="w-full h-full bg-gradient-to-t from-slate-900 to-slate-700"></div>
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/50 to-transparent z-10"></div>
                <div class="absolute bottom-0 left-0 w-full p-8 md:p-16 z-20">
                    <?php $cats = get_the_category($hero_post_id); ?>
                    <?php if (!empty($cats)) : ?>
                        <span class="inline-block bg-primary text-slate-900 text-sm font-bold px-4 py-1.5 rounded-full mb-6"><?php echo esc_html($cats[0]->name); ?></span>
                    <?php endif; ?>
                    <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-[1.22] max-w-4xl group-hover:text-primary transition-colors"><?php echo esc_html(get_the_title($hero_post_id)); ?></h1>
                    <p class="text-slate-300 text-lg md:text-xl max-w-2xl hidden md:block leading-relaxed mb-6"><?php echo esc_html(wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($hero_post_id)), 30)); ?></p>
                    <div class="flex items-center gap-4 text-sm text-slate-400 font-mono">
                        <span><?php echo esc_html(get_the_author_meta('display_name', (int) get_post_field('post_author', $hero_post_id))); ?></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                        <span><?php echo esc_html(get_the_date('j F Y', $hero_post_id)); ?></span>
                    </div>
                </div>
            </a>
        </section>
    <?php else : ?>
        <section class="hero-carousel max-w-7xl mx-auto px-4 py-8 relative" data-interval="6000" data-total="<?php echo esc_attr(count($hero_post_ids)); ?>">
            <div class="hero-carousel__track relative w-full h-[60vh] md:h-[70vh] overflow-hidden rounded-3xl shadow-2xl">
                <?php foreach ($hero_post_ids as $slide_index => $slide_post_id) : ?>
                    <div class="hero-carousel__slide absolute inset-0 transition-opacity duration-700 <?php echo $slide_index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>" data-slide-index="<?php echo esc_attr($slide_index); ?>">
                        <a href="<?php echo esc_url(get_permalink($slide_post_id)); ?>" class="relative block w-full h-full group cursor-pointer">
                            <?php if (has_post_thumbnail($slide_post_id)) : ?>
                                <?php echo get_the_post_thumbnail($slide_post_id, 'hero-image', ['class' => 'w-full h-full object-cover transform group-hover:scale-105 transition-smooth duration-700']); ?>
                            <?php else : ?>
                                <div class="w-full h-full bg-gradient-to-t from-slate-900 to-slate-700"></div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/50 to-transparent z-10"></div>
                            <div class="absolute bottom-0 left-0 w-full p-8 md:p-16 z-20">
                                <?php $cats = get_the_category($slide_post_id); ?>
                                <?php if (!empty($cats)) : ?>
                                    <span class="inline-block bg-primary text-slate-900 text-sm font-bold px-4 py-1.5 rounded-full mb-6"><?php echo esc_html($cats[0]->name); ?></span>
                                <?php endif; ?>
                                <h2 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-[1.22] max-w-4xl group-hover:text-primary transition-colors"><?php echo esc_html(get_the_title($slide_post_id)); ?></h2>
                                <p class="text-slate-300 text-lg md:text-xl max-w-2xl hidden md:block leading-relaxed mb-6"><?php echo esc_html(wp_trim_words(wp_strip_all_tags((string) get_the_excerpt($slide_post_id)), 30)); ?></p>
                                <div class="flex items-center gap-4 text-sm text-slate-400 font-mono">
                                    <span><?php echo esc_html(get_the_author_meta('display_name', (int) get_post_field('post_author', $slide_post_id))); ?></span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                                    <span><?php echo esc_html(get_the_date('j F Y', $slide_post_id)); ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="hero-carousel__dots absolute bottom-12 left-0 w-full flex justify-center gap-3 z-30 pointer-events-auto">
                <?php for ($i = 0; $i < count($hero_post_ids); $i++) : ?>
                    <button class="hero-carousel__dot w-2.5 h-2.5 rounded-full bg-white/40 hover:bg-white/80 transition-all duration-300 <?php echo $i === 0 ? 'bg-white w-6 active' : ''; ?>" data-index="<?php echo esc_attr($i); ?>" aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'mazaq'), $i + 1)); ?>"></button>
                <?php endfor; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>


