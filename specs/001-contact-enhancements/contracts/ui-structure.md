# Contracts: Contact Enhancements

## Front-End UI Structure (`page-contact.php`)

```html
<!-- The social media repeater loop will iterate over the ACF data directly. -->
<div class="bg-white dark:bg-slate-800 rounded-2xl p-8 border border-slate-200 dark:border-slate-700 shadow-sm">
    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-6">تابعنا على</h3>
    <div class="flex gap-4">
        <!-- Begin Repeater Loop: 'social_links' -->
        <?php if (have_rows('social_links', 'option')): ?>
            <?php while (have_rows('social_links', 'option')): the_row(); 
                $platform_name = get_sub_field('platform_name');
                $url = get_sub_field('url');
                $icon_svg = get_sub_field('icon_svg');
            ?>
                <a href="<?php echo esc_url($url); ?>" class="hover:text-primary" aria-label="<?php echo esc_attr($platform_name); ?>">
                    <?php if ($icon_svg): ?>
                        <span class="inline-block w-6 h-6 fill-current">
                            <?php echo $icon_svg; /* Ensure safe SVG rendering */ ?>
                        </span>
                    <?php else: ?>
                        <?php echo esc_html($platform_name); ?>
                    <?php endif; ?>
                </a>
            <?php endwhile; ?>
        <?php endif; ?>
        <!-- End Repeater Loop -->
    </div>
</div>
```

## Form Endpoint

- **Endpoint**: Current URL with POST data mapped to `mazaq_handle_contact_form()`.
- **Inputs Expected**: `name`, `email`, `subject`, `message`, `mazaq_contact_submit`, `mazaq_contact_nonce`, `website` (honeypot).
- **Backend Behavior**: `wp_insert_post` -> Returns Post ID if successful -> Sets `$_GET['contact_status'] = 'success'`.
