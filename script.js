function updateTotalCost() {
    let totalCost = 0;

    // Room price
    const roomSelect = document.getElementById('standard');
    totalCost += parseInt(roomSelect.options[roomSelect.selectedIndex].dataset.price);

    // Feature prices (1 each)
    if (document.getElementById('feature1').checked) totalCost += 1;
    if (document.getElementById('feature2').checked) totalCost += 1;
    if (document.getElementById('feature3').checked) totalCost += 1;

    // Update the total cost input field
    document.getElementById('totalCost').value = totalCost;
}