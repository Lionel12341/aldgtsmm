const fs = require("fs");
const path = require("path");

export default async function handler(req, res) {
  const filePath = path.join(process.cwd(), "public", "posts.json");
  const today = new Date().toISOString().slice(0, 10);

  let posts = [];
  if (fs.existsSync(filePath)) {
    posts = JSON.parse(fs.readFileSync(filePath, "utf-8"));
    if (posts.find(post => post.tanggal === today)) {
      return res.status(200).json({ message: "Sudah ada artikel hari ini" });
    }
  }

  const topik = [
    "Teknologi", "Politik", "Kesehatan", "Pendidikan", "Ekonomi",
    "Olahraga", "Lingkungan", "Hiburan", "Sains", "Gaya Hidup"
  ];
  const judul = `${topik[Math.floor(Math.random() * topik.length)]} Hari Ini`;
  const prompt = `Tulis 1 artikel dengan topik "${judul}", minimal 5 paragraf.`;

  const apiUrl = `https://aldgtapi.vercel.app/ai/gemini?apikey=aldgt&text=${encodeURIComponent(prompt)}`;
  const response = await fetch(apiUrl);
  const data = await response.json();

  const konten = data.result || "Gagal mengambil konten.";

  posts.unshift({ judul, tanggal: today, konten });
  fs.writeFileSync(filePath, JSON.stringify(posts.slice(0, 30), null, 2)); // Batasi 30 artikel

  res.status(200).json({ message: "Artikel ditambahkan", judul });
}
