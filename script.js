
document.addEventListener('DOMContentLoaded', function() {
    // Get all required form elements
    const roomSelect = document.getElementById('room');
    const feature1 = document.getElementById('feature1');
    const feature2 = document.getElementById('feature2');
    const feature3 = document.getElementById('feature3');
    const startDate = document.getElementById('start-date');
    const endDate = document.getElementById('end-date');
    const totalCostInput = document.getElementById('totalCost');
    const bookingForm = document.getElementById('bookingForm');
    
    // Function to update the total cost
    function updateTotalCost() {
        let totalCost = 0;
        
        // Get base room price
        const selectedRoom = roomSelect.options[roomSelect.selectedIndex];
        const roomPrice = parseInt(selectedRoom.dataset.price);
        
        // Calculate number of days if dates are set
        let numberOfDays = 1;
        if (startDate.value && endDate.value) {
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            numberOfDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
            if (numberOfDays < 1) numberOfDays = 1;
        }
        
        // Calculate total with duration
        totalCost = roomPrice * numberOfDays;
        
        // Add features (each costs 1)
        if (feature1.checked) totalCost += 1; // Minibar
        if (feature2.checked) totalCost += 1; // TV-satellite
        if (feature3.checked) totalCost += 1; // Gym
        
        // Update the total cost field
        totalCostInput.value = totalCost;
    }

    // Function to validate the dates
    function validateDates() {
        const startDateValue = new Date(startDate.value);
        const endDateValue = new Date(endDate.value);
        
        // Check if end date is after start date
        if (endDateValue <= startDateValue) {
            alert('The check-out date must be after the check-in date.');
            return false;
        }

        // Get selected room type to check availability
        const selectedRoom = roomSelect.value.toLowerCase();
        const roomCalendar = document.querySelector(`#${selectedRoom} .booked-dates`);
        
        if (!roomCalendar) {
            console.error('Room calendar not found');
            return false;
        }

        // Get all booked dates for the selected room
        const bookedDates = roomCalendar.querySelectorAll('.booked');
        const selectedDates = [];
        
        // Create array of selected dates
        let currentDate = new Date(startDateValue);
        while (currentDate <= endDateValue) {
            selectedDates.push(currentDate.getDate());
            currentDate.setDate(currentDate.getDate() + 1);
        }

        // Check if any selected date is booked
        for (let bookedDate of bookedDates) {
            const bookedDay = parseInt(bookedDate.textContent);
            if (selectedDates.includes(bookedDay)) {
                alert('One or more selected dates are already booked. Please choose different dates.');
                return false;
            }
        }

        return true;
    }

    // Add event listeners for all elements that affect the total cost
    roomSelect.addEventListener('change', updateTotalCost);
    feature1.addEventListener('change', updateTotalCost);
    feature2.addEventListener('change', updateTotalCost);
    feature3.addEventListener('change', updateTotalCost);
    startDate.addEventListener('change', updateTotalCost);
    endDate.addEventListener('change', updateTotalCost);

    // Form submission handler
    bookingForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Validate dates before submitting
        if (!validateDates()) {
            return;
        }
        
        // Disable submit button and show loading state
        const submitButton = bookingForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';
        
        // Submit the form
        bookingForm.submit();
    });

    // Initialize total cost when page loads
    updateTotalCost();
});

//-------------------Nuevo martes-------------------------//
// Reemplaza la parte del form submission handler con esto:
bookingForm.addEventListener('submit', function(event) {
    event.preventDefault();
    
    // Validate dates before submitting
    if (!validateDates()) {
        return;
    }
    
    // Disable submit button and show loading state
    const submitButton = bookingForm.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Processing...';
    
    // Prepare the data according to the API requirements
    const requestData = {
        user: document.getElementById('guestName').value,
        api_key: document.getElementById('transferCode').value,
        amount: parseInt(document.getElementById('totalCost').value)
    };

    // Make POST request to booking.php
    fetch('booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert('Booking successful!');
            window.location.href = 'index.php'; // Redirect on success
        } else if (data.error) {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error processing your booking. Please try again.');
    })
    .finally(() => {
        // Re-enable the submit button
        submitButton.disabled = false;
        submitButton.textContent = 'Book Now';
    });
});