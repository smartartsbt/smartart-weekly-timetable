=== Smartart Weekly Timetable ===
Contributors: smartartsbt
Donate link: https://smartart.hu
Tags: timetable, schedule, google sheets, class schedule, shortcode
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show a responsive, color-coded weekly timetable from a published Google Sheet with one simple shortcode.

== Description ==

Smartart Weekly Timetable lets you manage a weekly schedule (classes, trainers, shifts, appointments) in a Google Sheet and show it on your website with a single shortcode. Edit the sheet, and the website follows. There is nothing to export or upload.

**Features**

* Native, theme-friendly output (a real HTML table and a mobile day list), not an iframe
* Fully responsive: a weekly table on desktop, day tabs with a list on phones
* Each person (trainer, teacher, staff member) gets their own color
* Half-hour rows are merged automatically into one block (a 1 hour class = two cells)
* Monday to Saturday, any time range, half-hour steps
* Server-side fetching with caching, so the page stays fast even if Google is slow
* If Google is temporarily unreachable, the last good copy is shown
* Accessible (semantic table, keyboard-operable tabs) and works without JavaScript
* No database tables, no settings page, no tracking, no cookies
* Colors, fonts and radius can be changed with CSS variables
* Translation ready (Hungarian included)

**Shortcode**

`[smartart_timetable url="https://docs.google.com/spreadsheets/d/e/YOUR-ID/pub?gid=0&single=true&output=csv"]`

Optional attribute: `cache` – how many minutes the sheet is cached (1 to 1440, default 5).

**Sheet layout**

Create a Google Sheet with this layout (columns A to G):

* **A2** – the label of the week, for example "November 2 – 7, 2026"
* **B4 to G4** – the names of up to six people. The position decides the color (B = color 1, C = color 2, and so on)
* **B6 to G6** – the day names (Monday to Saturday). Clear a day name to hide that day
* **B7 to G7** – the dates under the day names
* **A8 and below** – the time slots, one per row, for example "8:00 – 8:30"
* **B8 to G…** – in each time slot write the name of the person who teaches then (use a dropdown list pointing to B4:G4 to avoid typos)

You can use formulas for the dates and for the week label, so the sheet updates itself.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install it from the Plugins screen, then activate it.
2. Prepare your Google Sheet as described above.
3. In Google Sheets choose **File → Share → Publish to web**, select the timetable sheet, choose the **Comma-separated values (.csv)** format and click **Publish**. Copy the link.
4. Add a Shortcode block to any page and paste: `[smartart_timetable url="YOUR_LINK"]`

== Frequently Asked Questions ==

= The timetable shows "The timetable is currently unavailable". What should I check? =

Log in as an editor and open the page: editors see the exact reason. The most common causes are a sheet that was published as a web page instead of CSV, or an .xlsx file that was not converted to a real Google Sheet (use File → Save as Google Sheets first).

= How quickly do changes appear on the website? =

Google refreshes published sheets within a few minutes, and the plugin caches the data for 5 minutes by default (change it with the `cache` attribute). Expect changes within about 5 to 10 minutes.

= Can I use it with the block editor, Elementor or other page builders? =

Yes. Use any shortcode block or widget.

= How can I change the colors and fonts? =

Add CSS to your theme. Example:

`.sawt { --sawt-primary: #1d6fa5; --sawt-c0: #cfe8ff; --sawt-c1: #ffe0b8; --sawt-font-heading: "Montserrat", sans-serif; }`

Available variables: `--sawt-primary`, `--sawt-primary-light`, `--sawt-heading`, `--sawt-body`, `--sawt-border`, `--sawt-line`, `--sawt-soft`, `--sawt-bg`, `--sawt-radius`, `--sawt-row`, `--sawt-font-heading`, and the person colors `--sawt-c0` to `--sawt-c5`.

= Which URLs are accepted? =

Only `https://docs.google.com/spreadsheets/...` links that return CSV. This is a security restriction.

= How can I translate the plugin? =

The plugin is translation ready (text domain `smartart-weekly-timetable`). The easiest way is to contribute on https://translate.wordpress.org/projects/wp-plugins/smartart-weekly-timetable/ – approved translations are downloaded automatically by WordPress. A Hungarian translation is included. If you work offline, the file `languages/smartart-weekly-timetable.pot` is the template for any language.

= Does the plugin store anything in my database? =

Only temporary cached copies of the timetable (WordPress transients), which are removed when you delete the plugin.

== External services ==

This plugin connects to Google Sheets to load the timetable that **you** published.

* **What it is used for:** downloading the CSV version of your published Google Sheet so it can be displayed on your site.
* **What is sent and when:** your web server requests the CSV link you entered in the shortcode (only when the cached copy has expired). The request is made by your server, so no visitor data is sent to Google, only the standard server request information (such as the server IP address).
* **Service provider:** Google LLC. Terms of Service: https://policies.google.com/terms – Privacy Policy: https://policies.google.com/privacy

== Screenshots ==

1. The weekly timetable on a desktop screen.
2. The mobile view with day tabs.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
