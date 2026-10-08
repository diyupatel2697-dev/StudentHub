document.getElementById("registrationForm").addEventListener("submit", function(event)
{
    let valid = true;

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const mobile = document.getElementById("mobile").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const course = document.getElementById("course").value;
    const year = document.getElementById("year").value;
    const gender = document.querySelector('input[name="gender"]:checked');
    const terms = document.getElementById("terms").checked;

    document.getElementById("nameError").textContent = "";
    document.getElementById("emailError").textContent = "";
    document.getElementById("mobileError").textContent = "";
    document.getElementById("passwordError").textContent = "";
    document.getElementById("confirmPasswordError").textContent = "";
    document.getElementById("courseError").textContent = "";
    document.getElementById("yearError").textContent = "";
    document.getElementById("genderError").textContent = "";
    document.getElementById("termsError").textContent = "";

    const namePattern = /^[A-Za-z ]{2,50}$/;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const mobilePattern = /^[0-9]{10}$/;

    if (!namePattern.test(name))
    {
        document.getElementById("nameError").textContent =
            "Enter a valid name.";

        valid = false;
    }

    if (!emailPattern.test(email))
    {
        document.getElementById("emailError").textContent =
            "Enter a valid email.";

        valid = false;
    }

    if (!mobilePattern.test(mobile))
    {
        document.getElementById("mobileError").textContent =
            "Mobile number must contain 10 digits.";

        valid = false;
    }

    if (password.length < 6)
    {
        document.getElementById("passwordError").textContent =
            "Password must contain at least 6 characters.";

        valid = false;
    }

    if (password !== confirmPassword)
    {
        document.getElementById("confirmPasswordError").textContent =
            "Passwords do not match.";

        valid = false;
    }

    if (course === "")
    {
        document.getElementById("courseError").textContent =
            "Please select a course.";

        valid = false;
    }

    if (year === "")
    {
        document.getElementById("yearError").textContent =
            "Please select a year.";

        valid = false;
    }

    if (!gender)
    {
        document.getElementById("genderError").textContent =
            "Please select your gender.";

        valid = false;
    }

    if (!terms)
    {
        document.getElementById("termsError").textContent =
            "Please accept the terms and conditions.";

        valid = false;
    }

    if (!valid)
    {
        event.preventDefault();
    }
});