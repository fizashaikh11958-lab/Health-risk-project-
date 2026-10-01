<?php
// result.php
// Gets the form data, calculates the risk score, saves the record
// in records.csv and shows a detailed health report.

date_default_timezone_set("Asia/Kolkata");

// If someone opens this page directly, send them back to the form
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

// ---------- Small helper functions ----------

// Gives "low", "medium" or "high" depending on how many points a factor got
function statusOf($points, $max) {
    if ($points == 0) {
        return "low";
    }
    if ($points >= $max && $max > 1) {
        return "high";
    }
    return "medium";
}

// Text shown inside the coloured pill
function statusLabel($cls) {
    if ($cls == "low") {
        return "Normal";
    }
    if ($cls == "medium") {
        return "Borderline";
    }
    return "High";
}

// ---------- Step 1: Get the data from the form ----------
$name      = trim($_POST["name"] ?? "");
$age       = (int) ($_POST["age"] ?? 0);
$gender    = $_POST["gender"] ?? "";
$height    = (float) ($_POST["height"] ?? 0);
$weight    = (float) ($_POST["weight"] ?? 0);
$systolic  = (int) ($_POST["systolic"] ?? 0);
$diastolic = (int) ($_POST["diastolic"] ?? 0);
$sugar     = (int) ($_POST["sugar"] ?? 0);
$smoking   = $_POST["smoking"] ?? "";
$exercise  = $_POST["exercise"] ?? "";
$family    = $_POST["family"] ?? "";

// ---------- Step 2: Check the data again on the server ----------
// (JavaScript can be switched off, so PHP checks too)
$errors = [];

if ($name === "") {
    $errors[] = "Name is required.";
}
if ($age < 1 || $age > 120) {
    $errors[] = "Age must be between 1 and 120.";
}
if (!in_array($gender, ["Male", "Female", "Other"])) {
    $errors[] = "Please select a valid gender.";
}
if ($height < 50 || $height > 250) {
    $errors[] = "Height must be between 50 and 250 cm.";
}
if ($weight < 10 || $weight > 300) {
    $errors[] = "Weight must be between 10 and 300 kg.";
}
if ($systolic < 70 || $systolic > 250 || $diastolic < 40 || $diastolic > 150) {
    $errors[] = "Blood pressure values are not valid.";
}
if ($systolic <= $diastolic) {
    $errors[] = "Upper blood pressure must be higher than lower blood pressure.";
}
if ($sugar < 40 || $sugar > 500) {
    $errors[] = "Blood sugar must be between 40 and 500 mg/dL.";
}
if (!in_array($smoking, ["no", "sometimes", "yes"]) ||
    !in_array($exercise, ["regular", "sometimes", "none"]) ||
    !in_array($family, ["no", "yes"])) {
    $errors[] = "Please answer all the lifestyle questions.";
}

// ---------- Step 3: Calculate the score (only if there are no errors) ----------
$score = 0;
$factors = [];   // each item: [factor name, points, maximum points]
$tips = [];      // simple advice for the patient
$bmi = 0;
$bmiText = "";
$bpText = "";
$sugarText = "";

if (count($errors) == 0) {

    // ----- Age (maximum 3 points) -----
    $p = 0;
    if ($age >= 60) {
        $p = 3;
    } elseif ($age >= 45) {
        $p = 2;
    } elseif ($age >= 30) {
        $p = 1;
    }
    $score += $p;
    $factors[] = ["Age", $p, 3];

    // ----- BMI = weight / (height in metre x height in metre) (maximum 2 points) -----
    $heightM = $height / 100;
    $bmi = round($weight / ($heightM * $heightM), 1);
    $p = 0;

    if ($bmi < 18.5) {
        $bmiText = "Underweight";
        $p = 1;
        $tips[] = "Eat a balanced diet with enough protein and healthy calories to reach a normal weight.";
    } elseif ($bmi < 25) {
        $bmiText = "Normal";
    } elseif ($bmi < 30) {
        $bmiText = "Overweight";
        $p = 1;
        $tips[] = "Reduce sugary and fried food and walk for at least 30 minutes daily to lower your weight.";
    } else {
        $bmiText = "Obese";
        $p = 2;
        $tips[] = "Please talk to a doctor or dietitian about a safe weight-loss plan.";
    }
    $score += $p;
    $factors[] = ["BMI", $p, 2];
    $bmiPoints = $p;

    // ----- Blood pressure (maximum 2 points) -----
    $p = 0;
    if ($systolic >= 140 || $diastolic >= 90) {
        $bpText = "High";
        $p = 2;
        $tips[] = "Reduce salt in your food and get your blood pressure checked by a doctor soon.";
    } elseif ($systolic >= 120 || $diastolic >= 80) {
        $bpText = "Slightly raised";
        $p = 1;
        $tips[] = "Keep checking your blood pressure regularly and limit salty snacks.";
    } else {
        $bpText = "Normal";
    }
    $score += $p;
    $factors[] = ["Blood Pressure", $p, 2];
    $bpPoints = $p;

    // ----- Fasting blood sugar (maximum 2 points) -----
    $p = 0;
    if ($sugar >= 126) {
        $sugarText = "High";
        $p = 2;
        $tips[] = "Your sugar level is in the diabetic range. Please get a diabetes test and consult a doctor.";
    } elseif ($sugar >= 100) {
        $sugarText = "Slightly raised";
        $p = 1;
        $tips[] = "Avoid too many sweets and cold drinks, and check your sugar level again after a few weeks.";
    } else {
        $sugarText = "Normal";
    }
    $score += $p;
    $factors[] = ["Blood Sugar", $p, 2];
    $sugarPoints = $p;

    // ----- Smoking (maximum 2 points) -----
    $p = 0;
    if ($smoking == "yes") {
        $p = 2;
        $tips[] = "Quit smoking. It is the best thing you can do for your heart and lungs.";
    } elseif ($smoking == "sometimes") {
        $p = 1;
        $tips[] = "Try to stop smoking completely, even occasional smoking harms your heart.";
    }
    $score += $p;
    $factors[] = ["Smoking", $p, 2];

    // ----- Exercise (maximum 2 points) -----
    $p = 0;
    if ($exercise == "none") {
        $p = 2;
        $tips[] = "Start with a 30 minute walk on most days of the week.";
    } elseif ($exercise == "sometimes") {
        $p = 1;
        $tips[] = "Try to increase your exercise to at least 4 days a week.";
    }
    $score += $p;
    $factors[] = ["Exercise Habit", $p, 2];

    // ----- Family history (maximum 1 point) -----
    $p = 0;
    if ($family == "yes") {
        $p = 1;
        $tips[] = "Because of your family history, do a full health check-up every year.";
    }
    $score += $p;
    $factors[] = ["Family History", $p, 1];

    // ----- Step 4: Decide the risk level -----
    if ($score <= 3) {
        $level = "Low Risk";
        $cssClass = "low";
        $message = "Your health risk looks low. Keep up your good habits and continue regular check-ups.";
    } elseif ($score <= 7) {
        $level = "Medium Risk";
        $cssClass = "medium";
        $message = "Some of your readings or habits need attention. Small lifestyle changes can bring your risk down.";
    } else {
        $level = "High Risk";
        $cssClass = "high";
        $message = "Several risk factors were found. Please visit a doctor for a proper check-up.";
    }

    if (count($tips) == 0) {
        $tips[] = "Great work! Continue eating healthy, sleeping well and exercising regularly.";
    }
    $tips[] = "Drink enough water, sleep 7 to 8 hours and take a health check-up once a year.";

    // Position of the marker on the score bar (score goes from 0 to 14)
    $markerPercent = round((($score + 0.5) / 15) * 100, 1);

    // Report number and date
    $reportId = "HRA-" . date("Ymd") . "-" . rand(1000, 9999);
    $reportDate = date("d M Y, h:i A");

    // ----- Step 5: Save the record in a CSV file -----
    $file = __DIR__ . "/records.csv";
    $row = [
        date("d-m-Y H:i"),
        $name,
        $age,
        $gender,
        $bmi,
        $systolic . "/" . $diastolic,
        $sugar,
        $score,
        $level
    ];

    $handle = fopen($file, "a");
    if ($handle) {
        flock($handle, LOCK_EX);   // lock so two people do not write together
        fputcsv($handle, $row);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    // File name used for the PDF download
    $pdfName = "Health_Risk_Report_" . preg_replace("/[^A-Za-z0-9]+/", "_", $name) . ".pdf";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Health Report - Patient Health Risk Assessment</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- ===== Top bar ===== -->
  <div class="topbar">
    <div class="topbar-inner">
      <a class="brand" href="index.html">
        <svg viewBox="0 0 40 40" aria-hidden="true">
          <circle cx="20" cy="20" r="20" fill="#ffffff"/>
          <rect x="17" y="8" width="6" height="24" rx="2" fill="#00897b"/>
          <rect x="8" y="17" width="24" height="6" rx="2" fill="#00897b"/>
        </svg>
        <div>
          <strong>Patient Health Risk Assessment</strong>
          <span>Know your risk. Stay healthy.</span>
        </div>
      </a>
      <nav>
        <a href="index.html">New Assessment</a>
        <a href="records.php">Saved Records</a>
      </nav>
    </div>
  </div>

  <main class="container">

<?php if (count($errors) > 0) { ?>

    <div class="page-title">
      <h1>We could not create the report</h1>
      <p>Please fix the following problems and try again.</p>
    </div>

    <div class="error-box show">
      <strong>Problems found:</strong>
      <ul>
        <?php foreach ($errors as $error) { ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php } ?>
      </ul>
    </div>

    <div class="buttons">
      <a class="btn" href="index.html">Go back to the form</a>
    </div>

<?php } else { ?>

    <div class="page-title">
      <h1>Your Health Report</h1>
      <p>The report is ready. You can download it as a PDF or print it.</p>
    </div>

    <div class="buttons">
      <button type="button" id="downloadBtn" class="btn-green" data-filename="<?php echo htmlspecialchars($pdfName); ?>">Download PDF Report</button>
      <button type="button" id="printBtn" class="btn-light">Print</button>
      <a class="btn btn-light" href="index.html">New Assessment</a>
      <a class="btn btn-light" href="records.php">Saved Records</a>
    </div>

    <!-- ===== The report (this part becomes the PDF) ===== -->
    <div class="report" id="report">

      <div class="report-head">
        <div class="brand">
          <svg viewBox="0 0 40 40" aria-hidden="true">
            <circle cx="20" cy="20" r="20" fill="#ffffff"/>
            <rect x="17" y="8" width="6" height="24" rx="2" fill="#00897b"/>
            <rect x="8" y="17" width="24" height="6" rx="2" fill="#00897b"/>
          </svg>
          <div>
            <strong>Patient Health Risk Report</strong>
            <span>Health Risk Assessment System</span>
          </div>
        </div>
        <div class="meta">
          Report No: <strong><?php echo $reportId; ?></strong><br>
          Date: <?php echo $reportDate; ?>
        </div>
      </div>

      <div class="report-body">

        <h3>Patient Details</h3>
        <div class="patient-grid">
          <div><small>Name</small><?php echo htmlspecialchars($name); ?></div>
          <div><small>Age</small><?php echo $age; ?> years</div>
          <div><small>Gender</small><?php echo htmlspecialchars($gender); ?></div>
          <div><small>Height</small><?php echo $height; ?> cm</div>
          <div><small>Weight</small><?php echo $weight; ?> kg</div>
          <div><small>BMI</small><?php echo $bmi; ?></div>
        </div>

        <h3>Overall Result</h3>
        <div class="verdict <?php echo $cssClass; ?>">
          <small>Risk level based on a score of <?php echo $score; ?> out of 14</small>
          <h2><?php echo $level; ?></h2>
          <p><?php echo $message; ?></p>
        </div>

        <!-- Score bar -->
        <div class="scale">
          <div></div>
          <div></div>
          <div></div>
        </div>
        <div class="marker-wrap">
          <span class="marker" style="left: <?php echo $markerPercent; ?>%;">Score: <?php echo $score; ?></span>
        </div>
        <div class="scale-labels">
          <span>Low (0-3)</span>
          <span>Medium (4-7)</span>
          <span>High (8-14)</span>
        </div>

        <h3>Your Readings Compared With Normal Values</h3>
        <table>
          <tr>
            <th>Parameter</th>
            <th>Your Value</th>
            <th>Normal Range</th>
            <th>Status</th>
          </tr>
          <tr>
            <td>Body Mass Index (BMI)</td>
            <td><?php echo $bmi . " (" . $bmiText . ")"; ?></td>
            <td>18.5 to 24.9</td>
            <?php $c = statusOf($bmiPoints, 2); ?>
            <td><span class="pill <?php echo $c; ?>"><?php echo statusLabel($c); ?></span></td>
          </tr>
          <tr>
            <td>Blood Pressure</td>
            <td><?php echo $systolic . "/" . $diastolic . " mmHg (" . $bpText . ")"; ?></td>
            <td>Below 120/80 mmHg</td>
            <?php $c = statusOf($bpPoints, 2); ?>
            <td><span class="pill <?php echo $c; ?>"><?php echo statusLabel($c); ?></span></td>
          </tr>
          <tr>
            <td>Fasting Blood Sugar</td>
            <td><?php echo $sugar . " mg/dL (" . $sugarText . ")"; ?></td>
            <td>Below 100 mg/dL</td>
            <?php $c = statusOf($sugarPoints, 2); ?>
            <td><span class="pill <?php echo $c; ?>"><?php echo statusLabel($c); ?></span></td>
          </tr>
        </table>

        <h3>How the Score Was Calculated</h3>
        <?php foreach ($factors as $f) {
            $c = statusOf($f[1], $f[2]);
            $width = ($f[1] / $f[2]) * 100;
        ?>
          <div class="factor">
            <span class="name"><?php echo $f[0]; ?></span>
            <div class="bar"><div class="<?php echo $c; ?>" style="width: <?php echo $width; ?>%;"></div></div>
            <span class="pts"><?php echo $f[1] . " / " . $f[2]; ?></span>
          </div>
        <?php } ?>

        <h3>Health Advice</h3>
        <ul class="advice">
          <?php foreach ($tips as $tip) { ?>
            <li><?php echo $tip; ?></li>
          <?php } ?>
        </ul>

        <?php if ($cssClass == "high") { ?>
          <div class="doctor-note">
            <strong>Please see a doctor.</strong> Your score is in the high range. A doctor can check your heart, blood pressure and sugar properly and suggest the right treatment.
          </div>
        <?php } ?>

        <p class="disclaimer">
          Disclaimer: This report is generated by a college project for learning purposes only. It gives a general idea of health risk and is not a medical diagnosis. Always consult a qualified doctor for medical advice.
        </p>
      </div>

      <div class="credit" style="margin-top: 0;">
        This website is made by <strong>Shaikh Fiza</strong> &nbsp;|&nbsp; Roll No: <strong>19048</strong> &nbsp;|&nbsp; <strong>TyBSc.IT</strong>
      </div>

    </div>
    <!-- ===== End of report ===== -->

<?php } ?>

  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script src="report.js"></script>
</body>
</html>
