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

$string['agent_sql'] = 'סוכן SQL';
$string['agent_template'] = 'סוכן תבנית';
$string['ai_reportcreator:manage'] = 'ניהול דוחות AI';
$string['aireports'] = 'דוחות AI';
$string['aistats'] = 'סטטיסטיקות יצירת AI';
$string['api_not_configured'] = 'ה-AI middleware אינו מוגדר. אנא פנה למנהל האתר.';
$string['apierror'] = 'ה-AI middleware החזיר שגיאה: {$a}';
$string['apikey'] = 'מפתח API (Bearer token)';
$string['apikey_desc'] = 'מפתח API בן 64 תווים הקסדצימליים, המוצג בלוח הניהול של ה-middleware לאחר יצירה או חידוש של הדייר.';
$string['back'] = 'חזרה';
$string['col_actions'] = 'פעולות';
$string['col_created'] = 'נוצר';
$string['col_name'] = 'שם';
$string['col_type'] = 'סוג';
$string['confirmdelete'] = 'האם אתה בטוח שברצונך למחוק דוח זה?';
$string['copied'] = 'הועתק!';
$string['copycode'] = 'העתק';
$string['createreport'] = 'יצירת דוח חדש';
$string['curlerror'] = 'לא ניתן להתחבר ל-AI middleware: {$a}';
$string['deletereport'] = 'מחיקה';
$string['done'] = 'הושלם';
$string['editreport'] = 'עריכה';
$string['editreporttitle'] = 'עריכת דוח';
$string['embed'] = 'הטמעה';
$string['embedcode'] = 'הטמע דוח זה';
$string['embedinstructions'] = 'העתק את הקוד למטה והדבק אותו בכל דף אינטרנט כדי להטמיע את הדוח. הצופה חייב להיות מחובר לאתר זה כדי לצפות בו.';
$string['errorheading'] = 'שגיאה';
$string['exportcsv'] = 'ייצוא CSV';
$string['generate'] = 'יצירה';
$string['generating'] = 'קורא ל-AI middleware...';
$string['generationtime'] = 'זמן עיבוד';
$string['httpstatus'] = 'HTTP {$a}';
$string['httpstatuslabel'] = 'HTTP';
$string['manage'] = 'ניהול דוחות AI';
$string['middlewareurl'] = 'כתובת URL של נקודת הקצה של ה-middleware';
$string['middlewareurl_desc'] = 'כתובת URL מלאה הכוללת את מזהה ה-UUID של הדייר בנתיב. פורמט: https://your-host/api/v1/{tenant-uuid}/report-creator — לדוגמה: https://api.example.com/api/v1/550e8400-e29b-41d4-a716-446655440000/report-creator';
$string['middlewareurlempty'] = 'כתובת ה-URL של ה-middleware ריקה.';
$string['moodleversion'] = 'גרסת Moodle הנשלחת ל-middleware';
$string['nlrequest'] = 'תאר מה תרצה לראות';
$string['nlrequest_help'] = 'טיפים לכתיבת בקשה טובה:<ul><li>התחל במה שתרצה <strong>לראות</strong>: ספירה, רשימה, סכום, ממוצע וכדומה.</li><li>ציין <strong>טווח זמן</strong> אם רלוונטי — לדוגמה: <em>ב-30 הימים האחרונים</em>.</li><li>הוסף <strong>שדות מותאמים אישית של קורס</strong> על ידי כתיבת: <em>course custom fields = isfrontal,isrequired</em>.</li><li>הוסף <strong>שדות מותאמים אישית של משתמש</strong> על ידי כתיבת: <em>user info fields = department,ouid,ouname,managerid</em>.</li><li><strong>דוגמה:</strong> <em>הצג לי את מספר ההרשמות הפעילות לפי קורס ב-30 הימים האחרונים, course custom fields = isfrontal,isrequired</em></li></ul>';
$string['nlrequest_placeholder'] = 'לדוגמה: הצג לי את מספר ההרשמות הפעילות לפי קורס ב-30 הימים האחרונים, course custom fields = isfrontal,isrequired';
$string['nopendingreportdata'] = 'לא נמצאו נתוני דוח ממתינים — אנא צור מחדש.';
$string['noreports'] = 'אין עדיין דוחות. צור את הדוח הראשון שלך.';
$string['pluginname'] = 'יוצר דוחות AI';
$string['privacy:metadata:ai_middleware'] = 'כדי ליצור דוח, הבקשה בשפה חופשית של המשתמש נשלחת לשירות AI middleware חיצוני שהוגדר על ידי מנהל האתר.';
$string['privacy:metadata:ai_middleware:request'] = 'הבקשה בשפה חופשית שהוקלדה על ידי המשתמש.';
$string['privacy:metadata:ai_middleware:system'] = 'מזהה המערכת הקוראת (Moodle).';
$string['privacy:metadata:ai_middleware:system_version'] = 'גרסת Moodle שממנה נשלחת הבקשה.';
$string['privacy:metadata:ai_middleware:template_type'] = 'סוג הפלט המבוקש עבור הדוח.';
$string['privacy:metadata:local_ai_reportcreator_rpts'] = 'מידע על הדוחות שנוצרו באמצעות AI על ידי כל משתמש.';
$string['privacy:metadata:local_ai_reportcreator_rpts:name'] = 'השם שניתן לדוח.';
$string['privacy:metadata:local_ai_reportcreator_rpts:nl_request'] = 'הבקשה בשפה חופשית שהוקלדה על ידי המשתמש לתיאור הדוח.';
$string['privacy:metadata:local_ai_reportcreator_rpts:sql_query'] = 'שאילתת ה-SQL שנוצרה מבקשת המשתמש.';
$string['privacy:metadata:local_ai_reportcreator_rpts:template_html'] = 'תבנית ה-HTML שנוצרה עבור הדוח.';
$string['privacy:metadata:local_ai_reportcreator_rpts:template_type'] = 'סוג הפלט שנבחר לדוח (טבלה, לוח בקרה, תרשים וכדומה).';
$string['privacy:metadata:local_ai_reportcreator_rpts:timecreated'] = 'מועד יצירת הדוח.';
$string['privacy:metadata:local_ai_reportcreator_rpts:timemodified'] = 'מועד השינוי האחרון של הדוח.';
$string['privacy:metadata:local_ai_reportcreator_rpts:tokens_completion'] = 'מספר טוקני ה-AI של התשובה ששימשו ליצירת הדוח.';
$string['privacy:metadata:local_ai_reportcreator_rpts:tokens_prompt'] = 'מספר טוקני ה-AI של הבקשה ששימשו ליצירת הדוח.';
$string['privacy:metadata:local_ai_reportcreator_rpts:tokens_total'] = 'סך כל טוקני ה-AI ששימשו ליצירת הדוח.';
$string['privacy:metadata:local_ai_reportcreator_rpts:userid'] = 'מזהה המשתמש שיצר את הדוח.';
$string['progress_almost'] = 'כמעט מוכן…';
$string['progress_calling'] = 'מתחבר ל-AI middleware…';
$string['progress_processing'] = 'מעבד את הבקשה…';
$string['progress_sql'] = 'מייצר שאילתת SQL…';
$string['progress_template'] = 'בונה את תבנית הדוח…';
$string['reportdeleted'] = 'הדוח נמחק בהצלחה.';
$string['reportlist'] = 'הדוחות שלי';
$string['reportname'] = 'שם הדוח';
$string['reportupdated'] = 'הדוח עודכן בהצלחה.';
$string['request'] = 'בקשה';
$string['role:ai_reportcreator'] = 'יוצר דוחות AI';
$string['role:ai_reportcreator_desc'] = 'יכול ליצור דוחות מבוססי AI. מבוסס על ארכיטיפ המנהל; ניתן להקצות ברמת מערכת או קטגוריה על ידי מנהל האתר בלבד.';
$string['running'] = 'פועל…';
$string['savefailed'] = 'שמירת הדוח נכשלה.';
$string['savereport'] = 'שמירת שינויים';
$string['savingerror'] = 'הדוח נוצר אך לא ניתן היה לשמור אותו: {$a}';
$string['sqlerror'] = 'שאילתת ה-SQL החזירה שגיאה: {$a}';
$string['sqlreadonlyerror'] = 'שאילתת ה-SQL מכילה פקודות כתיבה או DDL ולא ניתן להריץ אותה מטעמי אבטחה.';
$string['streamtimeout'] = 'זמן קצוב להזרמה (שניות)';
$string['streamtimeout_desc'] = 'זמן ההמתנה המרבי לסיום הזרמת הדוח מה-middleware לפני ביטול. מינימום 30 שניות.';
$string['templatetype'] = 'סוג פלט';
$string['testconnection'] = 'בדיקת חיבור';
$string['testconnection_fail'] = 'החיבור נכשל';
$string['testconnection_success'] = 'החיבור הצליח';
$string['testingconnection'] = 'בודק…';
$string['tokencompletion'] = 'טוקנים של התשובה';
$string['tokenprompt'] = 'טוקנים של הבקשה';
$string['tokentotal'] = 'סה"כ טוקנים';
$string['type_bar'] = 'תרשים — עמודות';
$string['type_dashboard'] = 'לוח בקרה (כרטיסי סטטיסטיקה + טבלה)';
$string['type_doughnut'] = 'תרשים — דונאט';
$string['type_line'] = 'תרשים — קו';
$string['type_pie'] = 'תרשים — עוגה';
$string['type_radar'] = 'תרשים — ראדאר';
$string['type_report'] = 'דוח (טבלה)';
$string['unknownerror'] = 'שגיאה לא ידועה';
$string['viewreport'] = 'צפייה בדוח';
$string['viewsql'] = 'צפייה ב-SQL';
$string['viewsqlfor'] = 'שאילתת SQL — {$a}';
