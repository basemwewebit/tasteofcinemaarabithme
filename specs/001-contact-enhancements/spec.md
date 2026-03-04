# Feature Specification: Contact Page Enhancements

**Feature Branch**: `001-contact-enhancements`  
**Created**: 2026-03-04  
**Status**: Draft  
**Input**: User description: "we need work here 1-when submit store submit on dashboard as cpt 2- we need make social as repeater field for links and icons"

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Store Contact Submissions in Dashboard (Priority: P1)

As a site administrator, I want contact form submissions to be saved directly to the WordPress dashboard as a custom post type, so that I can easily view, manage, and keep a historical record of all user inquiries without relying solely on email delivery.

**Why this priority**: Ensuring no messages are lost due to email delivery failures is critical for user communication and customer support.

**Independent Test**: Can be fully tested by submitting a contact form and verifying that a new entry appears in the dedicated section within the admin dashboard containing all the submitted details.

**Acceptance Scenarios**:

1. **Given** a visitor fills out the contact form with valid data, **When** they submit the form, **Then** a new record is created in the dashboard under a specific "Messages" or "Contact Submissions" section.
2. **Given** a new form submission is recorded, **When** an administrator views the submission in the dashboard, **Then** they can see the sender's name, email, subject, message content, and submission date.

---

### User Story 2 - Manage Social Media Links via Repeater Field (Priority: P2)

As a site administrator, I want to manage social media links using a flexible repeater field, so that I can easily add, remove, or modify social icons and their corresponding URLs without needing code changes.

**Why this priority**: Provides the admin with the flexibility to adapt to new social media platforms and change links dynamically without developer intervention.

**Independent Test**: Can be tested by navigating to the theme options/customizer, adding a new social link with an icon, and verifying it renders correctly on the front-end contact page.

**Acceptance Scenarios**:

1. **Given** an administrator is in the theme settings, **When** they add a new social media entry with an icon and URL, **Then** the new social link appears in all designated social link areas (e.g., contact page sidebar).
2. **Given** multiple social links are configured, **When** a visitor views the contact page, **Then** they see the list of social icons linking to the correct destinations.

---

### Edge Cases

- What happens if a user submits the form but the database fails to save the custom post type? 
- What happens if the administrator adds a social link but forgets to provide the icon or URL?
- How does the system handle spam submissions? 

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST intercept contact form submissions and save the data (Name, Email, Subject, Message) as a Custom Post Type.
- **FR-002**: System MUST display these custom post types in the admin dashboard with appropriate columns (Submitter Name, Email, Date).
- **FR-003**: System MUST provide a repeater field in the theme options or site settings to manage social media profiles.
- **FR-004**: System MUST allow each repeater field item to accept a URL and an icon.
- **FR-005**: System MUST dynamically render the social media links on the contact page using the data from the repeater field, replacing the currently hardcoded/static fields.
- **FR-006**: System MUST ensure the contact form still displays a success or error message appropriately after attempting to save the submission.

### Key Entities

- **Contact Message (Custom Post Type)**: Represents a single form submission.
  - Attributes: Post Title (Subject), Content (Message), Meta (Sender Name, Sender Email).
- **Social Link (Repeater Item)**: Represents a single social media platform link.
  - Attributes: Icon, URL.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of tested contact form submissions are successfully saved to the dashboard and visible to administrators.
- **SC-002**: Administrators can add a new social media link via the dashboard in under 1 minute without writing code.
- **SC-003**: No data truncation occurs for long messages saved in the dashboard.
- **SC-004**: Front-end rendering of social links dynamically reflects backend changes immediately upon saving.
