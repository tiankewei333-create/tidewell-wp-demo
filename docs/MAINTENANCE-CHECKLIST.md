# Maintenance checklist

The routine I follow for a small business WordPress site like Tidewell. Each completed pass is
recorded in [`MAINTENANCE-LOG.md`](MAINTENANCE-LOG.md).

## Before any change

- [ ] Full backup (files + database) and confirm it can be downloaded
- [ ] Work on a staging copy first (or a WordPress Playground copy for this demo)
- [ ] Note current versions of WordPress, PHP, theme and plugins in the log

## Monthly

**Updates** (staging first, then live)

- [ ] WordPress core
- [ ] Plugins one at a time: Elementor, WooCommerce, Contact Form 7, Flamingo, Yoast SEO
- [ ] Parent theme (Hello Elementor). The child theme `tidewell` holds all customisations, so the
      parent can update without losing them
- [ ] After Elementor updates: **Elementor › Tools › Regenerate CSS & Data**, then clear caches

**Smoke test after updates** (desktop and a 390 px phone)

- [ ] Home, a service page, Pricing, Portfolio (filter + lightbox), a blog post
- [ ] Mobile menu opens, Services submenu expands
- [ ] Booking form: submit a test request, check it arrives by email and in
      **Flamingo › Inbound Messages**
- [ ] WooCommerce: add a package to the cart, reach checkout, place a test order, check the
      order confirmation page loads, then cancel the order
- [ ] Browser console has no new JavaScript errors

**Health**

- [ ] **Tools › Site Health**: no critical issues
- [ ] Uptime and SSL certificate expiry date
- [ ] Remove unused plugins and themes (keep Hello Elementor: it is the parent theme)
- [ ] Delete spam form entries and old WooCommerce test orders

## Quarterly

- [ ] Lighthouse / PageSpeed on Home, a service page and Portfolio (mobile). Compare with
      [`PERFORMANCE.md`](PERFORMANCE.md); investigate drops of more than 10 points
- [ ] Google Search Console: coverage errors, Core Web Vitals, manual actions
- [ ] Check structured data with the Rich Results Test (LocalBusiness, FAQ, Article)
- [ ] Broken link check
- [ ] Review user accounts: remove old logins, confirm admins use strong passwords + 2FA
- [ ] Confirm business details (hours, phone, service areas) are still correct
- [ ] Restore a backup to staging to prove backups actually work

## Known gotchas on this site

| Situation | What to do |
| --- | --- |
| Pages lose their styling after a migration or update | Elementor › Tools › Regenerate CSS & Data. On hosts where Elementor cannot write CSS files, keep **Elementor › Settings › Performance › CSS print method = Internal embedding** |
| Order confirmation or My account sub-pages show the home page | Rewrite rules lost WooCommerce endpoints. Visit **Settings › Permalinks** and click **Save** (no changes needed) |
| A custom button or link turns the wrong colour | Button and link colours live in `assets/css/site.css`, not in Elementor's Site Settings. Keep Site Settings › Buttons empty so it cannot override them |
| Booking requests stop arriving by email | Check **Flamingo** first: if entries are there, the form works and email delivery is the problem (configure SMTP) |
| "Rebuild demo pages" in Tidewell › Demo tools | Overwrites manual Elementor edits. Only use it on a fresh demo |
