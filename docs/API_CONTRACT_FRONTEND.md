# Panduan Kontrak API & Integrasi Frontend (SMA Widya Darma)

Dokumen ini adalah panduan integrasi resmi backend REST API SMA Widya Darma untuk tim pengembang Frontend (React/Tailwind).

---

## 1. Informasi Dasar (General Info)

* **Base URL Lokal**: `http://localhost:8000/api`
* **Format Data**: JSON (`Content-Type: application/json`, `Accept: application/json`)
* **CORS**: Sudah aktif untuk seluruh origin lokal (`http://localhost:5173`, `http://localhost:3000`, dll.).
* **Autentikasi Pengunjung (Chatbot)**: Anonim menggunakan `visitor_token` (disimpan di `localStorage`).
* **Autentikasi Admin**: Bearer Token (`Authorization: Bearer <TOKEN>`).

---

## 2. Modul AI Chatbot (Public)

### A. Mengirim Pertanyaan ke Chatbot
* **Endpoint**: `POST /chat/send`
* **Rate Limit**: Maksimal 60 request/menit per pengunjung.
* **Request Body**:
```json
{
  "visitor_token": "user_unique_token_12345",
  "message": "Apa saja syarat pendaftaran SPMB di SMA Widya Darma?"
}
```

* **Response Sukses (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "session_id": 1,
    "visitor_token": "user_unique_token_12345",
    "user_message": "Apa saja syarat pendaftaran SPMB di SMA Widya Darma?",
    "bot_response": "Syarat pendaftaran SPMB di SMA Widya Darma meliputi fotokopi ijazah/SKL SMP, kartu keluarga, akta kelahiran, dan pas foto terbaru. Informasi selengkapnya dapat dilihat pada menu SPMB.",
    "kb_id": 3,
    "topic": "spmb",
    "is_answered": true,
    "tokens_used": 68,
    "created_at": "2026-11-15T10:00:00.000000Z"
  }
}
```

* **Response Batas Kecepatan Terlampaui (`429 Too Many Requests`)**:
```json
{
  "success": false,
  "message": "Terlalu banyak permintaan chat dalam waktu singkat. Silakan tunggu sebentar sebelum mengirim pesan kembali."
}
```

* **Response Validasi Gagal (`422 Unprocessable Entity`)**:
```json
{
  "message": "Pesan pertanyaan tidak boleh kosong.",
  "errors": {
    "message": ["Pesan pertanyaan tidak boleh kosong."]
  }
}
```

---

### B. Mengambil Riwayat Chat Pengunjung
Dipanggil saat widget chat dibuka pertama kali untuk memuat pesan lama pengunjung.
* **Endpoint**: `GET /chat/history/{visitor_token}`
* **Response Sukses (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "session_id": 1,
    "visitor_token": "user_unique_token_12345",
    "started_at": "2026-11-15T09:30:00.000000Z",
    "ended_at": null,
    "messages": [
      {
        "message_id": 1,
        "session_id": 1,
        "sender": "user",
        "message": "Halo selamat pagi",
        "topic": null,
        "is_answered": false,
        "tokens_used": 0,
        "created_at": "2026-11-15T09:30:00.000000Z"
      },
      {
        "message_id": 2,
        "session_id": 1,
        "sender": "bot",
        "message": "Halo! Selamat datang di Pusat Layanan Informasi SMA Widya Darma...",
        "topic": "greeting",
        "is_answered": true,
        "tokens_used": 0,
        "created_at": "2026-11-15T09:30:01.000000Z"
      }
    ]
  }
}
```

---

## 3. Modul Konten Publik (Website Utama)

| Method | Endpoint | Keterangan | Query Params / Catatan |
| :--- | :--- | :--- | :--- |
| `GET` | `/profile` | Profil resmi sekolah, visi-misi, kontak, medsos | - |
| `GET` | `/programs` | Daftar program keahlian / kegiatan | - |
| `GET` | `/programs/{id}` | Detail program | - |
| `GET` | `/extracurriculars` | Daftar seluruh ekstrakurikuler | - |
| `GET` | `/extracurriculars/{id}` | Detail ekstrakurikuler | - |
| `GET` | `/spmb` | Daftar periode pendaftaran aktif | - |
| `GET` | `/spmb/{id}` | Detail persyaratan & jadwal SPMB | - |
| `GET` | `/news` | Berita yang sudah terbit (*published*) | `?page=1&per_page=10` |
| `GET` | `/news/{slug}` | Detail berita (otomatis mencatat view) | Contoh: `/news/kegiatan-orientasi-2026` |
| `GET` | `/galleries` | Galeri foto kegiatan | `?page=1&per_page=12&news_id={id}` |

---

## 4. Modul Admin CMS & Dashboard

### A. Autentikasi Admin
* **Login**: `POST /admin/login`
  - Body: `{ "email": "admin@widyadarma.sch.id", "password": "password123" }`
  - Respons: `{ "success": true, "data": { "token": "...", "admin": {...} } }`
* **Logout**: `POST /admin/logout` (Header: `Authorization: Bearer <TOKEN>`)
* **Profil Saya**: `GET /admin/me` (Header: `Authorization: Bearer <TOKEN>`)

---

### B. Dashboard Ringkasan & Analitik Chatbot (Baru)

#### 1. Ringkasan Status Konten (`GET /admin/dashboard/stats`)
Header wajib: `Authorization: Bearer <TOKEN>`
* **Response (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "news": {
      "total": 12,
      "published": 10,
      "draft": 2
    },
    "programs": { "total": 4 },
    "extracurriculars": { "total": 8 },
    "galleries": { "total": 24 },
    "knowledge_base": {
      "total": 5,
      "active": 5
    },
    "spmb": {
      "is_active": true,
      "active_academic_year": "2026/2027"
    },
    "website_traffic": {
      "total_page_views": 1420
    }
  }
}
```

#### 2. Statistik Interaksi Chatbot (`GET /admin/dashboard/chatbot-stats`)
Header wajib: `Authorization: Bearer <TOKEN>`
* **Response (`200 OK`)**:
```json
{
  "success": true,
  "data": {
    "summary": {
      "total_sessions": 85,
      "total_user_questions": 140,
      "total_bot_responses": 140,
      "answered_count": 128,
      "unanswered_count": 12,
      "answered_rate_percentage": 91.4,
      "total_tokens_used": 8950
    },
    "popular_topics": [
      { "topic": "spmb", "count": 62 },
      { "topic": "ekskul", "count": 34 },
      { "topic": "profil", "count": 20 }
    ],
    "recent_unanswered_questions": [
      {
        "question": "Apakah ada asrama siswa?",
        "bot_response": "Mohon maaf, saya belum menemukan informasi resmi mengenai hal tersebut...",
        "topic": "unknown",
        "created_at": "2026-11-15T11:00:00.000000Z"
      }
    ]
  }
}
```

---

### C. Kelola Knowledge Base (Admin CRUD)
Header wajib: `Authorization: Bearer <TOKEN>`
* `GET /admin/knowledge-bases`: Ambil semua artikel KB (`?category=spmb&is_active=1&page=1`)
* `POST /admin/knowledge-bases`: Tambah KB baru
  - Body: `{ "category": "spmb", "title": "...", "content": "...", "is_active": true }`
* `GET /admin/knowledge-bases/{id}`: Detail satu artikel KB
* `PUT /admin/knowledge-bases/{id}`: Perbarui artikel KB
* `DELETE /admin/knowledge-bases/{id}`: Hapus artikel KB

---

## 5. Cuplikan Kode Siap Pakai untuk Frontend (React)

Berikut contoh fungsi *helper* sederhana yang bisa langsung digunakan oleh tim frontend di komponen React:

```javascript
// src/services/chatbotApi.js

const API_BASE_URL = 'http://localhost:8000/api';

// 1. Ambil atau buat token unik pengunjung di localStorage
export function getOrCreateVisitorToken() {
  let token = localStorage.getItem('widya_darma_visitor_token');
  if (!token) {
    token = 'visitor_' + Math.random().toString(36).substring(2, 15) + Date.now().toString(36);
    localStorage.setItem('widya_darma_visitor_token', token);
  }
  return token;
}

// 2. Ambil histori chat
export async function fetchChatHistory() {
  const token = getOrCreateVisitorToken();
  const res = await fetch(`${API_BASE_URL}/chat/history/${token}`);
  const json = await res.json();
  return json.data?.messages || [];
}

// 3. Kirim pesan ke bot
export async function sendChatMessage(message) {
  const token = getOrCreateVisitorToken();
  const res = await fetch(`${API_BASE_URL}/chat/send`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      visitor_token: token,
      message: message,
    }),
  });

  if (res.status === 429) {
    throw new Error('Terlalu banyak pesan. Silakan tunggu 1 menit.');
  }

  const json = await res.json();
  if (!res.ok) {
    throw new Error(json.message || 'Gagal mengirim pesan.');
  }

  return json.data;
}
```

