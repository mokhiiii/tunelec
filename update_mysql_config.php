<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tunelec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check current max_allowed_packet
    $stmt = $conn->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current max_allowed_packet: " . $result['Value'] . "<br>";

    // Set new max_allowed_packet (64M)
    $conn->exec("SET GLOBAL max_allowed_packet=67108864");

    // Verify the new value
    $stmt = $conn->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "New max_allowed_packet: " . $result['Value'] . "<br>";

    echo "Successfully updated max_allowed_packet size.<br>";
    echo "<a href='manage_images.html'>Return to image upload page</a>";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
