<?php

/**
 * Notification web surface: the frozen live-browser contract. Owns the REST
 * routes, the /mazaq-sw.js service worker route, and the footer markup. Thin
 * callers of the settings, store, and content seams.
 */

declare(strict_types=1);

/**
 * Register the root service worker rewrite.
 */
function mazaq_browser_notifications_add_rewrite_rule(): void
{
    add_rewrite_rule('^mazaq-sw\.js/?$', 'index.php?mazaq_sw=1', 'top');
}
add_action('init', 'mazaq_browser_notifications_add_rewrite_rule');

/**
 * Detect requests targeting the root service worker endpoint.
 */
function mazaq_browser_notifications_is_service_worker_request(): bool
{
    if ((int) get_query_var('mazaq_sw') === 1) {
        return true;
    }

    $request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
    if ($request_uri === '') {
        return false;
    }

    $request_path = (string) wp_parse_url($request_uri, PHP_URL_PATH);

    return (bool) preg_match('#/mazaq-sw\.js/?$#', $request_path);
}

/**
 * Prevent canonical redirects from breaking service worker registration.
 *
 * @param string|false $redirect_url Canonical target.
 * @return string|false
 */
function mazaq_browser_notifications_disable_canonical_redirects($redirect_url)
{
    if (mazaq_browser_notifications_is_service_worker_request()) {
        return false;
    }

    return $redirect_url;
}
add_filter('redirect_canonical', 'mazaq_browser_notifications_disable_canonical_redirects');

/**
 * Register service worker query vars.
 *
 * @param string[] $vars Existing vars.
 * @return string[]
 */
function mazaq_browser_notifications_register_query_vars(array $vars): array
{
    $vars[] = 'mazaq_sw';

    return $vars;
}
add_filter('query_vars', 'mazaq_browser_notifications_register_query_vars');

/**
 * Handle service worker requests at the site root.
 */
function mazaq_browser_notifications_render_service_worker(): void
{
    if (!mazaq_browser_notifications_is_service_worker_request()) {
        return;
    }

    $settings = mazaq_browser_notifications_get_settings();

    nocache_headers();
    header('Content-Type: application/javascript; charset=UTF-8');
    header('Service-Worker-Allowed: /');

    $fallback_icon = $settings['default_icon_url'] ?: (get_template_directory_uri() . '/assets/images/logo.webp');
    $fallback_badge = $settings['default_badge_url'] ?: $fallback_icon;
    $fallback_title = __('مذاق السينما', 'mazaq');

    echo "self.addEventListener('install',function(event){event.waitUntil(self.skipWaiting());});\n";
    echo "self.addEventListener('activate',function(event){event.waitUntil(self.clients.claim());});\n";
    echo "self.addEventListener('push',function(event){\n";
    echo "  var payload={};\n";
    echo "  try{payload=event.data?event.data.json():{};}catch(error){payload={};}\n";
    echo '  var title=payload.title||' . wp_json_encode($fallback_title) . ";\n";
    echo "  var options={\n";
    echo "    body: payload.body || '',\n";
    echo "    icon: payload.icon || " . wp_json_encode($fallback_icon) . ",\n";
    echo "    badge: payload.badge || " . wp_json_encode($fallback_badge) . ",\n";
    echo "    image: payload.image || undefined,\n";
    echo "    data: {url: payload.url || '/', id: payload.id || ''}\n";
    echo "  };\n";
    echo "  event.waitUntil(self.registration.showNotification(title, options));\n";
    echo "});\n";
    echo "self.addEventListener('notificationclick',function(event){\n";
    echo "  event.notification.close();\n";
    echo "  var target=(event.notification && event.notification.data && event.notification.data.url) ? event.notification.data.url : '/';\n";
    echo "  event.waitUntil(clients.matchAll({type:'window',includeUncontrolled:true}).then(function(clientList){\n";
    echo "    for(var i=0;i<clientList.length;i++){if('focus' in clientList[i]){clientList[i].navigate(target);return clientList[i].focus();}}\n";
    echo "    if(clients.openWindow){return clients.openWindow(target);} return undefined;\n";
    echo "  }));\n";
    echo "});\n";

    exit;
}
add_action('template_redirect', 'mazaq_browser_notifications_render_service_worker');

/**
 * Register REST endpoints for subscription management and bootstrap data.
 */
function mazaq_browser_notifications_register_rest_routes(): void
{
    register_rest_route('mazaq/v1', '/notifications/bootstrap', [
        'methods' => WP_REST_Server::READABLE,
        'permission_callback' => '__return_true',
        'callback' => function (): WP_REST_Response {
            $settings = mazaq_browser_notifications_get_settings();

            return new WP_REST_Response([
                'enabled' => !empty($settings['enabled']),
                'publicVapidKey' => mazaq_browser_notifications_is_push_ready() ? (string) $settings['vapid_public_key'] : '',
                'promptEligible' => mazaq_browser_notifications_is_push_ready(),
                'fallbackNotifications' => mazaq_browser_notifications_get_fallback_notifications(),
            ]);
        },
    ]);

    register_rest_route('mazaq/v1', '/notifications/subscription', [
        'methods' => WP_REST_Server::CREATABLE,
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request): WP_REST_Response {
            if (!mazaq_browser_notifications_is_enabled()) {
                return new WP_REST_Response([
                    'message' => __('ميزة التنبيهات غير مفعلة حالياً.', 'mazaq'),
                ], 400);
            }

            $saved = mazaq_browser_notifications_save_subscription((array) $request->get_json_params());

            return new WP_REST_Response([
                'success' => $saved,
            ], $saved ? 200 : 400);
        },
    ]);

    register_rest_route('mazaq/v1', '/notifications/subscription', [
        'methods' => WP_REST_Server::DELETABLE,
        'permission_callback' => '__return_true',
        'callback' => function (WP_REST_Request $request): WP_REST_Response {
            $params = (array) $request->get_json_params();
            $endpoint = isset($params['endpoint']) ? (string) $params['endpoint'] : '';
            $removed = mazaq_browser_notifications_unsubscribe($endpoint);

            return new WP_REST_Response([
                'success' => $removed,
            ], $removed ? 200 : 400);
        },
    ]);
}
add_action('rest_api_init', 'mazaq_browser_notifications_register_rest_routes');

/**
 * Render the shared prompt/toast markup.
 */
function mazaq_browser_notifications_render_markup(): void
{
    if (!mazaq_browser_notifications_is_enabled()) {
        return;
    }

    get_template_part('template-parts/common/browser-notifications', null, [
        'settings' => mazaq_browser_notifications_get_settings(),
    ]);
}
add_action('wp_footer', 'mazaq_browser_notifications_render_markup', 5);
