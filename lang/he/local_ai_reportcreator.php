<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Hebrew language strings for the AI Report Creator plugin.
 *
 * @package    local_ai_reportcreator
 * @copyright  2026 Highskills and more <info@highskills.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Core plugin strings
$string['pluginname']            = 'יוצר דוחות AI';
$string['manage']                = 'ניהול דוחות AI';
$string['ai_reportcreator:manage']  = 'ניהול דוחות AI';
$string['role:ai_reportcreator']      = 'יוצר דוחות AI';
$string['role:ai_reportcreator_desc'] = 'יכול ליצור דוחות מבוססי AI. מבוסס על ארכיטיפ המנהל; ניתן להקצות ברמת מערכת או קטגוריה על ידי מנהל האתר בלבד.';

// Pages / navigation
$string['aireports']             = 'דוחות AI';
$string['createreport']          = 'יצירת דוח חדש';
$string['reportlist']            = 'הדוחות שלי';
$string['back']                  = 'חזרה';
$string['viewreport']            = 'צפייה בדוח';

// Form fields
$string['reportname']            = 'שם הדוח';
$string['nlrequest']             = 'תאר מה תרצה לראות';
$string['nlrequest_placeholder'] = 'לדוגמה: הצג לי את מספר ההרשמות הפעילות לפי קורס ב-30 הימים האחרונים, course custom fields = isfrontal,isrequired';
$string['nlrequest_help']        = 'טיפים לכתיבת בקשה טובה:<ul><li>התחל במה שתרצה <strong>לראות</strong>: ספירה, רשימה, סכום, ממוצע וכדומה.</li><li>ציין <strong>טווח זמן</strong> אם רלוונטי — לדוגמה: <em>ב-30 הימים האחרונים</em>.</li><li>הוסף <strong>שדות מותאמים אישית של קורס</strong> על ידי כתיבת: <em>course custom fields = isfrontal,isrequired</em>.</li><li>הוסף <strong>שדות מותאמים אישית של משתמש</strong> על ידי כתיבת: <em>user info fields = department,ouid,ouname,managerid</em>.</li><li><strong>דוגמה:</strong> <em>הצג לי את מספר ההרשמות הפעילות לפי קורס ב-30 הימים האחרונים, course custom fields = isfrontal,isrequired</em></li></ul>';
$string['templatetype']          = 'סוג פלט';
$string['generate']              = 'יצירה';

// Template type options
$string['type_report']           = 'דוח (טבלה)';
$string['type_dashboard']        = 'לוח בקרה (כרטיסי סטטיסטיקה + טבלה)';
$string['type_bar']              = 'תרשים — עמודות';
$string['type_line']             = 'תרשים — קו';
$string['type_pie']              = 'תרשים — עוגה';
$string['type_doughnut']         = 'תרשים — דונאט';
$string['type_radar']            = 'תרשים — ראדאר';

// Actions
$string['editreport']            = 'עריכה';
$string['exportcsv']             = 'ייצוא CSV';
$string['viewsql']               = 'צפייה ב-SQL';
$string['viewsqlfor']            = 'שאילתת SQL — {$a}';
$string['deletereport']          = 'מחיקה';
$string['embed']                 = 'הטמעה';

// Edit page
$string['editreporttitle']       = 'עריכת דוח';
$string['savereport']            = 'שמירת שינויים';
$string['reportupdated']         = 'הדוח עודכן בהצלחה.';

// Table column headers
$string['col_name']              = 'שם';
$string['col_type']              = 'סוג';
$string['col_created']           = 'נוצר';
$string['col_actions']           = 'פעולות';

// Messages
$string['reportdeleted']         = 'הדוח נמחק בהצלחה.';
$string['confirmdelete']         = 'האם אתה בטוח שברצונך למחוק דוח זה?';
$string['noreports']             = 'אין עדיין דוחות. צור את הדוח הראשון שלך.';

// Error strings
$string['sqlerror']              = 'שאילתת ה-SQL החזירה שגיאה: {$a}';
$string['apierror']              = 'ה-AI middleware החזיר שגיאה: {$a}';
$string['sqlreadonlyerror']      = 'שאילתת ה-SQL מכילה פקודות כתיבה או DDL ולא ניתן להריץ אותה מטעמי אבטחה.';

// Admin settings
$string['middlewareurl']         = 'כתובת URL של נקודת הקצה של ה-middleware';
$string['middlewareurl_desc']    = 'כתובת URL מלאה הכוללת את מזהה ה-UUID של הדייר בנתיב. פורמט: https://your-host/api/{tenant-uuid}/query — לדוגמה: https://api.example.com/api/550e8400-e29b-41d4-a716-446655440000/query';
$string['apikey']                = 'מפתח API (Bearer token)';
$string['apikey_desc']           = 'מפתח API בן 64 תווים הקסדצימליים, המוצג בלוח הניהול של ה-middleware לאחר יצירה או חידוש של הדייר.';
$string['moodleversion']         = 'גרסת Moodle הנשלחת ל-middleware';

// Test connection
$string['testconnection']        = 'בדיקת חיבור';
$string['testconnection_success'] = 'החיבור הצליח';
$string['testconnection_fail']   = 'החיבור נכשל';

// Embed
$string['embedcode']             = 'הטמע דוח זה';
$string['embedinstructions']     = 'העתק את הקוד למטה והדבק אותו בכל דף אינטרנט כדי להטמיע את הדוח.';
$string['copycode']              = 'העתק';

// AI progress / stats
$string['generating']            = 'קורא ל-AI middleware...';
$string['progress_calling']      = 'מתחבר ל-AI middleware…';
$string['progress_processing']   = 'מעבד את הבקשה…';
$string['progress_sql']          = 'מייצר שאילתת SQL…';
$string['progress_template']     = 'בונה את תבנית הדוח…';
$string['progress_almost']       = 'כמעט מוכן…';
$string['aistats']               = 'סטטיסטיקות יצירת AI';
$string['tokenprompt']           = 'טוקנים של הבקשה';
$string['tokencompletion']       = 'טוקנים של התשובה';
$string['tokentotal']            = 'סה"כ טוקנים';
$string['generationtime']        = 'זמן עיבוד';
