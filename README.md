# KISS Coming Soon Post Status  

**Author**: KISS Plugins  
**Author URI**: https://KISSPlugins.com  
**Requires at least**: 5.0  
**Tested up to**: 6.5  
**Stable tag**: 1.0.0  
**License**: GPLv2 or later  
**License URI**: https://www.gnu.org/licenses/gpl-2.0.html  

A WordPress plugin to add a "Coming Soon" post status. This allows you to show posts in archive/category pages to build anticipation, but prevents users from clicking through to the full post.

## Description  

This plugin introduces a new post status called "Coming Soon". It's designed for when you want to tease upcoming content on your website without publishing the full post or page yet.

When a post or page is set to "Coming Soon":

It will still appear in your post grids on category, archive, and landing pages.

The featured image and excerpt will display as normal, giving visitors a preview of the content.

The "Read More" link (or equivalent) will be automatically replaced with the text "Coming Soon".

The link will point to "#" instead of the actual post URL, preventing public access to the single post page until you are ready to publish it.

This is the perfect solution for building anticipation for new articles, products, or announcements while maintaining your site's layout and design.

## Installation  

1. Download the plugin `.zip` file from [git repository](https://github.com/kissplugins/kiss-comings-soon-status).
2. Go to your WordPress admin → **Plugins > Add New**.
3. Click **Upload Plugin**, select the zip file, and install it.
4. Activate the plugin. That’s it! 
5. You can now use the "Coming Soon" status on your posts and pages.

## Developers  

See PHP plugin code on how to customize your labels.

## How to Use
 
Go to edit or create a new Post or Page.

In the Status & visibility panel (in the block editor) or the Publish meta box (in the classic editor), click on the current status (e.g., "Draft" or "Published").

You will now see "Coming Soon" as an option in the dropdown list.

Select "Coming Soon" and click Update or Save Draft.

The post will now appear on your site's archive pages with the "Coming Soon" link. When you are ready to publish it fully, simply change the status to "Published".

## Settings  

A placeholder settings page has been created and can be accessed via:

Settings > Coming Soon Status in the WordPress admin menu.

The "Settings" link next to the plugin on the main Plugins listing page.

## Changelog  

**1.1.1**

- Refactor: Replaced status dropdown modification with a dedicated "Set as Coming Soon" checkbox. 
- Feature: Added checkbox to the "Publish" meta box in the full post editor. 
- Feature: Added checkbox to the "Quick Edit" interface on post listing screens. 
- Fix: The "Coming Soon" status is now reliably saved and reflected in Quick Edit mode. 
- Dev: Removed old JavaScript for dropdown manipulation and added new script for Quick Edit checkbox logic.

**1.0.1 - 2025-07-17**

- Fix: Add "Coming Soon" status to the Quick Edit dropdown on post page listing screens.  
- Dev: Added documentation for developers on how to integrate with custom "Read More" links.  

**1.0.0 - 2025-07-17**

- Feature: Adds a "Coming Soon" custom post status.
- Feature: Posts with the "Coming Soon" status appear in frontend archive queries (category, tag, etc.).
- Feature: The permalink and "Read More" links for these posts are modified to point to "#" and display "Coming Soon".
- Feature: A placeholder settings page is available under Settings > Coming Soon Status.
- Feature: A convenient "Settings" link is added to the plugin's entry on the main plugins page.

**Follow Us on Blue Sky:**
https://bsky.app/profile/kissplugins.bsky.social

## 📜 License

Released under **GPL v2 or later**  
[https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

Use at your own risk. Provided as-is with no warranties.

---

## 📬 Contact & Support

- Email: [devops@kissplugins.com](mailto:devops@kissplugins.com)
- Email: [noel@kissplugins.com](mailto:noel@kissplugins.com)
- Website: [https://kissplugins.com](https://kissplugins.com)
- Follow us on Blue Sky: [kissplugins.bsky.social](https://bsky.app/profile/kissplugins.bsky.social)

---

© Copyright Hypercart D.B.A. Neochrome, Inc.

