<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blog Otomatis AI</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background: linear-gradient(to right, #2196f3, #e3f2fd);
      color: #333;
    }
    header {
      background-color: #1565c0;
      color: white;
      padding: 20px;
      text-align: center;
    }
    .container {
      max-width: 800px;
      margin: 20px auto;
      padding: 10px;
    }
    .card {
      background: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .card h2 {
      color: #1565c0;
      margin: 0 0 10px;
    }
    .card p {
      line-height: 1.6;
      white-space: pre-line;
    }
    .date {
      font-size: 0.9em;
      color: #777;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <header>
    <h1>Blog Otomatis AI</h1>
    <p>Diperbarui otomatis setiap hari oleh Gemini AI</p>
  </header>
  <div class="container">
    <?php
    $file = "posts.json";
    if (file_exists($file)) {
      $posts = json_decode(file_get_contents($file), true);
      if (is_array($posts) && count($posts) > 0) {
        foreach ($posts as $post) {
          echo "<div class='card'>";
          echo "<h2>" . htmlspecialchars($post['judul']) . "</h2>";
          echo "<div class='date'>" . htmlspecialchars($post['tanggal']) . "</div>";
          echo "<p>" . htmlspecialchars($post['konten']) . "</p>";
          echo "</div>";
        }
      } else {
        echo "<p>Tidak ada postingan ditemukan.</p>";
      }
    } else {
      echo "<p>File posts.json tidak ditemukan.</p>";
    }
    ?>
  </div>
</body>
</html>
