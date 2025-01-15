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
    
    // Validate that all elements exist
    if (!roomSelect || !feature1 || !feature2 || !feature3 || !startDate || 
        !endDate || !totalCostInput || !bookingForm) {
        console.error('Required form elements not found');
        return;
    }

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
        if (!startDate.value || !endDate.value) {
            alert('Please select both check-in and check-out dates.');
            return false;
        }

        const startDateValue = new Date(startDate.value);
        const endDateValue = new Date(endDate.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Check if start date is in the past
        if (startDateValue < today) {
            alert('The check-in date cannot be in the past.');
            return false;
        }
        
        // Check if end date is after start date
        if (endDateValue <= startDateValue) {
            alert('The check-out date must be after the check-in date.');
            return false;
        }

        // Get selected room type to check availability
        const selectedRoom = roomSelect.value;
        const roomCalendar = document.querySelector(`#${selectedRoom.toLowerCase()} .booked-dates`);
        
        if (!roomCalendar) {
            console.log('Room calendar not found for room:', selectedRoom);
            // Return true if we can't find the calendar - this allows booking to proceed
            return true;
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
        
        // Validate required fields
        const guestName = document.getElementById('guestName');
        const transferCode = document.getElementById('transferCode');
        
        if (!guestName || !guestName.value.trim()) {
            alert('Please enter your name.');
            return;
        }
        
        if (!transferCode || !transferCode.value.trim()) {
            alert('Please enter a transfer code.');
            return;
        }

        // Disable submit button and show loading state
        const submitButton = bookingForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Processing...';
        
        // Create FormData object
        const formData = new FormData(bookingForm);
        
        // Make POST request to booking.php
        fetch('booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Booking successful!');
                window.location.href = 'index.php';
            } else {
                alert(data.message || 'Booking failed. Please try again.');
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

    // Initialize total cost when page loads
    updateTotalCost();
    
    // Set minimum dates for date inputs
    const today = new Date().toISOString().split('T')[0];
    startDate.min = today;
    endDate.min = today;
});