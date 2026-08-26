const usernameInput = document.getElementById("usr");
const passwordInput = document.getElementById("passwd");
const form = document.getElementById("form");

const usrError = document.getElementById("usrError");
const passwdError = document.getElementById("passwdError");

const usrRegex =
  /^(D2[1-6]DCE(00[1-9]|0[1-9][0-9]|1[0-9][0-9]|200)|2[1-5]DCE(00[1-9]|0[1-9][0-9]|1[0-9][0-9]|200))$/;
const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/;

function validateField(inputElement, regex, errorElement, errorMessage) {
  const value = inputElement.value;
  const isValid = regex.test(value);

  if (value === "") {
    inputElement.classList.remove("valid", "invalid");
    errorElement.textContent = "";
    return false; // still "invalid" for submit purposes, just silent
  }

  if (isValid) {
    inputElement.classList.add("valid");
    inputElement.classList.remove("invalid");
    errorElement.textContent = "";
  } else {
    inputElement.classList.add("invalid");
    inputElement.classList.remove("valid");
    errorElement.textContent = errorMessage;
  }

  return isValid;
}

// changed from 'input' to 'blur'
usernameInput.addEventListener("blur", () => {
  validateField(
    usernameInput,
    usrRegex,
    usrError,
    "Invalid username format (e.g. 25DCE001 or D25DCE001)",
  );
});

// changed from 'input' to 'blur'
passwordInput.addEventListener("blur", () => {
  validateField(
    passwordInput,
    passwordRegex,
    passwdError,
    "Password must be 8+ chars, include 1 uppercase, 1 digit, 1 special char",
  );
});

form.addEventListener("submit", (e) => {
  const isUsernameValid = validateField(
    usernameInput,
    usrRegex,
    usrError,
    "Invalid username format (e.g. 25DCE001 or D25DCE001)",
  );
  const isPasswordValid = validateField(
    passwordInput,
    passwordRegex,
    passwdError,
    "Password must be 8+ chars, include 1 uppercase, 1 digit, 1 special char",
  );

  if (!isUsernameValid || !isPasswordValid) {
    e.preventDefault();
    return;
  }

  window.location.href = "home.html";
});
