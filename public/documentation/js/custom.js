let scrollUp = document.querySelector(".scrollUp");

window.addEventListener("scroll", function () {
  if (window.pageYOffset > 250) {
    scrollUp.classList.add("active");
  }
  else {
    scrollUp.classList.remove("active");
  }

});

const items = document.querySelectorAll(".accordion a");
items.forEach(item => item.addEventListener('click', toggleAccordion));
function toggleAccordion() {
  this.classList.toggle('active');
  this.nextElementSibling.classList.toggle('active');
}



