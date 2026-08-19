
  // const btn = document.getElementById("darkBtn");
  // btn.addEventListener("click", function () {
  //   document.body.classList.toggle("dark-mode");

  //   if (document.body.classList.contains("dark-mode")) {
  //     btn.textContent = "Light Mode";
  //   } else {
  //     btn.textContent = "Dark Mode";
  //   }
  // });
const btn = document.getElementById("darkBtn");

// Check saved preference when page loads
if (localStorage.getItem("theme") === "dark") {
  document.body.classList.add("dark-mode");
  btn.textContent = "Light Mode";
}

// Toggle on click and save preference
btn.addEventListener("click", function () {
  document.body.classList.toggle("dark-mode");

  if (document.body.classList.contains("dark-mode")) {
    btn.textContent = "Light Mode";
    localStorage.setItem("theme", "dark");
  } else {
    btn.textContent = "Dark Mode";
    localStorage.setItem("theme", "light");
  }
});


//login validation
document.addEventListener('DOMContentLoaded', function () {
  console.log('login.js loaded');

  const loginBtn = document.getElementById('home');

  if (!loginBtn) {
    console.error('Login button not found — check the id="home" matches your HTML');
    return;
  }

  loginBtn.addEventListener('click', function () {
    console.log('Login button clicked');

    const usrname = document.getElementById('usrname');
    const passwd = document.getElementById('passwd');

    if (!usrname || !passwd) {
      console.error('Username or password input not found — check IDs match');
      return;
    }

    if (usrname.value.trim() === '' || passwd.value.trim() === '') {
      alert('Both Username and Password are required.');
      return;
    }

    window.location.href = 'home.html';
  });
});

document.getElementById('registerBtn').addEventListener('click', function () {
  const usrname = document.getElementById('usrname');
  const passwd = document.getElementById('passwd');
  const confirmpasswd = document.getElementById('confirmpasswd');

  if (usrname.value.trim() === '' || passwd.value.trim() === '' || confirmpasswd.value.trim() === '') {
    alert('All fields are required.');
    return;
  }

  if (passwd.value !== confirmpasswd.value) {
    alert('Passwords do not match.');
    return;
  }

  window.location.href = 'login.html';
});