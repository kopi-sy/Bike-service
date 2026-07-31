
<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli("localhost", "root", "", "bike_service", 3306);

if ($conn->connect_error) {
    die("Database connection failed: " . htmlspecialchars($conn->connect_error));
}

$result = $conn->query("SELECT * FROM booking ORDER BY id DESC");

if (!$result) {
    die("Unable to fetch bookings: " . htmlspecialchars($conn->error));
}

echo "<h2>Bookings</h2>";
echo "<table border='1'>
<tr>
<th>ID</th><th>Name</th><th>Bike</th><th>Service</th><th>Mobile</th><th>Date</th>
</tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>
    <td>" . htmlspecialchars($row['id']) . "</td>
    <td>" . htmlspecialchars($row['name']) . "</td>
    <td>" . htmlspecialchars($row['bike']) . "</td>
    <td>" . htmlspecialchars($row['service']) . "</td>
    <td>" . htmlspecialchars($row['mobile']) . "</td>
    <td>" . htmlspecialchars($row['created_at']) . "</td>
    </tr>";
}

echo "</table>";

$conn->close();
?>