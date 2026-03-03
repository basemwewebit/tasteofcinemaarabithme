# Quickstart: Fix Single Post Template Issues

This document outlines how to verify the new features implemented on the single post template.

## Prerequisites
- A local WordPress instance with the `tasteofcinemaarabithme` theme active.
- At least one published post containing more than 6 paragraphs of content.
- Ensure the post is assigned to at least one category.

## Step 1: Testing Breadcrumbs
1. Navigate to your website's frontend.
2. Click on any post to view its single page (`single.php`).
3. View the top of the content area. Check that the breadcrumb trail is visible in the format: "الرئيسية / [Category Name] / [Post Title]".
4. Click on the Category name in the breadcrumbs and ensure it takes you to the category archive page.
5. Click on the Home ("الرئيسية") link and ensure it returns you to the front page.

## Step 2: Testing Author Link & Reading Time
1. On the same single post page, locate the author meta data section (usually near the title or at the bottom).
2. Validate that the author's name is a hyperlink.
3. Click the hyperlink; it should redirect to the `/author/[username]/` page.
4. Go back to the post and locate the estimated reading time (e.g., "3 دقائق قراءة").
5. Edit the post in the WordPress admin to make it significantly shorter (e.g., 20 words).
6. Refresh the frontend page and verify the reading time has updated to "1 دقيقة" (minimum fallback).

## Step 3: Testing In-Content Advertisements
1. Open a post that has more than 6 text paragraphs.
2. Scroll through the content.
3. Verify that an advertisement block (or placeholder "مساحة إعلانية") appears strictly after the 3rd paragraph.
4. Keep scrolling and verify that another advertisement block appears after the 6th paragraph.
5. Inspect the HTML source of the page to ensure the injected ads haven't broken any tags (e.g. they aren't injected inside a `<strong>` tag or an unordered list `<ul>`).

## Step 4: Testing Related Articles & Caching
1. Scroll to the very bottom of the single post.
2. Locate the "مقالات ذات صلة" (Related Articles) section.
3. Verify that you see a list of other posts (excluding the current one).
4. Check that the listed posts share the same category as the current post.
5. (Advanced) If you have a query monitor installed, refresh the page and verify that the `WP_Query` for related posts is served from the Transient cache on subsequent loads.
