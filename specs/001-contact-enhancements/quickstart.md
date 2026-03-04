# Quickstart: Contact Enhancements

## Testing Contact Submissions (CPT)
1. Ensure the new `Contact Messages` code is deployed or active.
2. Go to the front-end contact page: `yourdomain.com/contact/`.
3. Submit a test message using valid data (Name, Email, Subject, Message).
4. Verify the success message appears.
5. Log into the WordPress Admin Dashboard.
6. Look for a new menu item called **Contact Messages** (or similar, depending on the CPT label).
7. Click to view the list. The new message should be listed.
8. Click into the message to verify the Subject, Name, Email, and Content were saved correctly.

## Testing Social Media Links (Repeater)
1. Log into the WordPress Admin Dashboard.
2. Navigate to **Theme Options** (managed by ACF).
3. Find the newly created `Social Links` repeater field section.
4. Add a new row.
5. Provide a Platform Name (e.g., "Facebook"), a valid URL, and optionally paste an SVG icon code into the `Icon` field.
6. Update/Save the Theme Options.
7. Go to the front-end contact page: `yourdomain.com/contact/`.
8. Verify that the new social link and icon appear in the "Follow Us" section.
9. Remove or modify the social link in the dashboard and refresh the front-end to ensure changes propagate.
