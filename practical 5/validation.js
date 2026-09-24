document.getElementById("registrationForm").addEventListener("submit", function(e) {
  e.preventDefault(); // prevent form submission
  let valid = true;

  // Regex patterns
  const namePattern = /^[A-Za-z\s]{3,}$/;
  const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
  const mobilePattern = /^[6-9]\d{9}$/;
  const passwordPattern = /^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/;

  // Name validation
  const name = document.getElementById("name").value;
  if (!namePattern.test(name)) {
    document.getElementById("nameError").textContent = "Enter a valid name (min 3 letters).";
    valid = false;
  } else document.getElementById("nameError").textContent = "";

  // Email validation
  const email = document.getElementById("email").value;
  if (!emailPattern.test(email)) {
    document.getElementById("emailError").textContent = "Enter a valid email.";
    valid = false;
  } else document.getElementById("emailError").textContent = "";

  // Mobile validation
  const mobile = document.getElementById("mobile").value;
  if (!mobilePattern.test(mobile)) {
    document.getElementById("mobileError").textContent = "Enter a valid 10-digit mobile number.";
    valid = false;
  } else document.getElementById("mobileError").textContent = "";

  // Password validation
  const password = document.getElementById("password").value;
  if (!passwordPattern.test(password)) {
    document.getElementById("passwordError").textContent = "Password must be 6+ chars, include uppercase, number & special char.";
    valid = false;
  } else document.getElementById("passwordError").textContent = "";

  // Confirm password
  const confirmPassword = document.getElementById("confirmPassword").value;
  if (password !== confirmPassword) {
    document.getElementById("confirmPasswordError").textContent = "Passwords do not match.";
    valid = false;
  } else document.getElementById("confirmPasswordError").textContent = "";

  // Course validation
  const course = document.getElementById("course").value;
  if (course === "") {
    document.getElementById("courseError").textContent = "Please select a course.";
    valid = false;
  } else document.getElementById("courseError").textContent = "";

  const year = document.getElementById("year").value;
  if (year === "") {
    document.getElementById("yearError").textContent = "Please select a year.";
    valid = false;
  } else document.getElementById("yearError").textContent = "";

  const gender = document.querySelector('input[name="gender"]:checked');
  if (!gender) {
    document.getElementById("genderError").textContent = "Please select gender.";
    valid = false;
  } else document.getElementById("genderError").textContent = "";

  // Terms validation
  const terms = document.getElementById("terms").checked;
  if (!terms) {
    document.getElementById("termsError").textContent = "You must accept terms.";
    valid = false;
  } else document.getElementById("termsError").textContent = "";

  // Final check
  if (valid) {
    alert("Registration Successful!");
    this.reset();
  }
});
