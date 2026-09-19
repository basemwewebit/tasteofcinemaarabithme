<?php

/**
 * Notification admin surface: the theme settings page behind Push
 * notifications. Owns its admin-post actions, the settings/test/preview
 * handlers, and the runtime-status read model. Thin caller of the settings,
 * store, content, dispatch, and schedule seams.
 */

declare(strict_types=1);

use Minishlink\WebPush\VAPID;

const MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_ACTION = 'mazaq_browser_notifications_save_settings';
const MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_NONCE = 'mazaq_browser_notifications_save_settings_nonce';
const MAZAQ_BROWSER_NOTIFICATIONS_TEST_ACTION = 'mazaq_browser_notifications_send_test';
const MAZAQ_BROWSER_NOTIFICATIONS_TEST_NONCE = 'mazaq_browser_notifications_send_test_nonce';
const MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_ACTION = 'mazaq_browser_notifications_send_daily_preview';
const MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_NONCE = 'mazaq_browser_notifications_send_daily_preview_nonce';

/**
 * Return high-level runtime status values for the admin screen.
 *
 * @return array<string, mixed>
 */
function mazaq_browser_notifications_get_runtime_status(): array
{
    $settings = mazaq_browser_notifications_get_settings();
    $home_url = home_url('/');
    $scheme = (string) wp_parse_url($home_url, PHP_URL_SCHEME);
    $next_daily_timestamp = wp_next_scheduled(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT);

    return [
        'home_url' => $home_url,
        'is_https' => $scheme === 'https',
        'push_ready' => mazaq_browser_notifications_is_push_ready(),
        'next_daily_timestamp' => $next_daily_timestamp ? (int) $next_daily_timestamp : 0,
        'daily_random_time' => (string) $settings['daily_random_time'],
        'service_worker_url' => home_url('/mazaq-sw.js'),
        'can_generate_keys' => class_exists(VAPID::class),
        'vapid_configured' => $settings['vapid_public_key'] !== '' && $settings['vapid_private_key'] !== '',
    ];
}

/**
 * Add the admin settings page.
 */
function mazaq_browser_notifications_register_admin_page(): void
{
    add_theme_page(
        __('تنبيهات المتصفح', 'mazaq'),
        __('تنبيهات المتصفح', 'mazaq'),
        'manage_options',
        'mazaq-browser-notifications',
        'mazaq_browser_notifications_render_admin_page'
    );
}
add_action('admin_menu', 'mazaq_browser_notifications_register_admin_page');

/**
 * Save admin settings.
 */
function mazaq_browser_notifications_handle_settings_save(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('غير مسموح لك بتنفيذ هذا الإجراء.', 'mazaq'));
    }

    check_admin_referer(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_NONCE);

    $settings = mazaq_browser_notifications_get_settings();
    $settings['enabled'] = !empty($_POST['enabled']) ? 1 : 0;
    $settings['daily_random_enabled'] = !empty($_POST['daily_random_enabled']) ? 1 : 0;
    if (!empty($_POST['quick_toggle_daily_random'])) {
        $settings['daily_random_enabled'] = !empty($settings['daily_random_enabled']) ? 0 : 1;
    }
    $settings['daily_random_time'] = isset($_POST['daily_random_time'])
        ? mazaq_browser_notifications_normalize_daily_time(wp_unslash((string) $_POST['daily_random_time']))
        : (string) ($settings['daily_random_time'] ?? '09:00');
    $settings['new_post_enabled'] = !empty($_POST['new_post_enabled']) ? 1 : 0;
    $settings['vapid_public_key'] = isset($_POST['vapid_public_key']) ? sanitize_text_field(wp_unslash((string) $_POST['vapid_public_key'])) : '';
    $settings['vapid_private_key'] = isset($_POST['vapid_private_key']) ? sanitize_text_field(wp_unslash((string) $_POST['vapid_private_key'])) : '';
    $settings['default_icon_url'] = isset($_POST['default_icon_url']) ? esc_url_raw(wp_unslash((string) $_POST['default_icon_url'])) : '';
    $settings['default_badge_url'] = isset($_POST['default_badge_url']) ? esc_url_raw(wp_unslash((string) $_POST['default_badge_url'])) : '';
    $settings['prompt_title'] = isset($_POST['prompt_title']) ? sanitize_text_field(wp_unslash((string) $_POST['prompt_title'])) : '';
    $settings['prompt_body'] = isset($_POST['prompt_body']) ? sanitize_textarea_field(wp_unslash((string) $_POST['prompt_body'])) : '';

    if (!empty($_POST['generate_vapid_keys']) && class_exists(VAPID::class)) {
        $keys = VAPID::createVapidKeys();
        $settings['vapid_public_key'] = isset($keys['publicKey']) ? (string) $keys['publicKey'] : $settings['vapid_public_key'];
        $settings['vapid_private_key'] = isset($keys['privateKey']) ? (string) $keys['privateKey'] : $settings['vapid_private_key'];
    }

    mazaq_browser_notifications_update_settings($settings);
    mazaq_browser_notifications_clear_event(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_EVENT);
    mazaq_browser_notifications_schedule_daily_event();

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'mazaq-browser-notifications',
                'updated' => '1',
            ],
            admin_url('themes.php')
        )
    );
    exit;
}
add_action('admin_post_' . MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_ACTION, 'mazaq_browser_notifications_handle_settings_save');

/**
 * Send a one-off test notification to active subscriptions.
 */
function mazaq_browser_notifications_handle_test_send(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('غير مسموح لك بتنفيذ هذا الإجراء.', 'mazaq'));
    }

    check_admin_referer(MAZAQ_BROWSER_NOTIFICATIONS_TEST_NONCE);

    $result = mazaq_browser_notifications_send_push_payload(
        mazaq_browser_notifications_build_test_payload()
    );

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'mazaq-browser-notifications',
                'test_sent' => '1',
                'test_client_ready' => !empty($result['client_ready']) ? '1' : '0',
                'test_queued' => (string) (int) $result['queued'],
                'test_success' => (string) (int) $result['success'],
                'test_invalidated' => (string) (int) $result['invalidated'],
            ],
            admin_url('themes.php')
        )
    );
    exit;
}
add_action('admin_post_' . MAZAQ_BROWSER_NOTIFICATIONS_TEST_ACTION, 'mazaq_browser_notifications_handle_test_send');

/**
 * Send a preview of the daily random suggestion immediately.
 */
function mazaq_browser_notifications_handle_daily_preview_send(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('غير مسموح لك بتنفيذ هذا الإجراء.', 'mazaq'));
    }

    check_admin_referer(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_NONCE);

    $payload = mazaq_browser_notifications_build_daily_preview_payload();
    $result = empty($payload)
        ? ['client_ready' => mazaq_browser_notifications_is_push_ready(), 'queued' => 0, 'success' => 0, 'invalidated' => 0, 'has_payload' => false]
        : array_merge(
            mazaq_browser_notifications_send_push_payload($payload),
            ['has_payload' => true]
        );

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'mazaq-browser-notifications',
                'daily_preview_sent' => '1',
                'daily_preview_client_ready' => !empty($result['client_ready']) ? '1' : '0',
                'daily_preview_has_payload' => !empty($result['has_payload']) ? '1' : '0',
                'daily_preview_queued' => (string) (int) $result['queued'],
                'daily_preview_success' => (string) (int) $result['success'],
                'daily_preview_invalidated' => (string) (int) $result['invalidated'],
            ],
            admin_url('themes.php')
        )
    );
    exit;
}
add_action('admin_post_' . MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_ACTION, 'mazaq_browser_notifications_handle_daily_preview_send');

/**
 * Render the admin settings screen.
 */
function mazaq_browser_notifications_render_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = mazaq_browser_notifications_get_settings();
    $counts = mazaq_browser_notifications_get_subscription_counts();
    $runtime = mazaq_browser_notifications_get_runtime_status();
    $is_updated = isset($_GET['updated']) && $_GET['updated'] === '1';
    $is_test_sent = isset($_GET['test_sent']) && $_GET['test_sent'] === '1';
    $test_client_ready = isset($_GET['test_client_ready']) && $_GET['test_client_ready'] === '1';
    $test_queued = isset($_GET['test_queued']) ? (int) $_GET['test_queued'] : 0;
    $test_success = isset($_GET['test_success']) ? (int) $_GET['test_success'] : 0;
    $test_invalidated = isset($_GET['test_invalidated']) ? (int) $_GET['test_invalidated'] : 0;
    $is_daily_preview_sent = isset($_GET['daily_preview_sent']) && $_GET['daily_preview_sent'] === '1';
    $daily_preview_client_ready = isset($_GET['daily_preview_client_ready']) && $_GET['daily_preview_client_ready'] === '1';
    $daily_preview_has_payload = isset($_GET['daily_preview_has_payload']) && $_GET['daily_preview_has_payload'] === '1';
    $daily_preview_queued = isset($_GET['daily_preview_queued']) ? (int) $_GET['daily_preview_queued'] : 0;
    $daily_preview_success = isset($_GET['daily_preview_success']) ? (int) $_GET['daily_preview_success'] : 0;
    $daily_preview_invalidated = isset($_GET['daily_preview_invalidated']) ? (int) $_GET['daily_preview_invalidated'] : 0;
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('تنبيهات المتصفح', 'mazaq'); ?></h1>

        <?php if ($is_updated) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('تم حفظ إعدادات التنبيهات.', 'mazaq'); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($is_test_sent) : ?>
            <div class="notice <?php echo $test_client_ready ? 'notice-info' : 'notice-warning'; ?> is-dismissible">
                <p>
                    <?php
                    echo esc_html(
                        $test_client_ready
                            ? sprintf(
                                __('تم إرسال التنبيه التجريبي. تم وضع %1$d اشتراك في الإرسال، ونجح %2$d، وتم تعطيل %3$d غير صالح.', 'mazaq'),
                                $test_queued,
                                $test_success,
                                $test_invalidated
                            )
                            : __('لم يتم إرسال التنبيه التجريبي لأن Push غير جاهز بعد. تأكد من HTTPS ومفاتيح VAPID.', 'mazaq')
                    );
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($is_daily_preview_sent) : ?>
            <div class="notice <?php echo ($daily_preview_client_ready && $daily_preview_has_payload) ? 'notice-info' : 'notice-warning'; ?> is-dismissible">
                <p>
                    <?php
                    if (!$daily_preview_has_payload) {
                        echo esc_html__('لم يتم إرسال تجربة الاقتراح اليومي لأنه لا يوجد مقال منشور صالح للاختيار.', 'mazaq');
                    } elseif (!$daily_preview_client_ready) {
                        echo esc_html__('لم يتم إرسال تجربة الاقتراح اليومي لأن Push غير جاهز بعد. تأكد من HTTPS ومفاتيح VAPID.', 'mazaq');
                    } else {
                        echo esc_html(
                            sprintf(
                                __('تم إرسال تجربة الاقتراح اليومي. تم وضع %1$d اشتراك في الإرسال، ونجح %2$d، وتم تعطيل %3$d غير صالح.', 'mazaq'),
                                $daily_preview_queued,
                                $daily_preview_success,
                                $daily_preview_invalidated
                            )
                        );
                    }
                    ?>
                </p>
            </div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin:20px 0 24px;">
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('المشتركون النشطون', 'mazaq'); ?></div>
                <div style="font-size:28px;font-weight:700;"><?php echo esc_html((string) $counts['active']); ?></div>
            </div>
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('إجمالي السجلات', 'mazaq'); ?></div>
                <div style="font-size:28px;font-weight:700;"><?php echo esc_html((string) $counts['total']); ?></div>
            </div>
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('حالة Push', 'mazaq'); ?></div>
                <div style="font-size:16px;font-weight:600;color:<?php echo $runtime['push_ready'] ? '#008a20' : '#b32d2e'; ?>;">
                    <?php echo esc_html($runtime['push_ready'] ? __('جاهز', 'mazaq') : __('غير مكتمل', 'mazaq')); ?>
                </div>
            </div>
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('التنبيه اليومي القادم', 'mazaq'); ?></div>
                <div style="font-size:16px;font-weight:600;">
                    <?php
                    echo esc_html(
                        $runtime['next_daily_timestamp'] > 0
                            ? wp_date(get_option('date_format') . ' ' . get_option('time_format'), $runtime['next_daily_timestamp'], wp_timezone())
                            : __('غير مجدول', 'mazaq')
                    );
                    ?>
                </div>
            </div>
            <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
                <div style="font-size:13px;color:#646970;margin-bottom:8px;"><?php esc_html_e('الاقتراح اليومي العشوائي', 'mazaq'); ?></div>
                <div style="font-size:16px;font-weight:600;">
                    <?php
                    echo esc_html(
                        sprintf(
                            __('إرسال اقتراح عشوائي يومي الساعة %s', 'mazaq'),
                            (string) $runtime['daily_random_time']
                        )
                    );
                    ?>
                </div>
                <div style="margin-top:8px;font-size:13px;color:<?php echo !empty($settings['daily_random_enabled']) ? '#008a20' : '#646970'; ?>;">
                    <?php echo esc_html(!empty($settings['daily_random_enabled']) ? __('مفعّل حالياً', 'mazaq') : __('غير مفعّل حالياً', 'mazaq')); ?>
                </div>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:12px;">
                    <input type="hidden" name="action" value="<?php echo esc_attr(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_ACTION); ?>">
                    <input type="hidden" name="enabled" value="<?php echo esc_attr((string) (int) !empty($settings['enabled'])); ?>">
                    <input type="hidden" name="daily_random_enabled" value="<?php echo esc_attr((string) (int) !empty($settings['daily_random_enabled'])); ?>">
                    <input type="hidden" name="daily_random_time" value="<?php echo esc_attr((string) $settings['daily_random_time']); ?>">
                    <input type="hidden" name="new_post_enabled" value="<?php echo esc_attr((string) (int) !empty($settings['new_post_enabled'])); ?>">
                    <input type="hidden" name="vapid_public_key" value="<?php echo esc_attr((string) $settings['vapid_public_key']); ?>">
                    <input type="hidden" name="vapid_private_key" value="<?php echo esc_attr((string) $settings['vapid_private_key']); ?>">
                    <input type="hidden" name="default_icon_url" value="<?php echo esc_attr((string) $settings['default_icon_url']); ?>">
                    <input type="hidden" name="default_badge_url" value="<?php echo esc_attr((string) $settings['default_badge_url']); ?>">
                    <input type="hidden" name="prompt_title" value="<?php echo esc_attr((string) $settings['prompt_title']); ?>">
                    <input type="hidden" name="prompt_body" value="<?php echo esc_attr((string) $settings['prompt_body']); ?>">
                    <?php wp_nonce_field(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_NONCE); ?>
                    <button type="submit" name="quick_toggle_daily_random" value="1" class="button button-secondary">
                        <?php echo esc_html(!empty($settings['daily_random_enabled']) ? __('إيقاف الآن', 'mazaq') : __('تشغيل الآن', 'mazaq')); ?>
                    </button>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:10px;">
                    <input type="hidden" name="action" value="<?php echo esc_attr(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_ACTION); ?>">
                    <?php wp_nonce_field(MAZAQ_BROWSER_NOTIFICATIONS_DAILY_PREVIEW_NONCE); ?>
                    <button type="submit" class="button button-secondary"><?php esc_html_e('إرسال الاقتراح اليومي الآن كتجربة', 'mazaq'); ?></button>
                </form>
            </div>
        </div>

        <div style="background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;margin-bottom:24px;">
            <p style="margin:0 0 10px;"><strong><?php esc_html_e('رابط الموقع:', 'mazaq'); ?></strong> <code><?php echo esc_html((string) $runtime['home_url']); ?></code></p>
            <p style="margin:0 0 10px;"><strong><?php esc_html_e('رابط Service Worker:', 'mazaq'); ?></strong> <code><?php echo esc_html((string) $runtime['service_worker_url']); ?></code></p>
            <p style="margin:0 0 10px;"><strong><?php esc_html_e('HTTPS:', 'mazaq'); ?></strong> <?php echo esc_html($runtime['is_https'] ? __('مفعل', 'mazaq') : __('غير مفعل', 'mazaq')); ?></p>
            <p style="margin:0;"><strong><?php esc_html_e('مفاتيح VAPID:', 'mazaq'); ?></strong> <?php echo esc_html($runtime['vapid_configured'] ? __('موجودة', 'mazaq') : __('غير مضبوطة', 'mazaq')); ?></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:14px;">
                <input type="hidden" name="action" value="<?php echo esc_attr(MAZAQ_BROWSER_NOTIFICATIONS_TEST_ACTION); ?>">
                <?php wp_nonce_field(MAZAQ_BROWSER_NOTIFICATIONS_TEST_NONCE); ?>
                <button type="submit" class="button button-primary"><?php esc_html_e('إرسال تنبيه تجريبي الآن', 'mazaq'); ?></button>
            </form>
        </div>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_ACTION); ?>">
            <?php wp_nonce_field(MAZAQ_BROWSER_NOTIFICATIONS_SETTINGS_NONCE); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('تفعيل النظام', 'mazaq'); ?></th>
                        <td><label><input type="checkbox" name="enabled" value="1" <?php checked(!empty($settings['enabled'])); ?>> <?php esc_html_e('تفعيل التنبيهات للزوار', 'mazaq'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('التنبيه اليومي', 'mazaq'); ?></th>
                        <td>
                            <label><input type="checkbox" name="daily_random_enabled" value="1" <?php checked(!empty($settings['daily_random_enabled'])); ?>> <?php esc_html_e('تفعيل إرسال الاقتراح العشوائي اليومي', 'mazaq'); ?></label>
                            <p class="description"><?php esc_html_e('سيتم إرسال اقتراح عشوائي واحد يوميًا حسب الوقت المحدد أدناه.', 'mazaq'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-daily-random-time"><?php esc_html_e('وقت الإرسال اليومي', 'mazaq'); ?></label></th>
                        <td>
                            <input id="mazaq-daily-random-time" class="regular-text" type="time" name="daily_random_time" value="<?php echo esc_attr((string) $settings['daily_random_time']); ?>" step="60">
                            <p class="description"><?php esc_html_e('صيغة 24 ساعة حسب المنطقة الزمنية المضبوطة في ووردبريس.', 'mazaq'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('تنبيهات المقالات الجديدة', 'mazaq'); ?></th>
                        <td><label><input type="checkbox" name="new_post_enabled" value="1" <?php checked(!empty($settings['new_post_enabled'])); ?>> <?php esc_html_e('إرسال تنبيه عند نشر مقال جديد', 'mazaq'); ?></label></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-vapid-public"><?php esc_html_e('VAPID Public Key', 'mazaq'); ?></label></th>
                        <td>
                            <textarea id="mazaq-vapid-public" class="large-text code" rows="3" name="vapid_public_key"><?php echo esc_textarea((string) $settings['vapid_public_key']); ?></textarea>
                            <p class="description"><?php esc_html_e('المفتاح العام الذي يستخدمه المتصفح أثناء الاشتراك.', 'mazaq'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-vapid-private"><?php esc_html_e('VAPID Private Key', 'mazaq'); ?></label></th>
                        <td>
                            <textarea id="mazaq-vapid-private" class="large-text code" rows="3" name="vapid_private_key"><?php echo esc_textarea((string) $settings['vapid_private_key']); ?></textarea>
                            <p class="description"><?php esc_html_e('المفتاح الخاص المستخدم للإرسال من السيرفر. لا تشاركه علنًا.', 'mazaq'); ?></p>
                            <?php if (!empty($runtime['can_generate_keys'])) : ?>
                                <p style="margin-top:10px;">
                                    <button type="submit" name="generate_vapid_keys" value="1" class="button button-secondary"><?php esc_html_e('توليد مفاتيح جديدة تلقائيًا', 'mazaq'); ?></button>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-icon-url"><?php esc_html_e('رابط أيقونة التنبيه', 'mazaq'); ?></label></th>
                        <td><input id="mazaq-icon-url" class="regular-text code" type="url" name="default_icon_url" value="<?php echo esc_attr((string) $settings['default_icon_url']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-badge-url"><?php esc_html_e('رابط Badge التنبيه', 'mazaq'); ?></label></th>
                        <td><input id="mazaq-badge-url" class="regular-text code" type="url" name="default_badge_url" value="<?php echo esc_attr((string) $settings['default_badge_url']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-prompt-title"><?php esc_html_e('عنوان طلب الاشتراك', 'mazaq'); ?></label></th>
                        <td><input id="mazaq-prompt-title" class="regular-text" type="text" name="prompt_title" value="<?php echo esc_attr((string) $settings['prompt_title']); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="mazaq-prompt-body"><?php esc_html_e('نص طلب الاشتراك', 'mazaq'); ?></label></th>
                        <td><textarea id="mazaq-prompt-body" class="large-text" rows="4" name="prompt_body"><?php echo esc_textarea((string) $settings['prompt_body']); ?></textarea></td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(__('حفظ الإعدادات', 'mazaq')); ?>
        </form>

        <div style="margin-top:24px;background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:16px;">
            <h2 style="margin-top:0;"><?php esc_html_e('ملخص الاشتراكات', 'mazaq'); ?></h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('الحالة', 'mazaq'); ?></th>
                        <th><?php esc_html_e('العدد', 'mazaq'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php esc_html_e('نشط', 'mazaq'); ?></td>
                        <td><?php echo esc_html((string) $counts['active']); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('ألغى الاشتراك', 'mazaq'); ?></td>
                        <td><?php echo esc_html((string) $counts['unsubscribed']); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('غير صالح', 'mazaq'); ?></td>
                        <td><?php echo esc_html((string) $counts['invalid']); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php esc_html_e('الإجمالي', 'mazaq'); ?></strong></td>
                        <td><strong><?php echo esc_html((string) $counts['total']); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
