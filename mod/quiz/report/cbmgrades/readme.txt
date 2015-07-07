CBM Grades Readme.txt file
Tony Gardner-Medwin (a.gardner-medwin@ucl.ac.uk) 
v 2.7.02 (23 Aug 2014) for Moodle 2.7, 2.7.1, 2.8

 This plugin directory ('cbmgrades') belongs in /moodle/mod/quiz/report .
 It displays scores appropriate for interpreting performance when using CBM.
 It optionally displays responses for individual questions.
 Scores can be based either on the whole quiz or on just those questions which the student has chosen to respond to. 
 This latter is appropriate when a student uses the quiz for self-testing, choosing which Qs s/he wishes to work with.

The original plugin (v.1.2) was written for the CBM behaviours modified in Moodle 2.6 and also works with 
CBM in core Moodle in Moodle 2.3+. It is compatible with the patches available at TMedwin.net/cbm/moodle to
improve CBM behaviour in Moodle 2.3+, but is not essential with these.
Please report any issues to me Tony Gardner-Medwin (a.gardner-medwin@ucl.ac.uk)

This version differs from ones suitable for Moodle 2.6 because of small changes in core code.

23/11/2013 (v. 1.1.0) A help Icon linking to Moodle Docs was added 
29/11/2013 (v. 1.2.0) Code to calculate accuracy involving partially correct answers improved (in cbmgrades_table.php)
           required addidng rawfraction to $lateststeps : 
           function load_question_latest_steps(qubaid_condition $qubaids) added (code from Tim Hunt)
24/5/2014 (v.1.4) For 2.7: minor (but necessary) changes:
          Added null handling at start of load_question_latest_steps in cbmgrades_table.php
          quiz_has_questions() in place of quiz_questions_in_quiz() L156 in report.php
          [NB v.1.3 is obsolete]
11/8/2014 (v.2.7.0) Version name is now related to the Moodle version it is for. This is for Moodle 2.7 and 2.71
          Corrects a freeze caused if user tried to sort the report by special CBM scores in 1.4
          Now includes a column showing the total accumulated CBM marks.
          Clarifies which result columns are based on just those Qs the student has chosen, when this is selected
18/8/14   (v2.7.01) In CBM Grades report:
          Adds time columns in place of State column
          Shows CB Grade (CB accuracy * Quiz maxgrade) as well as Moodle Grade (Marks scaled to maxgrade)
          Only shows CB Bonus, CB Accuracy if CBM was used in the attempt
          Shows total marks even for an incomplete attempt
23/8/14   Minor correction affecting partially correct items in case people use this Plugin without the 
          code modifications at tmedwin.net/cbm/moodle/download
TGM 23/8/2014
