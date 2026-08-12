<?php
require_once "config.php";

$errors = [];

// ---- 1. Receive & validate POST data ----
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$age  = isset($_POST['age']) ? (int)$_POST['age'] : null;

$childNames = isset($_POST['childName']) ? $_POST['childName'] : [];
$childAges  = isset($_POST['childAge']) ? $_POST['childAge'] : [];

if ($name === '') {
    $errors[] = "Name is required.";
}
if ($age === null || $age < 0) {
    $errors[] = "A valid age is required.";
}

$children = [];
for ($i = 0; $i < count($childNames); $i++) {
    $cName = trim($childNames[$i]) !== '' ? trim($childNames[$i]) : "Child " . ($i + 1);
    $cAge  = isset($childAges[$i]) ? (int)$childAges[$i] : null;

    if ($cAge === null || $cAge < 0) {
        $errors[] = "A valid age is required for " . htmlspecialchars($cName) . ".";
        continue;
    }
    $children[] = ["name" => $cName, "age" => $cAge];
}

if (!empty($errors)) {
    echo "<h2>There was a problem with your booking:</h2><ul>";
    foreach ($errors as $e) {
        echo "<li>" . htmlspecialchars($e) . "</li>";
    }
    echo "</ul><p><a href='index.html'>Go back</a></p>";
    exit;
}

// ---- 2. Insert booking via MySQLi (prepared statements = safe from SQL injection) ----
$stmt = mysqli_prepare($conn, "INSERT INTO bookings (name, age) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "si", $name, $age);
mysqli_stmt_execute($stmt);
$bookingId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

foreach ($children as $child) {
    $stmt = mysqli_prepare($conn, "INSERT INTO children (booking_id, name, age) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isi", $bookingId, $child["name"], $child["age"]);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// ---- 3. Retrieve all bookings (with their children) to display ----
$allBookings = [];
$result = mysqli_query($conn, "SELECT * FROM bookings ORDER BY booked_at DESC");
while ($row = mysqli_fetch_assoc($result)) {
    $bId = $row['id'];
    $childResult = mysqli_query($conn, "SELECT name, age FROM children WHERE booking_id = $bId");
    $rowChildren = [];
    while ($c = mysqli_fetch_assoc($childResult)) {
        $rowChildren[] = $c;
    }
    $row['children'] = $rowChildren;
    $allBookings[] = $row;
}

function popcornNotice($age) {
    return $age < 18;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Ticket</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #1a1a2e;
    color: #eee;
    display: flex;
    justify-content: center;
    padding: 40px 20px;
  }
  .card {
    background: #22223b;
    border-radius: 12px;
    padding: 30px;
    max-width: 520px;
    width: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,0.4);
  }
  h1, h2 { color: #ffb703; }
  h1 { text-align: center; font-size: 1.5em; }
  #ticket {
    background: #1a1a2e;
    border: 1px dashed #ffb703;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 25px;
  }
  .notice {
    margin-top: 10px;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 0.9em;
  }
  .popcorn { background: #2d6a4f; color: #d8f3dc; }
  .supervise { background: #6a040f; color: #ffccd5; }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 0.9em;
  }
  th, td {
    text-align: left;
    padding: 6px 8px;
    border-bottom: 1px solid #444;
  }
  a.button {
    display: block;
    text-align: center;
    margin-top: 20px;
    padding: 10px;
    background: #ffb703;
    color: #1a1a2e;
    font-weight: bold;
    border-radius: 8px;
    text-decoration: none;
  }
</style>
</head>
<body>

<div class="card">
  <h1>🎬 Movie Ticket Booking</h1>

  <div id="ticket">
    <h2>🎟️ Your Ticket</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
    <p><strong>Age:</strong> <?php echo $age; ?></p>

    <?php if (popcornNotice($age)): ?>
      <div class="notice popcorn">🍿 You're under 18 — enjoy free popcorn with your ticket!</div>
    <?php endif; ?>

    <?php if (!empty($children)): ?>
      <p><strong>Children under your care:</strong></p>
      <ul>
        <?php foreach ($children as $child): ?>
          <li>
            <?php echo htmlspecialchars($child['name']); ?> (age <?php echo $child['age']; ?>)
            <?php if (popcornNotice($child['age'])): ?> — 🍿 free popcorn!<?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php
        $anyChildUnder18 = false;
        foreach ($children as $child) {
            if (popcornNotice($child['age'])) $anyChildUnder18 = true;
        }
      ?>
      <?php if ($age >= 18 && $anyChildUnder18): ?>
        <div class="notice supervise">👀 Reminder: Please supervise the children in your care throughout the movie.</div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <h2>📋 All Bookings (from database)</h2>
  <table>
    <tr><th>Name</th><th>Age</th><th>Children</th><th>Booked At</th></tr>
    <?php foreach ($allBookings as $b): ?>
      <tr>
        <td><?php echo htmlspecialchars($b['name']); ?></td>
        <td><?php echo $b['age']; ?></td>
        <td>
          <?php
            if (empty($b['children'])) {
                echo "-";
            } else {
                $parts = [];
                foreach ($b['children'] as $c) {
                    $parts[] = htmlspecialchars($c['name']) . " (" . $c['age'] . ")";
                }
                echo implode(", ", $parts);
            }
          ?>
        </td>
        <td><?php echo $b['booked_at']; ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <a class="button" href="index.html">Book Another Ticket</a>
</div>

</body>
</html>
