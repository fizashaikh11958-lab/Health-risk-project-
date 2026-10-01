// script.js
// This file does three things:
// 1. Shows the BMI (with its category) while the user types
// 2. Fills sample data when the sample button is clicked
// 3. Checks the form before it is sent to PHP

var form = document.getElementById("healthForm");
var heightInput = document.getElementById("height");
var weightInput = document.getElementById("weight");
var bmiLive = document.getElementById("bmiLive");
var errorBox = document.getElementById("errorBox");


// ---------- 1. Show BMI live ----------
function showBMI() {
  var h = parseFloat(heightInput.value);
  var w = parseFloat(weightInput.value);

  if (h > 0 && w > 0) {
    var heightInMeter = h / 100;
    var bmi = w / (heightInMeter * heightInMeter);
    var category = "";

    if (bmi < 18.5) {
      category = "Underweight";
    } else if (bmi < 25) {
      category = "Normal weight";
    } else if (bmi < 30) {
      category = "Overweight";
    } else {
      category = "Obese";
    }

    bmiLive.innerHTML = "Your BMI: <strong>" + bmi.toFixed(1) + "</strong> (" + category + ")";
    bmiLive.classList.add("show");
  } else {
    bmiLive.classList.remove("show");
    bmiLive.innerHTML = "";
  }
}

heightInput.addEventListener("input", showBMI);
weightInput.addEventListener("input", showBMI);


// ---------- 2. Sample data button (useful for demo) ----------
document.getElementById("sampleBtn").addEventListener("click", function () {
  document.getElementById("name").value = "Rahul Sharma";
  document.getElementById("age").value = 48;
  document.getElementById("gender").value = "Male";
  heightInput.value = 172;
  weightInput.value = 84;
  document.getElementById("systolic").value = 138;
  document.getElementById("diastolic").value = 88;
  document.getElementById("sugar").value = 112;
  document.getElementById("smoking").value = "sometimes";
  document.getElementById("exercise").value = "sometimes";
  document.getElementById("family").value = "yes";
  showBMI();
});


// ---------- 3. Validate the form ----------
// Each rule: which field, and the message if it is wrong
function markField(id, isWrong, message, errors) {
  var field = document.getElementById(id);
  if (isWrong) {
    field.classList.add("invalid");
    errors.push(message);
  } else {
    field.classList.remove("invalid");
  }
}

form.addEventListener("submit", function (event) {
  var errors = [];

  var name = document.getElementById("name").value.trim();
  var age = parseInt(document.getElementById("age").value);
  var height = parseFloat(heightInput.value);
  var weight = parseFloat(weightInput.value);
  var systolic = parseInt(document.getElementById("systolic").value);
  var diastolic = parseInt(document.getElementById("diastolic").value);
  var sugar = parseInt(document.getElementById("sugar").value);

  markField("name", name === "", "Please enter the patient name.", errors);
  markField("age", isNaN(age) || age < 1 || age > 120, "Age must be between 1 and 120.", errors);
  markField("gender", document.getElementById("gender").value === "", "Please select the gender.", errors);
  markField("height", isNaN(height) || height < 50 || height > 250, "Height must be between 50 and 250 cm.", errors);
  markField("weight", isNaN(weight) || weight < 10 || weight > 300, "Weight must be between 10 and 300 kg.", errors);
  markField("systolic", isNaN(systolic) || systolic < 70 || systolic > 250, "Upper blood pressure must be between 70 and 250.", errors);
  markField("diastolic", isNaN(diastolic) || diastolic < 40 || diastolic > 150, "Lower blood pressure must be between 40 and 150.", errors);
  markField("sugar", isNaN(sugar) || sugar < 40 || sugar > 500, "Blood sugar must be between 40 and 500 mg/dL.", errors);
  markField("smoking", document.getElementById("smoking").value === "", "Please answer the smoking question.", errors);
  markField("exercise", document.getElementById("exercise").value === "", "Please answer the exercise question.", errors);
  markField("family", document.getElementById("family").value === "", "Please answer the family history question.", errors);

  // Also make sure upper BP is more than lower BP
  if (!isNaN(systolic) && !isNaN(diastolic) && systolic <= diastolic) {
    errors.push("Upper blood pressure must be higher than lower blood pressure.");
  }

  // If there are errors, stop the form and show them
  if (errors.length > 0) {
    event.preventDefault();

    var html = "<strong>Please correct the following:</strong><ul>";
    for (var i = 0; i < errors.length; i++) {
      html += "<li>" + errors[i] + "</li>";
    }
    html += "</ul>";

    errorBox.innerHTML = html;
    errorBox.classList.add("show");
    errorBox.scrollIntoView({ behavior: "smooth", block: "center" });
  } else {
    errorBox.classList.remove("show");
    errorBox.innerHTML = "";
  }
});
