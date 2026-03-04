# Data Model: Contact Enhancements

## Entity: Contact Message
- **Type**: WordPress Custom Post Type (`contact_message`)
- **Properties**:
  - `ID`: Auto-generated Post ID.
  - `post_title` (String, required): Original subject of the contact form. Mapped from `subject`.
  - `post_content` (String, required): Original message body. Mapped from `message`.
  - `post_status` (String): 'private' or 'publish' (only visible in dashboard).
  - `post_type` (Enum): 'contact_message'.
- **Meta Fields**:
  - `_contact_name` (String, required): Submitter name. Mapped from `name`.
  - `_contact_email` (Email, required): Submitter email. Mapped from `email`.

## Entity: Social Link
- **Type**: ACF Repeater Field Row (part of `Theme Options` option page data)
- **Properties**:
  - `platform_name` (String, required): Label, e.g., "Twitter".
  - `url` (String/URL, required): Target URL.
  - `icon_svg` (String, optional): Raw SVG code string for rendering an icon (if needed) or class names (based on how the frontend handles it). Let's use `icon` as a textarea for raw SVG code, enabling full style control without a fixed icon set.
