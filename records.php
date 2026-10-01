<?php
// records.php
// Reads records.csv and shows all saved assessments in a table.
// The last column of each row is the risk level.

$file = __DIR__ . "/records.csv";
$records = [];

if (file_exists($file)) {
    $handle = fopen($file, "r");
    if ($handle) {
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 9) {   // ignore broken lines
                $records[] = $row;
            }
        }
        fclose($handle);
    }
}

// Count how many patients are in each risk level
$total = count($records);
$lowCount = 0;
$mediumCount = 0;
$highCount = 0;

foreach ($records as $r) {
    if ($r[8] == "Low Risk") {
        $lowCount++;
    } elseif ($r[8] == "Medium Risk") {
        $mediumCount++;
    } elseif ($r[8] == "High Risk") {
        $highCount++;
    }
}

// Show the newest record first
$records = array_reverse($records);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Saved Records - Patient Health Risk Assessment</title>
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
        <a href="records.php" class="active">Saved Records</a>
      </nav>
    </div>
  </div>

  <main class="container wide">

    <div class="page-title">
      <h1>Saved Records</h1>
      <p>All completed assessments, newest first.</p>
    </div>

    <!-- Summary boxes -->
    <div class="stats">
      <div class="stat"><b><?php echo $total; ?></b><span>Total patients</span></div>
      <div class="stat s-low"><b><?php echo $lowCount; ?></b><span>Low risk</span></div>
      <div class="stat s-medium"><b><?php echo $mediumCount; ?></b><span>Medium risk</span></div>
      <div class="stat s-high"><b><?php echo $highCount; ?></b><span>High risk</span></div>
    </div>

    <div class="card">

      <?php if ($total == 0) { ?>

        <p>No records saved yet. Fill the form to add the first one.</p>
        <p><a class="btn btn-green" href="index.html">Start an Assessment</a></p>

      <?php } else { ?>

        <div class="table-wrap">
          <table>
            <tr>
              <th>Date</th>
              <th>Name</th>
              <th>Age</th>
              <th>Gender</th>
              <th>BMI</th>
              <th>BP</th>
              <th>Sugar</th>
              <th>Score</th>
              <th>Risk Level</th>
            </tr>

            <?php foreach ($records as $row) {
                // Colour of the badge depends on the level
                $badge = "medium";
                if ($row[8] == "Low Risk") {
                    $badge = "low";
                } elseif ($row[8] == "High Risk") {
                    $badge = "high";
                }
            ?>
              <tr>
                <td><?php echo htmlspecialchars($row[0]); ?></td>
                <td><?php echo htmlspecialchars($row[1]); ?></td>
                <td><?php echo htmlspecialchars($row[2]); ?></td>
                <td><?php echo htmlspecialchars($row[3]); ?></td>
                <td><?php echo htmlspecialchars($row[4]); ?></td>
                <td><?php echo htmlspecialchars($row[5]); ?></td>
                <td><?php echo htmlspecialchars($row[6]); ?></td>
                <td><?php echo htmlspecialchars($row[7]); ?> / 14</td>
                <td><span class="pill <?php echo $badge; ?>"><?php echo htmlspecialchars($row[8]); ?></span></td>
              </tr>
            <?php } ?>
          </table>
        </div>

      <?php } ?>

    </div>

  </main>

  <p class="site-note">This is a college project for learning. It does not give a medical diagnosis. Please consult a doctor for real medical advice.</p>

  <div class="credit">
    This website is made by <strong>Shaikh Fiza</strong> &nbsp;|&nbsp; Roll No: <strong>19048</strong> &nbsp;|&nbsp; <strong>TyBSc.IT</strong>
  </div>

</body>
</html>
