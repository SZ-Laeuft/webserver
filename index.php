<!DOCTYPE html>
<html>

<head>
  <title>Leaderboard</title>
  <!-- <meta http-equiv="refresh" content="1"> -->
  <link rel="stylesheet" href="normalize.css">
  <link rel="stylesheet" href="skeleton.css">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <?php

  $top10laptime = [];
  $top10MostLaps = [];
  $conn = pg_connect("host=100.102.196.30 port=5432 dbname=postgres user=admin password=Szl-20010901");
  $result = pg_query($conn, "SELECT * FROM userinformation ORDER BY fastest_lap ASC LIMIT 10");

  $counter = 0;
  if (pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
      $counter++;
      $top10laptime[$counter] = $row;
    }
  } else {
    echo "Best times couldnt be loaded.";
  }

  $result = pg_query($conn, "select * from userinformation u join user_gifts g on u.uid = g.uid");
  $counter = 0;
  if (pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
      $counter++;
      $top10MostLaps[$counter] = $row;
    }
  } else {
    echo "Most Laps couldnt be loaded.";
  }
  pg_close($conn);
  ?>
  <div class="container">
    <div class="row">
      <div class="six columns">
        <table class="content-table">
          <thead>
            <tr>
              <td>Vorname</td>
              <td>Nachname</td>
              <td>Klasse</td>
              <td>Bestzeit</td>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($top10laptime as $i => $participant) {
              echo "<tr><td>" . $participant["firstname"] . "</td><td>" . $participant["lastname"] . "</td><td>" . $participant["school_class"] . "</td><td>" . $participant["fastest_lap"] . "</td></tr>";
            } ?>
          </tbody>
        </table>
      </div>
      <div class="six columns">
        <table class="content-table">
          <thead>
            <tr>
              <td>Vorname</td>
              <td>Nachname</td>
              <td>Klasse</td>
              <td>Runden</td>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($top10MostLaps as $i => $participant) {
              echo "<tr><td>" . $participant["firstname"] . "</td><td>" . $participant["lastname"] . "</td><td>" . $participant["school_class"] . "</td><td>" . $participant["total_rounds"] . "</td></tr>";
            } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>

</html>