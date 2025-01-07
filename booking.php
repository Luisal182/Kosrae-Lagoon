<?php
// Set up database connection
$pdo = new PDO('sqlite:Kosrae_lagoon.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Variables to store error messages
$errorMessages = [];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
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
            // Check if room is available during the selected dates
            $stmt = $pdo->prepare("SELECT * FROM Bookings WHERE RoomID = ? AND (CheckInDate < ? AND CheckOutDate > ?)");
            $stmt->execute([$roomID, $startDate, $endDate]);
            if ($stmt->rowCount() > 0) {
                $errorMessages[] = "The selected room is already booked during those dates.";
            }

            // If no errors, proceed with booking
            if (empty($errorMessages)) {
                // Insert the booking into the Bookings table
                $guestName = "Guest Name"; // Placeholder for guest name
                $stmt = $pdo->prepare("INSERT INTO Bookings (RoomID, GuestName, CheckInDate, CheckOutDate) VALUES (?, ?, ?, ?)");
                $stmt->execute([$roomID, $guestName, $startDate, $endDate]);

                // Insert features into RoomFeatures table
                foreach ($features as $featureName) {
                    $stmt = $pdo->prepare("SELECT id FROM Features WHERE FeatureName = ?");
                    $stmt->execute([$featureName]);
                    $feature = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($feature) {
                        $featureID = $feature['id'];
                        // Insert the feature into RoomFeatures table
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
        // Display errors in JSON format
        echo json_encode(["error" => $errorMessages]);
        exit();
    }
} else {
    // If the form hasn't been submitted, redirect to the reservation form
    header("Location: index.php");
    exit();
}
?>
