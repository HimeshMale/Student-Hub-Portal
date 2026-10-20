// Frontend validation: same rules as includes/registration.php
document.getElementById("regForm").addEventListener("submit", function (ev) {
  var usr     = document.getElementById("usr");
  var passwd  = document.getElementById("passwd");
  var confirm = document.getElementById("confirmpasswd");
  var valid   = true;

  function setError(input, errId, msg) {
    document.getElementById(errId).textContent = msg;
    input.classList.toggle("invalid", msg !== "");
    input.classList.toggle("valid", msg === "");
    if (msg !== "") valid = false;
  }

  // Username (server converts to uppercase, so allow any case here)
  var u = usr.value.trim();
  var uMsg = "";
  if (u === "") uMsg = "Username is required.";
  else if (!/^[A-Za-z0-9_]{3,20}$/.test(u)) uMsg = "3-20 characters: letters, numbers, underscore only.";
  setError(usr, "usrError", uMsg);

  // Password
  var p = passwd.value;
  var pMsg = "";
  if (p === "") pMsg = "Password is required.";
  else if (p.length < 8) pMsg = "Password must be at least 8 characters.";
  else if (!/[A-Z]/.test(p)) pMsg = "Password needs at least one uppercase letter.";
  else if (!/[a-z]/.test(p)) pMsg = "Password needs at least one lowercase letter.";
  else if (!/[0-9]/.test(p)) pMsg = "Password needs at least one number.";
  else if (!/[^A-Za-z0-9]/.test(p)) pMsg = "Password needs at least one special character.";
  setError(passwd, "passwdError", pMsg);

  // Confirm password
  var cMsg = "";
  if (confirm.value === "") cMsg = "Please confirm your password.";
  else if (confirm.value !== p) cMsg = "Passwords do not match.";
  setError(confirm, "confirmError", cMsg);

  if (!valid) ev.preventDefault();   // block submit; duplicates are checked on the server
});