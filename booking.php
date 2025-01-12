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
        return null;
    }
}

function checkBookingAvailability($roomId, $startDate, $endDate, $database)
{
    $query = 'SELECT * FROM Bookings WHERE RoomID = :roomId AND ((CheckInDate <= :endDate AND CheckOutDate >= :startDate))';
    $stmt = $database->prepare($query);
    $stmt->execute(['roomId' => $roomId, 'startDate' => $startDate, 'endDate' => $endDate]);

    return $stmt->rowCount() == 0; // If no bookings, dates are available
}

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

        return isset($data['status']) && $data['status'] == 'success'; // Verifies transfer code
    } catch (ClientException $e) {
        return false;
    }
}

function processBooking($transferCode, $roomId, $guestName, $startDate, $endDate, $totalCost, $database)
{
    // Step 1: Insert booking into database
    try {
        $stmt = $database->prepare("INSERT INTO Bookings (RoomID, GuestName, CheckInDate, CheckOutDate, TotalCost, TransferCode) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$roomId, $guestName, $startDate, $endDate, $totalCost, $transferCode]);

        // Step 2: Process payment and generate JSON receipt
        $client = new Client(['verify' => false]);
        $response = $client->request('POST', 'https://www.yrgopelago.se/centralbank/deposit', [
            'form_params' => [
                'transferCode' => $transferCode,
                'totalCost' => $totalCost
            ]
        ]);

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // Check if payment was successful
        if (isset($data['status']) && $data['status'] == 'success') {
            // Generate a JSON receipt
            $bookingDetails = [
                'roomId' => $roomId,
                'guestName' => $guestName,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'totalCost' => $totalCost,
                'transferCode' => $transferCode
            ];
            file_put_contents('booking_receipt.json', json_encode($bookingDetails));

            // Redirect to the index page after successful booking
            header('Location: index.php');
            exit();
        } else {
            return false; // Payment failed
        }
    } catch (Exception $e) {
        return false; // DB or payment failure
    }
}

// If the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Collect form data safely using isset to avoid warnings
    $room = isset($_POST['room']) ? $_POST['room'] : '';
    $start_date = isset($_POST['start-date']) ? $_POST['start-date'] : '';
    $end_date = isset($_POST['end-date']) ? $_POST['end-date'] : '';
    $guestName = isset($_POST['guestName']) ? $_POST['guestName'] : '';
    $transferCode = isset($_POST['transferCode']) ? $_POST['transferCode'] : '';
    $totalCost = isset($_POST['totalCost']) ? $_POST['totalCost'] : 0;

    // Collect selected features (Minibar, TV-satellite, Gym)
    $selectedFeatures = [];
    if (isset($_POST['feature1'])) {
        $selectedFeatures[] = 'Minibar';
    }
    if (isset($_POST['feature2'])) {
        $selectedFeatures[] = 'TV-satellite';
    }
    if (isset($_POST['feature3'])) {
        $selectedFeatures[] = 'Gym';
    }

    // Validate that all necessary fields are present
    if (empty($room) || empty($start_date) || empty($end_date) || empty($guestName) || empty($transferCode)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.'
        ]);
        exit();
    }

    // Room data map (base cost per room)
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

    // Step 1: Check availability of dates
    if (!checkBookingAvailability($roomId, $start_date, $end_date, $database)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'The selected dates are already booked.'
        ]);
        exit();
    }

    // Step 2: Validate transfer code
    if (!checkTransferCode($transferCode, $totalCost)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid or incorrect transfer code.'
        ]);
        exit();
    }

    // Step 3: Process booking and payment
    $paymentSuccess = processBooking($transferCode, $roomId, $guestName, $start_date, $end_date, $totalCost, $database);

    if (!$paymentSuccess) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment failed or an error occurred. Please try again.'
        ]);
        exit();
    }

    // Step 4: Return success response
    $response = [
        'status' => 'success',
        'island' => 'Main island',
        'hotel' => 'Centralhotellet',
        'arrival_date' => $start_date,
        'departure_date' => $end_date,
        'total_cost' => $totalCost,
        'stars' => $roomData['cost'],
        'features' => $selectedFeatures,
        'additional_info' => [
            'greeting' => 'Thank you for choosing Centralhotellet!',
            'imageUrl' => 'https://upload.wikimedia.org/wikipedia/commons/e/e2/Hotel_Boscolo_Exedra_Nice.jpg'
        ]
    ];

    // Return the response as JSON
    echo json_encode($response);
}
