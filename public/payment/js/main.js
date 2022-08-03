document.getElementById("dropdownParent").addEventListener("click", function() {
    document.getElementById("dropdownArea").classList.toggle("active")
});
const target = document.querySelector('#dropdownParent')

document.addEventListener('click', (event) => {
  const withinBoundaries = event.composedPath().includes(target)
  if (!withinBoundaries) {
    document.getElementById("dropdownArea").classList.remove("active")
  } 
})


