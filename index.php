<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

$database = new PDO('sqlite:Kosrae_lagoon.db');

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Kosrae Lagoon</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <!-- Header Section -->
    <header id="header-title">
        <nav>
            <h1>Kosrae Lagoon Hotel</h1>
        </nav>
        <div class="hero">
            <div class="press-hotel">
                <img src="images/hotel-night3.jpg" alt="micronesia">
                <h2>A wonderful place far away from civilization. Your paradise.</h2>
                <h3>&starf;&starf;&starf;</h3>
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
                    <form action="booking.php" method="post">
                        <div class="content-section">
                            <div class="form-row">
                                <!-- Check-in Date -->
                                <div class="section-form">
                                    <label for="start">Check-in Date: </label>
                                    <input type="date" id="start" name="start-date" min="2025-01-01" max="2025-01-31" value="2025-01-01">
                                </div>

                                <!-- Check-out Date -->
                                <div class="section-form">
                                    <label for="end">Check-out Date: </label>
                                    <input type="date" id="end" name="end-date" min="2025-01-02" max="2025-01-31" value="2025-01-02">
                                </div>
                            </div>

                            <div class="form-row">
                                <!-- Room Selection -->
                                <div class="section-form">
                                    <label for="standard">Room:</label>
                                    <select name="room" id="standard" onchange="updateTotalCost()">
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

                        <!-- NEW Additional Fields -->
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
                <ul class="booked-dates">
                    <li class="day-header">M</li>
                    <li class="day-header">T</li>
                    <li class="day-header">W</li>
                    <li class="day-header">T</li>
                    <li class="day-header">F</li>
                    <li class="day-header">S</li>
                    <li class="day-header">S</li>
                    <li class="booked">1</li>
                    <li class="booked">2</li>
                    <li class="booked">3</li>
                    <li>4</li>
                    <li>5</li>
                    <li>6</li>
                    <li>7</li>
                    <li>8</li>
                    <li class="booked">9</li>
                    <li class="booked">10</li>
                    <li class="booked">11</li>
                    <li>12</li>
                    <li>13</li>
                    <li>14</li>
                    <li>15</li>
                    <li class="booked">16</li>
                    <li class="booked">17</li>
                    <li>18</li>
                    <li>19</li>
                    <li>20</li>
                    <li>21</li>
                    <li>22</li>
                    <li class="booked">23</li>
                    <li class="booked">24</li>
                    <li class="booked">25</li>
                    <li class="booked">26</li>
                    <li>27</li>
                    <li>28</li>
                    <li>29</li>
                    <li>30</li>
                    <li>31</li>
                </ul>
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

                <ul class="booked-dates">
                    <li class="day-header">M</li>
                    <li class="day-header">T</li>
                    <li class="day-header">W</li>
                    <li class="day-header">T</li>
                    <li class="day-header">F</li>
                    <li class="day-header">S</li>
                    <li class="day-header">S</li>
                    <li>1</li>
                    <li class="booked">2</li>
                    <li class="booked">3</li>
                    <li>4</li>
                    <li>5</li>
                    <li>6</li>
                    <li>7</li>
                    <li>8</li>
                    <li>9</li>
                    <li>10</li>
                    <li>11</li>
                    <li>12</li>
                    <li>13</li>
                    <li class="booked">14</li>
                    <li class="booked">15</li>
                    <li class="booked">16</li>
                    <li class="booked">17</li>
                    <li>18</li>
                    <li>19</li>
                    <li>20</li>
                    <li>21</li>
                    <li>22</li>
                    <li class="booked">23</li>
                    <li>24</li>
                    <li>25</li>
                    <li>26</li>
                    <li>27</li>
                    <li>28</li>
                    <li>29</li>
                    <li>30</li>
                    <li>31</li>
                </ul>
            </article>

            <article class="room" id="premium">
                <div class="roomImg premiumImg">
                    <img src="images/hotel-luxury.jpg" alt="premium room">
                </div>
                <div class="roomInfo">
                    <h3>Elite & Escape (Sublime)</h3>
                    <p>Experience Premium & Majesty: Our luxurious sanctuary offers panoramic views, private spa, smart automation, and premium amenities for the ultimate digital detox retreat..</p>
                </div>

                <ul class="booked-dates">
                    <li class="day-header">M</li>
                    <li class="day-header">T</li>
                    <li class="day-header">W</li>
                    <li class="day-header">T</li>
                    <li class="day-header">F</li>
                    <li class="day-header">S</li>
                    <li class="day-header">S</li>
                    <li>1</li>
                    <li>2</li>
                    <li class="booked">3</li>
                    <li>4</li>
                    <li>5</li>
                    <li>6</li>
                    <li>7</li>
                    <li class="booked">8</li>
                    <li>9</li>
                    <li>10</li>
                    <li>11</li>
                    <li>12</li>
                    <li class="booked">13</li>
                    <li>14</li>
                    <li>15</li>
                    <li>16</li>
                    <li class="booked">17</li>
                    <li>18</li>
                    <li>19</li>
                    <li>20</li>
                    <li>21</li>
                    <li>22</li>
                    <li class="booked">23</li>
                    <li>24</li>
                    <li>25</li>
                    <li>26</li>
                    <li>27</li>
                    <li>28</li>
                    <li>29</li>
                    <li>30</li>
                    <li>31</li>
                </ul>
            </article>
        </section>
    </main>

    <!-- Footer Section -->
    <footer>
        <span>&copy; 2025 Kosrae Lagoon. All Rights Reserved.</span>
    </footer>

    <script src="script.js"></script>

</body>


</html>