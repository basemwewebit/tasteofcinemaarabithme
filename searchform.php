<?php
/**
 * Site search field: the boxed operative input shared by the search room,
 * the 404 recovery room, and anywhere get_search_form() is used.
 *
 * @package Mazaq
 */

$search_input_id = wp_unique_id('search-input-');
?>
<form role="search" method="get" class="search-field" action="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('البحث في الموقع', 'mazaq'); ?>">
    <label for="<?php echo esc_attr($search_input_id); ?>" class="sr-only"><?php esc_html_e('ابحث عن فيلم أو مقال', 'mazaq'); ?></label>
    <input type="search" id="<?php echo esc_attr($search_input_id); ?>" class="search-field__input" placeholder="<?php esc_attr_e('ابحث عن فيلم أو مقال...', 'mazaq'); ?>" value="<?php echo esc_attr(get_search_query()); ?>" name="s" autocomplete="off" autocorrect="off" spellcheck="false" dir="auto" enterkeyhint="search" />
    <button type="submit" class="search-field__btn" aria-label="<?php esc_attr_e('بحث', 'mazaq'); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
    </button>
</form>
