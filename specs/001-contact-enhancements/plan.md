# Implementation Plan: Contact Page Enhancements

**Branch**: `001-contact-enhancements` | **Date**: 2026-03-04 | **Spec**: [spec.md](../spec.md)
**Input**: Feature specification from `/specs/001-contact-enhancements/spec.md`

## Summary

This feature enhances the contact page functionality by:
1. Intercepting contact form submissions and saving them as a Custom Post Type (CPT) in the WordPress dashboard, in addition to sending the email.
2. Replacing the hardcoded social media links in the theme options with a flexible repeater field allowing administrators to add, remove, and configure an arbitrary number of social links and icons.

## Technical Context

**Language/Version**: PHP 8+ (WordPress minimum requirements)
**Primary Dependencies**: WordPress Core API (register_post_type, wp_insert_post), Advanced Custom Fields (ACF) PRO for the repeater field.
**Storage**: WordPress MySQL Database (`wp_posts` and `wp_postmeta`)
**Testing**: Manual testing via WordPress admin interface and front-end contact page.
**Target Platform**: WordPress Theme (tasteofcinemaarabithme)
**Project Type**: WordPress Theme Customization
**Performance Goals**: N/A
**Constraints**: Must use existing ACF infrastructure for Theme Options (`inc/scf-fields.php`). Form handler is in `inc/contact-form.php`.
**Scale/Scope**: Impacts the Theme Options page, the front-end Contact page template (`page-contact.php`), and creates a new admin menu item for the CPT.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **WordPress Standards**: Ensures we are using `register_post_type` and standard WordPress data structures.
- **ACF Usage**: Follows the existing pattern of programmatic ACF field registration in `inc/scf-fields.php`.

## Project Structure

### Documentation (this feature)

```text
specs/001-contact-enhancements/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
└── quickstart.md        # Phase 1 output
```

### Source Code (repository root)

```text
wp-content/themes/tasteofcinemaarabithme/
├── inc/
│   ├── scf-fields.php       # Updated: Add repeater field for social links, remove old static fields
│   ├── contact-form.php     # Updated: Add logic to save CPT
│   └── post-types/          # NEW: Directory for CPT registrations
│       └── contact-message.php # NEW: Register the Contact Message CPT
├── functions.php            # Updated: Include the new CPT registration file
└── page-contact.php         # Updated: Fetch and loop through social repeater field
```

**Structure Decision**: The logic will be added to the existing theme structure. Form logic is already separated into `inc/contact-form.php`. ACF registrations are in `inc/scf-fields.php`. We will create a new directory `inc/post-types` to cleanly register the new CPT and include it in `functions.php`.
