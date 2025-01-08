<?php

$pdo = new PDO('sqlite:Kosrae_lagoon.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Variables to store error messages
$errorMessages = [];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startDate = $_POST['start-date'];
    $endDate = $_POST['end-date'];
    $roomID = $_POST['room'];  // Now using room ID directly
    $transferCode = $_POST['transferCode'];
    $features = [];

    // Get features from the form
    if (isset($_POST['feature1'])) {
        $features[] = 'Coffeemaker';
    }
    if (isset($_POST['feature2'])) {
        $features[] = 'TV';
    }
    if (isset($_POST['feature3'])) {
        $features[] = 'Gym';
    }

    // Basic validation for dates
    if (empty($startDate) || empty($endDate)) {
        $errorMessages[] = "Check-in and check-out dates are required.";
    } elseif ($startDate >= $endDate) {
        $errorMessages[] = "Check-out date must be later than check-in date.";
    }

    // Check if room is available
    if (empty($errorMessages)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM Bookings WHERE RoomID = ? AND (CheckInDate < ? AND CheckOutDate > ?)");
            $stmt->execute([$roomID, $startDate, $endDate]);
            if ($stmt->rowCount() > 0) {
                $errorMessages[] = "The selected room is already booked during those dates.";
            }

            // If no errors, proceed with booking
            if (empty($errorMessages)) {
                $guestName = "Guest Name";
                $stmt = $pdo->prepare("INSERT INTO Bookings (RoomID, GuestName, CheckInDate, CheckOutDate) VALUES (?, ?, ?, ?)");
                $stmt->execute([$roomID, $guestName, $startDate, $endDate]);

                // Insert features into RoomFeatures table
                foreach ($features as $featureName) {
                    $stmt = $pdo->prepare("SELECT id FROM Features WHERE FeatureName = ?");
                    $stmt->execute([$featureName]);
                    $feature = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($feature) {
                        $featureID = $feature['id'];
                        $stmt = $pdo->prepare("INSERT INTO RoomFeatures (RoomID, FeatureID) VALUES (?, ?)");
                        $stmt->execute([$roomID, $featureID]);
                    }
                }

                // Prepare JSON response for successful booking
                $response = [
                    "island" => "Kosrae Lagoon",
                    "hotel" => "Kosrae Lagoon Hotel",
                    "arrival_date" => $startDate,
                    "departure_date" => $endDate,
                    "room" => $roomID,
                    "features" => $features,
                    "message" => "Thank you for booking with us!",
                ];

                // Send the JSON response
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }
        } catch (PDOException $e) {
            $errorMessages[] = "Error processing your reservation. Please try again later.";
            echo json_encode(["error" => $errorMessages]);
            exit();
        }
    } else {
        echo json_encode(["error" => $errorMessages]);
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
