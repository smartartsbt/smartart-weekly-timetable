# Smartart Weekly Timetable

[English](README.md) | [Magyar](README.hu.md)

A responsive, color-coded weekly timetable for WordPress, powered by a published Google Sheet.

Manage classes, trainers, shifts or appointments in Google Sheets and display the schedule on your WordPress website with one shortcode. There is no need to export or upload a new file after editing the sheet.

## Features

- **Google Sheets as the content manager:** edit the timetable in a familiar spreadsheet.
- **Automatic updates:** the website retrieves the published CSV data when the cache expires.
- **Responsive layout:** a weekly table on desktop and day tabs with a list on mobile.
- **Color-coded people:** each trainer, teacher or staff member has an individual color.
- **Automatic time-block merging:** consecutive half-hour slots assigned to the same person are displayed as one block.
- **Flexible timetable:** Monday to Saturday, configurable time range, using half-hour slots.
- **Server-side caching:** reduces repeated requests to Google Sheets and helps keep pages fast.
- **Fallback to the last successful copy:** the last successfully retrieved timetable can remain available if Google Sheets is temporarily unreachable.
- **Accessible output:** semantic HTML table and keyboard-operable mobile tabs; the timetable also works without JavaScript.
- **Theme-friendly styling:** customize colors, fonts and corner radius with CSS variables.
- **Lightweight:** no custom database tables, tracking or cookies. Temporary timetable cache is stored using WordPress transients.
- **Translation-ready:** Hungarian is included.

## Requirements

- WordPress 6.3 or later
- PHP 8.0 or later
- A Google Sheet published in CSV format

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`, or install the plugin through **Plugins → Add New Plugin** in WordPress.
2. Activate **Smartart Weekly Timetable**.
3. Create your timetable in Google Sheets using the layout described below.
4. In Google Sheets, select **File → Share → Publish to web**.
5. Select the timetable sheet and choose **Comma-separated values (.csv)** as the format. Publish it and copy the generated URL.
6. Add a **Shortcode** block or shortcode widget to the page where the timetable should appear.
7. Paste the shortcode below, replacing the example URL with your published CSV URL.

```text
[smartart_timetable url="https://docs.google.com/spreadsheets/d/e/YOUR-ID/pub?gid=0&single=true&output=csv"]
```

The shortcode can be used in the WordPress block editor, Elementor or another page builder that supports shortcodes.

## Google Sheets layout

Create a Google Sheet with the following layout in columns A to G:

| Cell range | Content |
|---|---|
| `A2` | Week label, for example `November 2 – 7, 2026` |
| `B4:G4` | Names of up to six trainers, teachers or staff members |
| `B6:G6` | Day names, Monday to Saturday. Clear a day name to hide that day. |
| `B7:G7` | Dates displayed under the day names |
| `A8:A…` | Time slots, one per row, for example `8:00 – 8:30` |
| `B8:G…` | The name of the person assigned to each time slot |

The position of each person in row 4 determines their timetable color: column B uses color 1, column C uses color 2, and so on.

**Tip:** Use a dropdown list in the timetable cells, with its options pointing to `B4:G4`. This helps prevent spelling differences in names.

You can use Google Sheets formulas for the week label and dates so they update automatically.

## Shortcode options

The required `url` attribute must contain a published Google Sheets CSV URL:

```text
[smartart_timetable url="YOUR_PUBLISHED_CSV_URL"]
```

The optional `cache` attribute sets how many minutes the retrieved timetable is cached.

```text
[smartart_timetable url="YOUR_PUBLISHED_CSV_URL" cache="10"]
```

- Minimum: `1` minute
- Maximum: `1440` minutes
- Default: `5` minutes

Google may take a few minutes to refresh a published sheet. With the default cache setting, allow approximately 5–10 minutes for an edit to appear on the website.

## Styling

The timetable uses CSS variables, so you can adapt its appearance to your theme. Add your CSS to your theme's **Additional CSS** area or child theme stylesheet.

Example:

```css
.sawt {
    --sawt-primary: #1d6fa5;
    --sawt-c0: #cfe8ff;
    --sawt-c1: #ffe0b8;
    --sawt-font-heading: "Montserrat", sans-serif;
}
```

Available CSS variables:

- `--sawt-primary`
- `--sawt-primary-light`
- `--sawt-heading`
- `--sawt-body`
- `--sawt-border`
- `--sawt-line`
- `--sawt-soft`
- `--sawt-bg`
- `--sawt-radius`
- `--sawt-row`
- `--sawt-font-heading`
- `--sawt-c0` to `--sawt-c5` for person colors

## Frequently asked questions

### The timetable says “The timetable is currently unavailable”. What should I check?

Log in as an editor and open the page. Editors can see the specific error message.

Common causes include:

- The sheet was published as a web page instead of a CSV file.
- The source file is an Excel `.xlsx` file rather than a Google Sheet. Open it in Google Sheets and choose **File → Save as Google Sheets**.
- The published URL is incorrect or does not return CSV data.

### How quickly do changes appear on the website?

Google refreshes published sheets within a few minutes. The plugin also caches the downloaded data for 5 minutes by default. In normal circumstances, allow approximately 5–10 minutes for changes to appear. You can adjust the cache duration with the `cache` shortcode attribute.

### Can I use the plugin with Elementor or another page builder?

Yes. Add the shortcode to any shortcode block or widget supported by your editor or page builder.

### Can I change the colors and fonts?

Yes. Add CSS to your theme and override the CSS variables listed in the **Styling** section.

### Which URLs are accepted?

For security reasons, the plugin accepts only `https://docs.google.com/spreadsheets/...` URLs that return CSV data.

### Does the plugin create database tables or use cookies?

The plugin does not create custom database tables and does not use tracking or cookies. It stores temporary cached timetable data using WordPress transients. WordPress manages this cache.

### How can I translate the plugin?

The plugin is translation-ready and includes Hungarian. Its text domain is `smartart-weekly-timetable`.

You can contribute translations through the [WordPress translation project](https://translate.wordpress.org/projects/wp-plugins/smartart-weekly-timetable/). For offline translation, use `languages/smartart-weekly-timetable.pot` as the template.

## External service: Google Sheets

This plugin connects to Google Sheets to retrieve the CSV version of a spreadsheet that the site administrator has published.

- **Purpose:** download the published CSV timetable so it can be displayed on the website.
- **When a request is made:** the website's server requests the CSV URL configured in the shortcode when the cached copy has expired.
- **Data transmitted:** the request is made by the website's server. Visitor data is not sent to Google by this plugin; Google receives standard server request information, such as the server IP address.
- **Provider:** Google LLC.
- **Terms of Service:** https://policies.google.com/terms
- **Privacy Policy:** https://policies.google.com/privacy

## Support and project links

- Website: https://smartart.hu
- WordPress plugin page: https://wordpress.org/plugins/smartart-weekly-timetable/
- Translation project: https://translate.wordpress.org/projects/wp-plugins/smartart-weekly-timetable/

## License

Smartart Weekly Timetable is licensed under the GNU General Public License, version 2 or later (GPL-2.0-or-later).
