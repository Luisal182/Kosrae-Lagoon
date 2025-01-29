<?php

declare(strict_types=1);
require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/calendar.php'; // Include the calendar function

use GuzzleHttp\Client;

$database = new PDO('sqlite:Kosrae_lagoon.db');

$bookedDaysBudget = [1, 3, 9, 10, 11, 25, 26];
$bookedDaysLuxury = [2, 14, 15, 16, 17, 23];
$bookedDaysPremium = [3, 8, 13, 17, 24];

?>

<!-- Header Section -->
<header id="header-title">
    <nav>
        <h1>Kosrae Lagoon Hotel</h1>
    </nav>
    <div class="hero">
        <div class="press-hotel">
            <img src="images/hotel-night3.jpg" alt="micronesia">
            <h2>A wonderful place far away from civilization. Your paradise.</h2>
        </div>
    </div>
</header>

<!-- Main Content Section -->
<main>
    <!-- Booking Form Section -->
    <section class="booking">
        <h2>Book your Nirvana here</h2>
        <div class="form">
            <div class="booking-form">
                <form id="bookingForm" action="booking.php" method="post">
                    <div class="content-section">
                        <div class="form-row">
                            <!-- Check-in Date -->
                            <div class="section-form">
                                <label for="start-date">Check-in Date: </label>
                                <input type="date" id="start-date" name="start-date" min="2025-01-01" max="2025-01-31" value="2025-01-01">
                            </div>

                            <!-- Check-out Date -->
                            <div class="section-form">
                                <label for="end-date">Check-out Date: </label>
                                <input type="date" id="end-date" name="end-date" min="2025-01-02" max="2025-01-31" value="2025-01-02">
                            </div>
                        </div>

                        <div class="form-row">
                            <!-- Room Selection -->
                            <div class="section-form">
                                <label for="room">Room:</label>
                                <select name="room" id="room" onchange="updateTotalCost()">
                                    <option value="Budget" data-price="1">Code and Rest (Simple) - 1</option>
                                    <option value="Standard" data-price="2">Syntax & Serenity (Medium) - 2</option>
                                    <option value="Luxury" data-price="4">Elite & Escape (Sublime) - 4</option>
                                </select>
                            </div>

                            <!-- Transfer Code -->
                            <div class="section-form">
                                <label for="transferCode">Transfer Code: </label>
                                <input name="transferCode" type="text" id="transferCode" required>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Fields -->
                    <div class="content-section">
                        <!-- Guest Name -->
                        <div class="section-form">
                            <label for="guestName">Guest Name: </label>
                            <input name="guestName" type="text" id="guestName" required>
                        </div>

                        <!-- Features (Checkboxes for Minibar, TV-satellite, Gym) -->
                        <div class="section-form">
                            <label>Features:</label>
                            <div class="feature">
                                <input type="checkbox" name="feature1" id="feature1" value="Minibar" onchange="updateTotalCost()">
                                <label for="feature1">Minibar (1)</label>
                            </div>
                            <div class="feature">
                                <input type="checkbox" name="feature2" id="feature2" value="TV-satellite" onchange="updateTotalCost()">
                                <label for="feature2">TV-satellite (1)</label>
                            </div>
                            <div class="feature">
                                <input type="checkbox" name="feature3" id="feature3" value="Gym" onchange="updateTotalCost()">
                                <label for="feature3">Gym (1)</label>
                            </div>
                        </div>

                        <!-- Total Cost -->
                        <div class="section-form">
                            <label for="totalCost">Total Cost: </label>
                            <input name="totalCost" type="number" id="totalCost" value="1" min="1" required readonly>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="section-form">
                        <button type="submit">Book Now</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Rooms Section -->
    <section class="rooms">
        <h2>Our Rooms</h2>

        <article class="room" id="budget">
            <!-- Room Image and Content -->
            <div class="roomImg budgetImg">
                <img src="images/hotel-bugget.jpg" alt="budget room">
            </div>
            <div class="roomInfo">
                <h3>Code and Rest (Simple)</h3>
                <p>Experience our Compile & Sleep room: A cozy haven for developers, featuring fast Wi-Fi, ergonomic workspace, and comfortable bed for post-coding rest.</p>
            </div>

            <!-- Booking Calendar -->
            <?php echo generateCalendar($bookedDaysBudget); ?>
        </article>

        <!-- Repeat for other rooms -->
        <article class="room" id="luxury">
            <div class="roomImg luxuryImg">
                <img src="images/hotel-medium.avif" alt="luxury room">
            </div>
            <div class="roomInfo">
                <h3>Syntax & Serenity (Medium)</h3>
                <p>Discover Buffer & Balance: A harmonious blend of modern tech and relaxation. Premium workspace, rainfall shower, and deluxe comfort for the discerning professional.</p>
            </div>

            <!-- Booking Calendar -->
            <?php echo generateCalendar($bookedDaysLuxury); ?>
        </article>

        <article class="room" id="premium">
            <div class="roomImg premiumImg">
                <img src="images/hotel-luxury.jpg" alt="premium room">
            </div>
            <div class="roomInfo">
                <h3>Elite & Escape (Sublime)</h3>
                <p>Experience Premium & Majesty: Our luxurious sanctuary offers panoramic views, private spa, smart automation, and premium amenities for the ultimate digital detox retreat..</p>
            </div>

            <!-- Booking Calendar -->
            <?php echo generateCalendar($bookedDaysPremium); ?>
        </article>
    </section>
</main>

<!-- Include your script.js file -->
<script src="script.js"></script>

<?php
require_once(__DIR__ . '/footer.php');
?>