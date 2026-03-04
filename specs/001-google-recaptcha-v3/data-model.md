# Data Model: Google reCAPTCHA v3 Integration

Since this integration relies on the WordPress Settings API, no new custom database tables are required. Instead, the implementation uses the core `wp_options` table to store configuration variables.

## Entities

### `recaptcha_v3_settings` Options

These settings will be grouped under a single options array or as individual options depending on implementation preference, but logically they include:

- **`toc_recaptcha_site_key`**: 
  - **Type**: String
  - **Description**: The public Site Key provided by Google reCAPTCHA for the domain.
  - **Validation**: Cannot be blank for the frontend mechanism to be active. Stored as plain text.

- **`toc_recaptcha_secret_key`**:
  - **Type**: String
  - **Description**: The private Secret Key provided by Google reCAPTCHA to verify tokens server-side.
  - **Validation**: Cannot be blank.

- **`toc_recaptcha_score_threshold`**:
  - **Type**: Float (representing 0.0 to 1.0)
  - **Description**: The minimum score required for a submission to be considered human. Defaults to `0.5`.
  - **Validation**: Ensure value falls within the valid range before saving.

## Data Lifecycle

- **Read**: configuration is read on every request to wp-login.php, wp-register.php, and any page containing a contact form.
- **Write**: Updated exclusively by administrators via the WordPress dashboard settings page.
- **State**: Once saved, the changes apply instantly on the next page load.
