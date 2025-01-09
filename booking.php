<?php
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Exception\ClientException;

// Database connection (ensure you have a valid SQLite or other DB connection)
try {
    $database = new PDO('sqlite:Kosrae_lagoon.db');
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

// Collect form data safely using isset to avoid warnings
$room = isset($_POST['room']) ? $_POST['room'] : '';
$start_date = isset($_POST['start-date']) ? $_POST['start-date'] : '';
$end_date = isset($_POST['end-date']) ? $_POST['end-date'] : '';
$guestName = isset($_POST['guestName']) ? $_POST['guestName'] : '';
$transferCode = isset($_POST['transferCode']) ? $_POST['transferCode'] : '';
$totalCost = isset($_POST['totalCost']) ? $_POST['totalCost'] : 0;

// Validate that all necessary fields are present
if (empty($room) || empty($start_date) || empty($end_date) || empty($guestName) || empty($transferCode) || $totalCost <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required, and total cost must be greater than 0.'
    ]);
    exit();
}

// Create the booking data array
$bookingData = [
    'transfercode' => $transferCode,
    'totalcost' => $totalCost
];

// Step 1: Check transfer code validity
$transferCodeResponse = checkTransferCode($bookingData);

// If the transfer code is invalid, show an error
if (isset($transferCodeResponse['status']) && $transferCodeResponse['status'] !== 'success') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Transfer code validation failed: ' . (isset($transferCodeResponse['message']) ? $transferCodeResponse['message'] : 'Unknown error')
    ]);
    exit();
}

// Step 2: Process the payment
$paymentSuccess = processPayment($transferCode, $guestName);

// If payment fails, show an error
if (!$paymentSuccess) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Payment failed. Please try again.'
    ]);
    exit();
}

// Step 3: Insert the booking into the database
try {
    $roomIdMap = [
        'Budget' => ['id' => 1, 'name' => 'Code and Rest (Simple)', 'cost' => 1],
        'Standard' => ['id' => 2, 'name' => 'Syntax & Serenity (Medium)', 'cost' => 2],
        'Luxury' => ['id' => 3, 'name' => 'Elite & Escape (Sublime)', 'cost' => 4]
    ];

    // Check if the selected room is valid
    if (!isset($roomIdMap[$room])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid room selection.'
        ]);
        exit();
    }

    // Room data from the map
    $roomData = $roomIdMap[$room];
    $roomId = $roomData['id'];

    // Insert the booking data into the database
    $stmt = $database->prepare("INSERT INTO Bookings (RoomID, GuestName, CheckInDate, CheckOutDate, TotalCost, TransferCode) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$roomId, $guestName, $start_date, $end_date, $totalCost, $transferCode]);

    // Create the JSON response
    $response = [
        'status' => 'success',
        'island' => 'Main island',
        'hotel' => 'Centralhotellet',  // Hotel name
        'arrival_date' => $start_date,
        'departure_date' => $end_date,
        'total_cost' => $totalCost,
        'stars' => $roomData['cost'], // Mapping room cost to star rating
        'features' => [],
        'additional_info' => [
            'greeting' => 'Thank you for choosing Centralhotellet!',
            'imageUrl' => 'https://upload.wikimedia.org/wikipedia/commons/e/e2/Hotel_Boscolo_Exedra_Nice.jpg'
        ]
    ];

    // Return the response as JSON
    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Booking failed: ' . $e->getMessage()
    ]);
}
