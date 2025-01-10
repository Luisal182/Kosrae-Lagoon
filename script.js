document.addEventListener('DOMContentLoaded', function() {
    // References to the form elements
    const roomSelect = document.getElementById('room');
    const feature1 = document.getElementById('feature1');
    const feature2 = document.getElementById('feature2');
    const feature3 = document.getElementById('feature3');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const totalCostInput = document.getElementById('totalCost');
    const transferCodeInput = document.getElementById('transferCode');
    const bookingForm = document.getElementById('bookingForm');

    // Function to update the total cost
    function updateTotalCost() {
        let totalCost = 0;

        // Room price
        const selectedRoom = roomSelect.options[roomSelect.selectedIndex];
        totalCost += parseInt(selectedRoom.dataset.price);

        // Feature prices (1 for each)
        if (feature1.checked) totalCost += 1;
        if (feature2.checked) totalCost += 1;
        if (feature3.checked) totalCost += 1;

        // Update the total cost field
        totalCostInput.value = totalCost;
    }

    // Recalculate the total cost whenever options change
    roomSelect.addEventListener('change', updateTotalCost);
    feature1.addEventListener('change', updateTotalCost);
    feature2.addEventListener('change', updateTotalCost);
    feature3.addEventListener('change', updateTotalCost);

    // Function to validate the dates before submitting the form
    function validateDates() {
        const startDateValue = new Date(startDate.value);
        const endDateValue = new Date(endDate.value);
        const today = new Date();

        // Check that the start date is not earlier than the current date
        if (startDateValue < today) {
            alert('The start date cannot be earlier than the current date.');
            return false;
        }

        // Check that the end date is later than the start date
        if (endDateValue <= startDateValue) {
            alert('The end date must be later than the start date.');
            return false;
        }

        // If everything is fine, return true
        return true;
    }

    // Function to handle the transfer code
    function setTransferCode() {
        const transferCode = prompt('Please enter the generated transfer code:');
        
        // If the transfer code is valid, insert it into the appropriate field
        if (transferCode && transferCode.trim() !== '') {
            transferCodeInput.value = transferCode;
        } else {
            alert('The transfer code is required.');
        }
    }

    // Call to request the transfer code when the form is ready
    document.getElementById('generate-transfer-code').addEventListener('click', setTransferCode);

    // Function to submit the form using Fetch (AJAX)
    function submitForm(event) {
        event.preventDefault();  // Prevent the default behavior (reloading the page)

        // First, validate the dates
        if (!validateDates()) {
            return;  // If the dates are not valid, do not submit the form
        }

        // Create a FormData object with all the form data
        const formData = new FormData(bookingForm);

        // Use fetch to send the data to the backend without reloading the page
        fetch('booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Booking successful');
                window.location.href = 'index.php'; // Redirect to the main page
            } else {
                alert('Error: ' + data.message); // Show error message
            }
        })
        .catch(error => {
            alert('There was an error processing the booking.');
            console.error(error);  // Log error to console
        });
    }

    // Attach the submit form event to the submitForm function
    bookingForm.addEventListener('submit', submitForm);

    // Call the function to update the total cost when the page loads
    updateTotalCost();
});
