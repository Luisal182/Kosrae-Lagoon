<?php

declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
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

// Function to generate transfer code (external API)
function generateTransferCode($apiKey, $amount)
{
    $client = new Client(['verify' => false]);

    try {
        $response = $client->request('POST', 'https://www.yrgopelago.se/centralbank/withdraw', [
            'form_params' => [
                'apiKey' => $apiKey,
                'amount' => $amount
            ]
        ]);

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        return isset($data['transferCode']) ? $data['transferCode'] : null;
    } catch (ClientException $e) {
        return null; // Handle error
    }
}

// Function to check room booking availability
function checkBookingAvailability($roomId, $startDate, $endDate, $database)
{
    $query = 'SELECT * FROM Bookings WHERE RoomID = :roomId AND ((CheckInDate <= :endDate AND CheckOutDate >= :startDate))';
    $stmt = $database->prepare($query);
    $stmt->execute(['roomId' => $roomId, 'startDate' => $startDate, 'endDate' => $endDate]);

    return $stmt->rowCount() == 0; // If no bookings, dates are available
}

// Function to check transfer code validity
function checkTransferCode($transferCode, $totalCost)
{
    $client = new Client(['verify' => false]);

    try {
        $response = $client->request('POST', 'https://www.yrgopelago.se/centralbank/transferCode', [
            'form_params' => [
                'transferCode' => $transferCode,
                'totalCost' => $totalCost
            ]
        ]);

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // Check the response for a valid status
        if (isset($data['status']) && $data['status'] == 'success') {
            return true;
        }
    } catch (ClientException $e) {
        // Handle error gracefully
        error_log("Error validating transfer code: " . $e->getMessage());
        return false;
    }

    return false;
}

// Function to process the booking and payment
function processBooking($transferCode, $roomId, $guestName, $startDate, $endDate, $totalCost, $database)
{
    try {
        // Step 1: Insert booking into database
        $stmt = $database->prepare("INSERT INTO Bookings (RoomID, GuestName, CheckInDate, CheckOutDate, TotalCost, TransferCode) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$roomId, $guestName, $startDate, $endDate, $totalCost, $transferCode]);

        // Step 2: Process payment
        $client = new Client(['verify' => false]);
        $response = $client->request('POST', 'https://www.yrgopelago.se/centralbank/deposit', [
            'form_params' => [
                'transferCode' => $transferCode,
                'totalCost' => $totalCost
            ]
        ]);

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        if (isset($data['status']) && $data['status'] == 'success') {
            // Payment success - return booking details
            $bookingDetails = [
                'roomId' => $roomId,
                'guestName' => $guestName,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalCost' => $totalCost,
                'transferCode' => $transferCode
            ];

            return $bookingDetails; // Return booking details
        } else {
            return null; // Payment failed
        }
    } catch (Exception $e) {
        return null; // DB or payment failure
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $room = $_POST['room'] ?? '';
    $start_date = $_POST['start-date'] ?? '';
    $end_date = $_POST['end-date'] ?? '';
    $guestName = $_POST['guestName'] ?? '';
    $transferCode = $_POST['transferCode'] ?? '';
    $totalCost = $_POST['totalCost'] ?? 0;

    // --------------------------------- Debugging and trim transfer code
    $transferCode = trim($transferCode); // Trim any leading/trailing spaces
    error_log('Trimmed transferCode: ' . $transferCode);

    // --------------------------------- Validación de Fechas
    $startDateObj = DateTime::createFromFormat('Y-m-d', $start_date);
    $endDateObj = DateTime::createFromFormat('Y-m-d', $end_date);

    if (!$startDateObj || !$endDateObj) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid date format. Please use YYYY-MM-DD.']);
        exit();
    }

    if ($startDateObj > $endDateObj) {
        echo json_encode(['status' => 'error', 'message' => 'The start date cannot be later than the end date.']);
        exit();
    }

    // Collect selected features
    $selectedFeatures = [];
    if (isset($_POST['feature1'])) $selectedFeatures[] = ['name' => 'Minibar', 'cost' => 1];
    if (isset($_POST['feature2'])) $selectedFeatures[] = ['name' => 'TV-satellite', 'cost' => 1];
    if (isset($_POST['feature3'])) $selectedFeatures[] = ['name' => 'Gym', 'cost' => 1];

    // Validate required fields
    if (empty($room) || empty($start_date) || empty($end_date) || empty($guestName) || empty($transferCode)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit();
    }

    // Room ID map
    $roomIdMap = [
        'Budget' => ['id' => 1, 'name' => 'Code and Rest (Simple)', 'cost' => 1],
        'Standard' => ['id' => 2, 'name' => 'Syntax & Serenity (Medium)', 'cost' => 2],
        'Luxury' => ['id' => 3, 'name' => 'Elite & Escape (Sublime)', 'cost' => 4]
    ];

    // --------------------------------- Debugging the transfer code
    error_log('Received transferCode: ' . $transferCode);

    // Validate selected room
    if (!isset($roomIdMap[$room])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid room selection.']);
        exit();
    }

    // Check room availability
    $roomData = $roomIdMap[$room];
    $roomId = $roomData['id'];
    if (!checkBookingAvailability($roomId, $start_date, $end_date, $database)) {
        echo json_encode(['status' => 'error', 'message' => 'The selected dates are already booked.']);
        exit();
    }

    // Validate transfer code
    if (!checkTransferCode($transferCode, $totalCost)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid transfer code.']);
        exit();
    }

    // Process the booking
    $bookingDetails = processBooking($transferCode, $roomId, $guestName, $start_date, $end_date, $totalCost, $database);
    if (!$bookingDetails) {
        echo json_encode(['status' => 'error', 'message' => 'Payment failed or an error occurred. Please try again.']);
        exit();
    }

    // Successful booking response with all required data
    $response = [
        'status' => 'success',
        'island' => 'Kosrae', // Hardcoded for your case, change if needed
        'hotel' => 'Kosrae Lagoon',
        'arrival_date' => $start_date,
        'departure_date' => $end_date,
        'total_cost' => $totalCost,
        'stars' => 4, // Can be dynamic based on your hotel’s rating
        'features' => $selectedFeatures,
        'additional_info' => [
            'greeting' => 'Thank you for choosing Kosrae Lagoon!',
            'imageUrl' => 'https://example.com/images/kosrae-hotel.jpg'
        ]
    ];

    // Send the JSON response
    echo json_encode($response);
    exit();
}
