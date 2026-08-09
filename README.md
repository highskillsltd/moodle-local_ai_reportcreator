# AI Report Creator — Moodle Local Plugin

This plugin is part of the **LearningOps** suite by **Highskills and more**.
https://www.highskills.co.il/

## Overview

Generate data reports and charts from plain-language descriptions. Type what you want to see — the plugin sends your request to an AI middleware, which returns a SQL query and a ready-to-render HTML template. No SQL knowledge required.

## Activation

To get your activation endpoint and API key, please [complete the setup process here](https://www.highskills.co.il/blog/ai/reportcreator-moodle).

## Features

- **Natural language → SQL** — describe your report in plain text; the AI writes the query.
- **Multiple output types** — table report, stat-card dashboard, bar/line/pie/doughnut/radar charts.
- **Embeddable** — every report gets an iframe embed code; viewers must be logged in to this Moodle site.
- **Safe by design** — all generated SQL is validated as read-only before execution; write/DDL statements are always rejected.
- **Hebrew (RTL) support** — full Hebrew translation included; Moodle handles RTL layout automatically.

---

## Accessing the plugin

Authorised users find the plugin at:

**Site Administration → Reports → AI Reports  → AI Report Creator**

## Requirements

- Moodle | 4.3 or later |
- PHP | 7.4 or later |
- Access to the **AI Report Creator FastAPI service** (provided by Highskills and more) |

---

## Installation

1. Copy (or clone) the plugin folder into your Moodle installation (rename `ai_reportcreator`):

   ```
   /path/to/moodle/local/ai_reportcreator/
   ```

2. Log in as a site administrator and go to **Site administration → Notifications** to run the database upgrade.

3. The plugin creates one database table: `local_ai_reportcreator_reports`.

---

## Configuration

Go to **Site administration → Plugins → Local plugins → AI Report Creator**.

| Setting | Description |
|---|---|
| **Middleware endpoint URL** |  Full URL of the FastAPI streaming endpoint provided by Highskills and more |
| **API key (Bearer token)** | 64-character hex key provided by Highskills and more  |
| **Moodle version** | Version string sent to the middleware for context (auto-filled from `$CFG->release`) |

After saving, click **Test Connection** to verify the middleware is reachable and the credentials are correct.

---

## Writing a Good Prompt

Navigate to **AI Report Creator → Create new report** and fill in the description field.

**Tips:**

- Start with what you want to **see**: a count, list, total, average, etc.
- Specify a **time range** if relevant — e.g. *for the last 30 days*.
- Add **course custom fields** by writing: *course custom fields = isfrontal,isrequired*.
- Add **user custom fields** by writing: *user info fields = department,ouid,ouname,managerid*.

**Example prompt:**

> Show me the number of active enrollments per course for the last 30 days, course custom fields = isfrontal,isrequired

---

## Output Types

| Type | Description |
|---|---|
| **Report (table)** | Standard data table with column headers |
| **Dashboard** | Stat-card summary row above a detail table |
| **Bar chart** | Chart.js bar chart |
| **Line chart** | Chart.js line chart |
| **Pie chart** | Chart.js pie chart |
| **Doughnut chart** | Chart.js doughnut chart |
| **Radar chart** | Chart.js radar chart |

---

## Embedding a Report

1. Open any report and click **Embed** (or scroll to the embed panel at the bottom of the view page).
2. Copy the `<iframe>` snippet and paste it into any webpage or Moodle HTML block.
3. The embed endpoint (`embed.php`) requires the viewer to be logged in to this Moodle site.
4. The iframe auto-resizes to its content height via `postMessage`.

---

## Permissions

| Capability | Default roles |
|---|---|
| `local/ai_reportcreator:manage` |   |

### Roles

The plugin automatically creates a custom role **"AI Report creator"** during installation or upgrade.

| Property | Value |
|---|---|
| **Archetype** | Manager (defaults copied on creation) |
| **Assignable context** | System, Category |
| **Who can assign it** | Site administrators only |
| **Default capability** | `local/ai_reportcreator:manage` = Allow |

To verify or adjust role assignment permissions, go to:
**Site administration → Users → Permissions → Define roles → AI Report creator → Allow role assignments**
and confirm only the **Administrator** role is listed.

---

## License

GNU General Public License v3 or later — see [https://www.gnu.org/licenses/gpl-3.0.html](https://www.gnu.org/licenses/gpl-3.0.html).
