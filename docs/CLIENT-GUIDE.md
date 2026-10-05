# Tidewell website: owner's guide

How to handle everyday updates without a developer. Each task takes a few minutes.
Log in at `/wp-admin` with your username and password.

> Rule of thumb: change content through the screens below. Leave plugin updates, theme files and
> the **Tidewell › Demo tools** buttons to whoever maintains the site.

---

## Change your phone, email, address or opening hours

**Tidewell › Business details** (left menu)

1. Edit the phone, email, street, city, state, ZIP, or opening and closing times.
2. Click **Save business details**.

These details are entered once and reused everywhere: the header call button, the footer,
the contact page, the booking page, and the information Google reads about your business
(structured data). There is no need to edit pages one by one.

---

## Edit text or photos on a page

**Pages › hover the page › Edit with Elementor**

1. Click any heading, paragraph, button or image on the page to edit it in the left panel.
2. To swap a photo, click it, then **Choose image** in the left panel.
3. Click **Publish** (bottom left) to save. Use the eye icon to preview first.

Tips:

- Keep images under 300 KB. Upload JPEGs; the site creates smaller WebP versions automatically.
- If something goes wrong, use the clock icon (**History › Revisions**) to restore an earlier version.

> Important: **Tidewell › Demo tools › Rebuild demo pages** rebuilds the pages from the original
> layout and **overwrites changes made in Elementor**. Do not use it once you have edited pages.

---

## Add a project to the portfolio

**Portfolio › Add new project**

| Field | What to enter |
| --- | --- |
| Title | e.g. "Craftsman kitchen deep clean in Sellwood" |
| Main text | The story: the problem, what the team did, the result |
| Excerpt | One or two sentences shown on the portfolio card |
| Main photo (after) | Right sidebar. The finished result. Also used as the card image |
| Project types | Right sidebar. Tick one or more (Kitchens, Bathrooms…). Powers the filter buttons |
| Project details › Location | e.g. "Hawthorne, Portland" |
| Project details › Service | Links the project to the matching service page |
| Project details › Duration / team | e.g. "5 hours, 2 cleaners" |
| Project details › "Before" photo | Optional. Adds a before/after slider |
| Project details › Gallery | Optional extra photos |

Click **Publish**. The project appears on the Portfolio page and under its filter.

Ask the customer's permission before publishing photos of their home, and avoid house numbers
or personal items in shot.

---

## Write a blog post

**Posts › Add New Post**

1. Add the title and text. Use headings (H2) for sections; they help readers and Google.
2. In the right sidebar, choose a **Category** and set a **Featured image**.
3. Fill in the **Excerpt**. It is shown under the title and on the blog cards.
4. Scroll down to the **Yoast SEO** box and write a meta description (see below).
5. Click **Publish**.

The newest three posts appear automatically on the home page.

---

## See booking and contact requests

**Flamingo › Inbound Messages**

Every booking and contact form submission is saved here, even if an email notification is missed.
Each entry shows the name, email, service, date and message.

Notification emails are set in **Contact › Contact Forms › (form) › Mail**. Change the
**To** address there if requests should go to a different inbox.

---

## Packages, prices and orders

- **Change a price:** **Products › edit the package**. For packages with home sizes
  (Studio, 2 bedrooms…), open **Product data › Variations** and edit each size.
- **See orders:** **WooCommerce › Orders**. Customers pay after the clean, so new orders arrive
  as *Processing*. Mark them *Completed* once paid.
- **Payment method text:** **WooCommerce › Settings › Payments › Pay after your clean**.

---

## Search results (Yoast SEO)

At the bottom of every page and post, the **Yoast SEO** box controls how it looks on Google:

- **SEO title:** about 50–60 characters, include the service and city
  (e.g. "Deep Cleaning in Portland, OR | Tidewell").
- **Meta description:** about 140–155 characters, say what you offer and why to click.

Green or orange lights are guidance, not rules. A clear, honest description matters more than
a perfect score.

---

## When to call your developer

- A plugin or WordPress shows "Update available" (updates are tested on a copy first; see
  `MAINTENANCE-CHECKLIST.md`).
- The site shows an error, or the booking form stops sending.
- You want a new page type, a new form field or a design change across the whole site.
