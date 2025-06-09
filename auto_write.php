<?php
// Cek apakah sudah update hari ini
$today = date("Y-m-d");
$file = "posts.json";
$posts = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

foreach ($posts as $p) {
  if ($p["tanggal"] === $today) return; // Sudah ada, jangan tulis ulang
}

// Jika belum ada, tulis otomatis
$api_url = "https://aldgtapi.vercel.app/ai/gemini?apikey=aldgt&text=";

$topik = [
  "Teknologi", "Politik", "Kesehatan", "Pendidikan", "Ekonomi",
  "Olahraga", "Lingkungan", "Hiburan", "Sains", "Gaya Hidup"
];

$judul = $topik[array_rand($topik)] . " Hari Ini";
$prompt = "Tulis 1 artikel singkat dengan topik $judul, minimal 5 paragraf.";

$response = file_get_contents($api_url . urlencode($prompt));
$data = json_decode($response, true);
$konten = $data["result"] ?? "Gagal mendapatkan konten dari AI.";

// Tambahkan ke posts.json
array_unshift($posts, [
  "judul" => $judul,
  "tanggal" => $today,
  "konten" => $konten
]);

file_put_contents($file, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
