<?php
// write.php - Generate blog post automatically using Gemini API

// API KEY kamu (ganti dengan API asli kamu)
$apikey = "aldgt";

// Daftar topik abadi yang selalu update
$topics = [
  "Teknologi terbaru dan dampaknya di Indonesia",
  "Isu politik terkini dan analisis kebijakannya",
  "Update harga crypto dan analisis pasar hari ini",
  "Tips hidup sehat dan pola makan seimbang",
  "Belajar skill digital baru untuk masa depan",
  "Ide bisnis modal kecil dan strategi memulainya",
  "Krisis iklim dan solusi yang bisa dilakukan individu",
  "Review gadget terbaru bulan ini",
  "Tren viral di media sosial dan pengaruhnya",
  "Opini tentang isu populer dari sudut pandang masyarakat"
];

// Pilih topik secara acak
$topik = $topics[array_rand($topics)];
$prompt = "Tulis artikel blog sepanjang 500 kata tentang $topik dalam bahasa Indonesia.";

// Kirim request ke API Gemini
$response = file_get_contents("https://aldgtapi.vercel.app/ai/gemini?apikey=$apikey&text=" . urlencode($prompt));
$data = json_decode($response, true);

if (!$data || !isset($data['result'])) {
  die("Gagal mendapatkan respons dari AI");
}

$judul = ucfirst(substr($topik, 0, 60));
$tanggal = date("Y-m-d H:i:s");
$isi = $data['result'];

// Siapkan data postingan
$post = [
  "judul" => $judul,
  "tanggal" => $tanggal,
  "konten" => $isi
];

// Simpan ke file JSON
$file = "posts.json";
$posts = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
array_unshift($posts, $post);
file_put_contents($file, json_encode($posts, JSON_PRETTY_PRINT));

echo "Post berhasil ditambahkan: $judul\n";
