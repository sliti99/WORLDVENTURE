// script.js
function handleClick(section) {
    console.log(`Clicked on: ${section}`);
    document.getElementById('content').innerHTML = `<h2>Section "${section}" est en construction...</h2>`;
  }
  