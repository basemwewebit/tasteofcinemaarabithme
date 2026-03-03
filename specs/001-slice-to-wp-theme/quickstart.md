# Quickstart: Taste of Cinema Arabic Theme

**Branch**: `001-slice-to-wp-theme` | **Date**: 2026-03-03

## Prerequisites

- WordPress 6.4+ installed and running
- PHP 8.1+
- Node.js 18+ and npm (for Tailwind CSS build)
- Secure Custom Fields (SCF) plugin activated
- MySQL 5.7+ / MariaDB 10.3+

## Setup

### 1. Install Node Dependencies

```bash
cd wp-content/themes/tasteofcinemaarabithme
npm install
```

### 2. Build Tailwind CSS

```bash
# Development (with watch mode)
npm run dev

# Production (minified)
npm run build
```

### 3. Activate Theme

1. Go to **WordPress Admin → Appearance → Themes**
2. Find "Taste of Cinema Arabic" and click **Activate**

### 4. Configure Theme Options

1. Go to **WordPress Admin → Theme Options** (SCF options page)
2. Fill in:
   - **Google Analytics**: GA4 Measurement ID (e.g., `G-XXXXXXXXXX`)
   - **Google AdSense**: Publisher ID (e.g., `ca-pub-XXXXXXXXXXXXXXXX`)
   - **Ad Slot IDs**: Configure each of the 7 ad placements
   - **Hero Article**: Select the featured post for homepage hero
   - **Contact Info**: Email, address, social links

### 5. Set Up Menus

1. Go to **WordPress Admin → Appearance → Menus**
2. Create and assign:
   - **Primary Navigation** → Header menu (الرئيسية, المراجعات, القوائم, أخبار السينما)
   - **Footer Sections** → Footer "الأقسام" column
   - **Footer Links** → Footer "روابط هامة" column

### 6. Create Required Pages

Create WordPress pages with these specific page templates:
- **اتصل بنا** (Contact) → Uses `page-contact.php` template
- **سياسة الخصوصية** (Privacy Policy) → Uses `page-privacy.php` template

### 7. Configure Author Profiles

For each author, edit their profile and fill SCF fields:
- **Role/Title** (e.g., "ناقد سينمائي")
- **Twitter URL**
- **Website URL**

## Development Workflow

### CSS Changes

```bash
# Watch mode — rebuilds on every PHP/HTML class change
npm run dev
```

### File Structure Quick Reference

| Need to change... | Edit this file |
|-------------------|---------------|
| Header/Logo/Nav | `header.php` |
| Footer | `footer.php` |
| Homepage layout | `front-page.php` |
| Single article | `single.php` |
| Category page | `category.php` |
| Search results | `search.php` |
| Author page | `author.php` |
| 404 error page | `404.php` |
| Article card design | `template-parts/content/card.php` |
| Ad placements | `template-parts/ads/*.php` |
| SCF field definitions | `inc/scf-fields.php` |
| Theme setup & hooks | `functions.php` + `inc/*.php` |
| Tailwind config | `tailwind.config.js` |
| Custom CSS overrides | `assets/css/src/style.css` |
| JavaScript | `assets/js/app.js` |

## Verification Checklist

After setup, verify these work:

- [ ] Homepage loads with hero section and article grid
- [ ] Dark/light mode toggle works and persists
- [ ] Mobile menu opens on tap
- [ ] Search overlay opens on search icon click
- [ ] Infinite scroll loads more articles
- [ ] Single post shows reading progress bar
- [ ] Font size A+/A- controls work on single post
- [ ] Category pages show pagination
- [ ] Author pages display profile + articles
- [ ] Contact form submits and delivers email
- [ ] 404 page displays cinematic design
- [ ] Ads render in all 7 placements (when configured)
- [ ] Google Analytics tracking visible in page source (when configured)
