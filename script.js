document.addEventListener('DOMContentLoaded', function() {
 
    const roomSelect = document.getElementById('room');
    const feature1 = document.getElementById('feature1');
    const feature2 = document.getElementById('feature2');
    const feature3 = document.getElementById('feature3');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const bookingForm = document.getElementById('bookingForm');
    const totalCostInput = document.getElementById('totalCost');
    const submitButton = bookingForm.querySelector('button[type="submit"]');
    
    // Ensure all elements exist
    if (!roomSelect || !feature1 || !feature2 || !feature3 || !startDate || !endDate || !totalCostInput || !bookingForm || !submitButton) {
        console.error('Required form elements not found');
        return;
    }

    // Function to enable or disable the submit button based on form validity
    
    function validateForm() {
        const guestName = document.getElementById('guestName');
        const transferCode = document.getElementById('transferCode');
        
        if (guestName.value.trim() && transferCode.value.trim() && totalCostInput.value > 0) {
            submitButton.disabled = false; // Habilitar el botón de envío
        } else {
            submitButton.disabled = true; // Deshabilitar el botón de envío
        }
    }

    // Call validateForm() as soon as the page is loaded to check the initial state of the form
    validateForm();
    
//-----------Llamar a la función de validación cada vez que cambie el costo total. MIRARVESTO
    totalCostInput.addEventListener('change', validateForm);
    

    // Event listener for form validation
    const guestName = document.getElementById('guestName');
    const transferCode = document.getElementById('transferCode');
    if (guestName) guestName.addEventListener('input', validateForm);
    if (transferCode) transferCode.addEventListener('input', validateForm);

    // Function to update selected features and calculate total cost
    function updateTotalCost() {
        const roomPrice = parseInt(roomSelect.selectedOptions[0].getAttribute('data-price'));
        // Get the start and end date
        const startDateObj = new Date(startDate.value);
        const endDateObj = new Date(endDate.value);
        // Calculate the number of days
        const days = (endDateObj - startDateObj) / (1000 * 60 * 60 * 24); // Convert from milliseconds to days
        // Default total cost is room price * number of days
        let totalCost = roomPrice * days;

        if (isNaN(days) || days <= 0) {
            console.error('Invalid dates or duration');
            totalCostInput.value = 0;  // Reset the cost to 0 if dates are invalid
            return;
        }

        // Add cost for selected features
        if (feature1.checked) totalCost += 1;  // Minibar
        if (feature2.checked) totalCost += 1;  // TV-satellite
        if (feature3.checked) totalCost += 1;  // Gym

        // Update the total cost input field
        totalCostInput.value = totalCost;

        // Optionally log the total cost for debugging
        //console.log('Total Cost:', totalCost);
    }

    // Event listeners to update the total cost when the room, dates, or features are changed
    roomSelect.addEventListener('change', updateTotalCost);
    startDate.addEventListener('change', updateTotalCost);
    endDate.addEventListener('change', updateTotalCost);
    feature1.addEventListener('change', updateTotalCost);
    feature2.addEventListener('change', updateTotalCost);
    feature3.addEventListener('change', updateTotalCost);

    // Basic UI Updates: Example of showing selected room features dynamically
    function updateSelectedFeatures() {
        let features = [];
        if (feature1.checked) features.push('Minibar');
        if (feature2.checked) features.push('TV-satellite');
        if (feature3.checked) features.push('Gym');
        console.log('Selected features:', features); // Optionally log selected features
    }

    // Event listeners to dynamically show selected features
    feature1.addEventListener('change', updateSelectedFeatures);
    feature2.addEventListener('change', updateSelectedFeatures);
    feature3.addEventListener('change', updateSelectedFeatures);

    // Form submission handler (AJAX)
    bookingForm.addEventListener('submit', function(event) {
        event.preventDefault(); 

        console.log("Start Date (Frontend):", startDate.value);
        console.log("End Date (Frontend):", endDate.value);
    
        // Disable submit button to prevent multiple submissions
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';

        // Send the form data to the server (using FormData)
        const formData = new FormData(bookingForm);

        // Fetch request to handle the booking process (with transferCode verification)
        fetch('booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.status === 'success') {
                // Handle successful booking confirmation (you can display a message, redirect, etc.)
                alert('Booking Successful! Thank you for choosing our hotel.');
                window.location.href = 'index.php'; // Redirect to a confirmation or home page
            } else {
                // Handle errors (show error message)
                alert(data.message || 'There was an error processing your booking. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error processing your booking. Please try again.');
        })
        .finally(() => {
            // Re-enable submit button
            submitButton.disabled = false;
            submitButton.textContent = 'Book Now';
        });
    });

    // Initialize form validation on page load
    validateForm();

    // Initialize total cost calculation on page load
    updateTotalCost();
});

