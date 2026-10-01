PATIENT HEALTH RISK ASSESSMENT
==============================
Made by: Shaikh Fiza | Roll No: 19048 | TyBSc.IT


ABOUT THE PROJECT
-----------------
A web application that checks a person's general health risk
(Low, Medium or High) using age, BMI, blood pressure, fasting blood
sugar, smoking, exercise and family history. It shows a detailed
health report that can be downloaded as a PDF.

Technologies used:
  HTML        - structure of the pages and the form
  CSS         - medical blue/teal design, responsive layout, print styles
  JavaScript  - live BMI, form validation, PDF download
  PHP         - server side validation, score calculation, saving records

FILES
-----
  index.html   - home page with the assessment form
  style.css    - design for all pages
  script.js    - live BMI, sample data button, form validation
  result.php   - calculates the score and shows the health report
  report.js    - Download PDF and Print buttons
  records.php  - shows all saved assessments with a summary
  records.csv  - created automatically when the first record is saved

HOW TO RUN (using XAMPP)
------------------------
1. Install XAMPP and start the Apache server.
2. Copy this whole folder into  C:\xampp\htdocs\
3. Open the browser and go to:
   http://localhost/health-risk-assessment/index.html

Important:
  - PHP files do not run by double clicking. They must run on a server.
  - The PDF button needs internet (the html2pdf.js library loads from a CDN).
    If there is no internet, click Print and choose "Save as PDF".

HOW THE SCORE WORKS (maximum 14 points)
---------------------------------------
  Age             30-44 = 1,  45-59 = 2,  60 and above = 3
  BMI             Underweight = 1,  Overweight = 1,  Obese = 2
  Blood pressure  120/80 to 139/89 = 1,  140/90 or more = 2
  Blood sugar     100-125 = 1,  126 or more = 2
  Smoking         Sometimes = 1,  Regularly = 2
  Exercise        1-3 days a week = 1,  Rarely/never = 2
  Family history  Yes = 1

  Score 0 - 3   = Low Risk
  Score 4 - 7   = Medium Risk
  Score 8 - 14  = High Risk

FLOW OF THE PROJECT
-------------------
  User fills the form (index.html)
        |
  JavaScript checks the values (script.js)
        |
  Form data is sent to PHP (result.php)
        |
  PHP validates again, calculates the score, saves to records.csv
        |
  Health report is shown  ->  Download as PDF (report.js)

FUTURE IMPROVEMENTS
-------------------
  - Store data in a MySQL database instead of a CSV file
  - Login system for doctors
  - Charts for risk levels on the records page
  - More health factors such as cholesterol

NOTE
----
This is a college project for learning. It is NOT a medical tool.
Always consult a real doctor for medical advice.
