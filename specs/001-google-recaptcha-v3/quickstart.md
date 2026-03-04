# Quickstart: Google reCAPTCHA v3 Implementation

## Overview
This implementation integrates completely invisibly without the need for puzzle-solving. We will deploy the scripts for user authentication forms and custom contact forms to reduce spam by comparing scores against a threshold.

## Configuration Guide

1. Generate your Site and Secret Keys:
   - Go to Google's [reCAPTCHA admin console](https://www.google.com/recaptcha/admin/create)
   - Choose "reCAPTCHA v3"
   - Add your domain (`tasteofcinemaarabi.com` locally or your staging environment)
   - Do not require user verification (acting simply as scoring).
2. Log in to the WordPress admin dashboard on your site.
3. Navigate to **Settings > reCAPTCHA Settings**.
4. Enter your generated `Site Key` and `Secret Key`.
5. Ensure the score threshold is `0.5` initially.
6. Check your forms functionally verify correctly:
   - Note that if you submit forms too rapidly as an admin testing locally, Google may temporarily penalize your score. Use Chrome Incognito windows via different IPs if false positives arise during testing.

## Local Testing Without API Keys
If you do not have API keys handy, leave the fields blank in the WordPress settings. The plugin checks if the Site Key and Secret Key are present and will skip validation (allowing submissions normally) if they are missing. This behavior makes local development easier.
