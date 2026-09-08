# Keep site-wide security headers at the deployment edge

HSTS, CSP, COOP, and frame-protection headers remain owned by the hosting/CDN or a dedicated WordPress security layer. The theme contains WordPress-generated markup, inline configuration, advertisements, analytics, and plugin output, while edge coverage also reaches static assets and non-theme routes; adding a strict policy in the theme alone would be incomplete and could break existing integrations before their nonce and dependency strategy is ready.
