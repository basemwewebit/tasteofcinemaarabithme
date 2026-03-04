# Research & Technical Decisions: Google reCAPTCHA v3 Integration

## Overview
This document outlines the technical decisions and architectural approach for integrating Google reCAPTCHA v3 into the existing WordPress theme to secure forms automatically.

## Decision 1: Backend Verification Mechanism
**Decision**: Use WordPress HTTP API (`wp_remote_post`) to communicate with Google's siteverify endpoint.
**Rationale**: `wp_remote_post` is native to WordPress, extremely stable, and handles SSL verification and timeouts gracefully out of the box. It avoids adding external dependencies like Guzzle or raw cURL implementations.
**Alternatives considered**: Direct cURL. Rejected because WP provides a wrapper that applies environment-specific configurations properly.

## Decision 2: reCAPTCHA Configuration Storage
**Decision**: Utilize the standard WordPress Options API via a custom administrative settings page.
**Rationale**: Keeps credentials out of the codebase (preventing security leaks in git) and gives admins immediate control to adjust the Score Threshold without deploying code.
**Alternatives considered**: Hardcoding in `functions.php` or `wp-config.php`. Rejected because it violates the FR-004 requirement for an admin interface.

## Decision 3: Frontend Script Injection & Interception
**Decision**: Enqueue the Google reCAPTCHA JS library only on specific pages (login, registration, password reset, and the contact page) using `wp_enqueue_script`. Bind the reCAPTCHA token generation to the form submission events via JavaScript.
**Rationale**: Selectively enqueuing the script minimizes performance impact on standard content pages, adhering to the SC-003 constraint and general performance best practices for WP.
**Alternatives considered**: Loading the script globally on all pages. Rejected due to unnecessary performance overhead.

## Decision 4: API Timeout Default Behavior
**Decision**: Configure the `wp_remote_post` timeout to 3 seconds. On timeout (`is_wp_error`), fail open and process the submission as normal.
**Rationale**: Prioritizes legitimate user experience during external outages, as required by the clarification session.
