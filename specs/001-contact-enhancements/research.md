# Phase 0: Outline & Research

## Decision: Contact Form Custom Post Type Registration
- **Decision**: Create a dedicated `inc/post-types/contact-message.php` file to register a non-public Custom Post Type named `contact_message`. Include this file in `functions.php`.
- **Rationale**: Keeps `functions.php` clean. A non-public CPT ensures that these messages don't accidentally appear in front-end search results or have dedicated, unwanted single URLs. We only need them visible in the admin dashboard.
- **Alternatives considered**: Putting the registration directly in `functions.php` (rejected for maintainability), or modifying a third-party form plugin (rejected because a custom form already exists in `inc/contact-form.php`).

## Decision: Saving Contact Submissions
- **Decision**: Update `mazaq_handle_contact_form()` in `inc/contact-form.php` to use `wp_insert_post()` to create a new `contact_message` CPT upon successful form validation and BEFORE sending the email (or after, but ensuring data is saved regardless of email success). Map Subject to `post_title`, Message to `post_content`, and Name/Email to post meta (`sender_name`, `sender_email`).
- **Rationale**: Standard WordPress way to save unstructured and structured data.
- **Alternatives considered**: Custom database table (rejected as over-engineering for simple contact messages). 

## Decision: Social Media Repeater Field
- **Decision**: Update `mazaq_register_acf_fields()` in `inc/scf-fields.php`. Remove the static `social_twitter` and `social_website` fields. Add a new repeater field `social_links` to the `Theme Options`. Inside the repeater, add sub-fields: `platform_name` (Text, optional, for accessibility/labeling), `url` (URL, required), and `icon` (Select/Text/Icon Picker depending on current setup; we will use a text field for SVG/classes or an image field if allowed, let's stick to an Image/SVG field or text class field. Since it's not clear what icon library is used, we'll use a `text` field for an icon class (e.g., FontAwesome/Tailwind SVG) or an `image` field. Let's research current icons).
- **Rationale**: Provides the requested flexibility. ACF is already in use.
- **Alternatives considered**: Hardcoding arrays in PHP (rejected because the user explicitly requested it in the dashboard).

*Note: Checked `page-contact.php` and the current social links don't have icons, just text links. We'll add an `url` field and a `label` field (e.g., "Twitter"), and an `icon_svg` textarea field for the admin to paste SVG code, allowing maximum flexibility without requiring an external library.*
