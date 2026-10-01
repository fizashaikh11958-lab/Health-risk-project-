// report.js
// Handles the "Download PDF" and "Print" buttons on the report page.
// The PDF is created with the html2pdf.js library (loaded in result.php).

var downloadBtn = document.getElementById("downloadBtn");
var printBtn = document.getElementById("printBtn");
var report = document.getElementById("report");

// ---------- Download the report as PDF ----------
if (downloadBtn && report) {
  downloadBtn.addEventListener("click", function () {

    // If the library did not load (for example no internet), use the print option
    if (typeof html2pdf === "undefined") {
      alert("PDF library could not be loaded. Please check your internet connection.\n\nYou can also click Print and choose 'Save as PDF'.");
      return;
    }

    var oldText = downloadBtn.textContent;
    downloadBtn.textContent = "Creating PDF...";
    downloadBtn.disabled = true;

    var options = {
      margin: 8,
      filename: downloadBtn.getAttribute("data-filename") || "Health_Risk_Report.pdf",
      image: { type: "jpeg", quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true },
      jsPDF: { unit: "mm", format: "a4", orientation: "portrait" },
      pagebreak: { mode: ["avoid-all", "css"] }
    };

    html2pdf().set(options).from(report).save().then(function () {
      downloadBtn.textContent = oldText;
      downloadBtn.disabled = false;
    }).catch(function () {
      downloadBtn.textContent = oldText;
      downloadBtn.disabled = false;
      alert("Sorry, the PDF could not be created. Please try the Print button and choose 'Save as PDF'.");
    });
  });
}

// ---------- Print the report ----------
if (printBtn) {
  printBtn.addEventListener("click", function () {
    window.print();
  });
}
