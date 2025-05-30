# Webserver for SZ Läuft Leaderboards
This repository contains the files served by the webserver running on the SZL Server on TCP port 80.

## Docker Image
The `nginx` container uses the [richarvey/nginx-php-fpm](https://hub.docker.com/r/richarvey/nginx-php-fpm) Docker image. It is recommended to use this specific image, as other images might lack the necessary Nginx, PHP, and PostgreSQL PHP plugin installations.

## Docker Container
This container serves the `leaderboard.php` file, allowing live statistics to be displayed locally during the event. The Docker container connects to the host system via a bind mount at `/home/admin/nginx/www/`, which enables file uploads to the webserver.

## `compose.yml`
```yaml
  nginx:
    restart: always
    container_name: nginx
    image: richarvey/nginx-php-fpm
    volumes:
      - ./nginx/www/:/var/www/html/
    ports:
      - "80:80"
```
This snippet is taken from the `compose.yml` file located in the admin user's home directory.

Here's a breakdown of the directives used:

<details>
  <summary>restart: always</summary>
  This tells Docker to always restart the `nginx` container if it stops. This is useful for ensuring services stay running, for example, after a server reboot or if the application crashes.
</details>

<details>
  <summary>container_name: nginx</summary>
  This explicitly sets the name of the container to `nginx`.
</details>

<details>
  <summary>image: richarvey/nginx-php-fpm</summary>
  This specifies the Docker image to use for this service. In this case, it's `richarvey/nginx-php-fpm`, which is an image that includes Nginx, PHP-FPM, and the PostgreSQL PHP plugin.
</details>

<details>
  <summary>volumes:</summary>
  This section defines volume mounts.
    <ul>
    <li><code>- ./nginx/www/:/var/www/html/</code>: This mounts a directory from the host machine into the container.
        <ul>
        <li><code>./nginx/www/</code>: This is the path on the host machine, relative to the <code>compose.yml</code> file's location.</li>
        <li><code>/var/www/html/</code>: This is the path inside the container where the host directory will be mounted.</li>
        </ul>
    </li>
    </ul>
</details>

<details>
  <summary>ports:</summary>
  This section maps ports between the host and the container.
    <ul>
    <li><code>- "80:80"</code>: This maps port 80 on the host machine to port 80 on the container.
        <ul>
        <li><code>80</code> (left side): The port on the host machine.</li>
        <li><code>80</code> (right side): The port the Nginx server is listening on inside the container. This allows you to access the web server running in the container by navigating to <code>http://localhost:80</code> (or just <code>http://localhost</code> as 80 is the default HTTP port) on your host machine.</li>
        </ul>
    </li>
    </ul>
</details>

## Files
<details>
<summary>index.php</summary>
This file contains the logic to select the top 10 users based on the best lap times and the highest number of laps. It then displays these users in a table.
</details>  
<details>
<summary>info.php</summary>
This file displays the phpinfo(); command.
</details>
<details>
<summary>normalize.css</summary>
This file, part of the included Skeleton CSS pack, normalizes CSS styling across different browsers.
</details>
<details>
<summary>skeleton.css</summary>
Also part of the Skeleton CSS pack, this file provides the framework's column system.
</details>
<details>
<summary>style.css</summary>
This file contains user-defined styles for the table's appearance.
</details>
      

### Leaderboard
```php
<!DOCTYPE html>
<html>

<head>
  <title>Leaderboard</title>
  <meta http-equiv="refresh" content="1">
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

  $result = pg_query($conn, "SELECT * from userinformation u join user_gifts g on u.uid = g.uid ORDER BY total_rounds DESC LIMIT 10");
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
      <div class="six columns table1">
        <table class="content-table ">
          <thead>
            <tr>
              <td>Vorname</td>
              <td>Nachname</td>
              <td>Klasse</td>
              <td>Bestzedit</td>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($top10laptime as $i => $participant) {
              echo "<tr><td>" . $participant["firstname"] . "</td><td>" . $participant["lastname"] . "</td><td>" . $participant["school_class"] . "</td><td>" . $participant["fastest_lap"] . "</td></tr>";
            } ?>
          </tbody>
        </table>
      </div>
      <div class="six columns table2">
        <table class="content-table ">
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
</html
```

<details>
<summary>Explanation of the Leaderboard Code</summary>

This code snippet generates a dynamic leaderboard page using HTML and PHP. Here's a breakdown of its functionality:

**HTML Structure and Styling:**
*   The line `<meta http-equiv="refresh" content="1">` causes the page to automatically refresh every 1 second, ensuring the leaderboard data is kept up-to-date.
*   It links three CSS files for styling:
    *   `normalize.css`: Resets default browser styles for consistency.
    *   `skeleton.css`: A CSS framework used for the grid layout.
    *   `style.css`: Contains custom styles for the leaderboard's appearance.

**PHP Backend Logic:**
1.  **Initialization:**
    *   Two empty arrays, `$top10laptime` and `$top10MostLaps`, are created to store the data for the two leaderboards.
2.  **Database Connection:**
    *   `$conn = pg_connect("host=100.102.196.30 port=5432 dbname=postgres user=admin password=Szl-20010901");` establishes a connection to the PostgreSQL database. The connection parameters (host, port, database name, username, and password) are hardcoded.
3.  **Fetching Top 10 Fastest Lap Times:**
    *   `$result = pg_query($conn, "SELECT * FROM userinformation ORDER BY fastest_lap ASC LIMIT 10");` executes a SQL query to retrieve the top 10 users from the `userinformation` table, ordered by their `fastest_lap` in ascending order.
    *   It then checks if any rows were returned (`pg_num_rows($result) > 0`).
    *   If results exist, it iterates through them using `while ($row = pg_fetch_assoc($result))`, adding each user's data as an associative array to the `$top10laptime` array.
    *   If no results are found, it outputs the message "Best times couldnt be loaded.".
4.  **Fetching Top 10 Most Laps:**
    *   `$result = pg_query($conn, "SELECT * from userinformation u join user_gifts g on u.uid = g.uid ORDER BY total_rounds DESC LIMIT 10");` executes another SQL query. This query joins the `userinformation` table (aliased as `u`) with a `user_gifts` table (aliased as `g`) on their common `uid` (user ID). It retrieves the top 10 users ordered by `total_rounds` in descending order (highest count first).
    *   Similar to the first query, it populates the `$top10MostLaps` array with the fetched data or outputs "Most Laps couldnt be loaded." if no results are found.
5.  **Closing Connection:**
    *   `pg_close($conn);` closes the database connection.

**Displaying the Leaderboards (HTML with embedded PHP):**
*   The main content is wrapped in a `div` with class `container`, and then a `div` with class `row`. These are part of the `skeleton.css` framework for creating a responsive grid.
*   **First Table (Fastest Lap Times):**
    *   A `div` with class `six columns table1` creates the left column for the first leaderboard.
    *   The table body (`<tbody>`) is populated by iterating through the `$top10laptime` array using a `foreach` loop. For each participant, a table row (`<tr>`) is generated displaying their `firstname`, `lastname`, `school_class`, and `fastest_lap`.
*   **Second Table (Most Laps):**
    *   A `div` with class `six columns table2` creates the right column for the second leaderboard.
    *   The table header (`<thead>`) includes columns: "Vorname", "Nachname", "Klasse", and "Runden" (Laps).
    *   The table body (`<tbody>`) is populated by iterating through the `$top10MostLaps` array. For each participant, a table row is generated displaying their `firstname`, `lastname`, `school_class`, and `total_rounds`.

In summary, this script connects to a PostgreSQL database to fetch leaderboard data and then displays this data in two side-by-side tables. The page is configured to auto-refresh every second, ensuring the displayed information is always current.
</details>
