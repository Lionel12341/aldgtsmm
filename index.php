<?php include_once "auto_write.php"; ?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blog Otomatis AI</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      background: #f1f1f1;
      color: #333;
    }
    header {
      background-color: #1565c0;
      color: #fff;
      padding: 30px 20px;
      text-align: center;
    }
    .container {
      max-width: 900px;
      margin: 20px auto;
      padding: 20px;
    }
    .card {
      background: #fff;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 25px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .card h2 {
      margin-top: 0;
      color: #1565c0;
    }
    .card .date {
      font-size: 0.9em;
      color: #777;
      margin-bottom: 15px;
    }
    .card p {
      line-height: 1.7;
      white-space: pre-line;
    }
  </style>
</head>
<body>
  <header>
    <h1>Blog Otomatis AI</h1>
    <p>Ditulis otomatis setiap hari oleh Gemini AI</p>
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
            echo "<p>" . nl2br(htmlspecialchars($post['konten'])) . "</p>";
            echo "</div>";
          }
        } else {
          echo "<p>Belum ada artikel tersedia.</p>";
        }
      } else {
        echo "<p>File posts.json tidak ditemukan.</p>";
      }
    ?>
  </div>
</body>
</html>
