document.addEventListener("DOMContentLoaded", () => {
  const amountCells = document.querySelectorAll(".amount");

  amountCells.forEach(cell => {
    cell.addEventListener("input", updateTotal);
  });

  function updateTotal() {
    let total = 0;
    amountCells.forEach(cell => {
      const value = parseFloat(cell.textContent.replace(/[^0-9.]/g, '')) || 0;
      total += value;
    });
    document.getElementById("totalAmount").textContent = "£" + total.toFixed(2);
  }
});
