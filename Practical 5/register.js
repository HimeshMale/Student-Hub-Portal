const usernameInput = document.getElementById('usrname');
const passwordInput = document.getElementById('passwd');
const confirmPasswordInput = document.getElementById('confirmpasswd');
const form = document.getElementById('form');

const usrError = document.getElementById('usrError');
const passwdError = document.getElementById('passwdError');
const confirmPasswdError = document.getElementById('confirmPasswdError');

const usrRegex = /^(D2[1-6]DCE(00[1-9]|0[1-9][0-9]|1[0-9][0-9]|200)|2[1-5]DCE(00[1-9]|0[1-9][0-9]|1[0-9][0-9]|200))$/;
const passwordRegex = /^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/;

// generic regex-based field validator (username, password)
function validateField(inputElement, regex, errorElement, errorMessage) {
  const value = inputElement.value;

  if (value === '') {
    inputElement.classList.remove('valid', 'invalid');
    errorElement.textContent = '';
    return false;
  }

  const isValid = regex.test(value);

  if (isValid) {
    inputElement.classList.add('valid');
    inputElement.classList.remove('invalid');
    errorElement.textContent = '';
  } else {
    inputElement.classList.add('invalid');
    inputElement.classList.remove('valid');
    errorElement.textContent = errorMessage;
  }

  return isValid;
}

// separate validator just for confirm password (matching logic, not regex)
function validateConfirmPassword() {
  const passwordValue = passwordInput.value;
  const confirmValue = confirmPasswordInput.value;

  if (confirmValue === '') {
    confirmPasswordInput.classList.remove('valid', 'invalid');
    confirmPasswdError.textContent = '';
    return false;
  }

  const isMatch = confirmValue === passwordValue;

  if (isMatch) {
    confirmPasswordInput.classList.add('valid');
    confirmPasswordInput.classList.remove('invalid');
    confirmPasswdError.textContent = '';
  } else {
    confirmPasswordInput.classList.add('invalid');
    confirmPasswordInput.classList.remove('valid');
    confirmPasswdError.textContent = "Passwords do not match";
  }

  return isMatch;
}

// blur listeners
usernameInput.addEventListener('blur', () => {
  validateField(
    usernameInput,
    usrRegex,
    usrError,
    "Invalid username format (e.g. 25DCE001 or D26DCE001)"
  );
});

passwordInput.addEventListener('blur', () => {
  validateField(
    passwordInput,
    passwordRegex,
    passwdError,
    "Password must be 8+ chars, include 1 uppercase, 1 digit, 1 special char"
  );
});

confirmPasswordInput.addEventListener('blur', () => {
  validateConfirmPassword();
});

// submit handler
form.addEventListener('submit', (e) => {
  const isUsernameValid = validateField(
    usernameInput,
    usrRegex,
    usrError,
    "Invalid username format (e.g. 25DCE001 or D26DCE001)"
  );
  const isPasswordValid = validateField(
    passwordInput,
    passwordRegex,
    passwdError,
    "Password must be 8+ chars, include 1 uppercase, 1 digit, 1 special char"
  );
  const isConfirmPasswordValid = validateConfirmPassword();

  if (!isUsernameValid || !isPasswordValid || !isConfirmPasswordValid) {
    e.preventDefault();
    return;
  }

  window.location.href = "login.html"; // or wherever registration should redirect
});