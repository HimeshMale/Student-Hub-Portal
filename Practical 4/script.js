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


