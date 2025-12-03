<?php namespace App\Controllers;

use App\Models\PeminjamanModel;
use App\Models\PeminjamanDetailModel;
use App\Models\BookModel;
use App\Models\UserModel;

class Peminjaman extends BaseController
{
    protected $peminjamanModel;
    protected $peminjamanDetailModel;
    protected $bookModel;
    protected $userModel;

    public function __construct()
    {
        $this->peminjamanModel = new PeminjamanModel();
        $this->peminjamanDetailModel = new PeminjamanDetailModel();
        $this->bookModel = new BookModel();
        $this->userModel = new UserModel();
        helper('jwt');
    }

    // GET: Semua peminjaman
    public function index()
    {
        $user = $this->validateToken();
        if (!$user) {
            return $this->responseJSON(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            // Get semua peminjaman user ini
            $peminjamans = $this->peminjamanModel->where('user_id', $user['user_id'])->findAll();
            
            $result = [];
            foreach ($peminjamans as $peminjaman) {
                // Get detail buku
                $details = $this->peminjamanDetailModel
                    ->select('peminjaman_detail.*, books.judul, books.penulis')
                    ->join('books', 'books.id = peminjaman_detail.book_id')
                    ->where('peminjaman_id', $peminjaman['id'])
                    ->findAll();
                
                // Hitung total buku
                $totalBuku = 0;
                foreach ($details as $detail) {
                    $totalBuku += $detail['jumlah'];
                }
                
                $result[] = [
                    'header' => $peminjaman,
                    'details' => $details,
                    'total_buku' => $totalBuku
                ];
            }

            return $this->responseJSON([
                'status' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // POST: Buat peminjaman baru
    public function create()
    {
        $user = $this->validateToken();
        if (!$user) {
            return $this->responseJSON(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $request = service('request');
        $data = $request->getJSON(true);

        // Validasi
        if (empty($data['items']) || !is_array($data['items'])) {
            return $this->responseJSON(['status' => false, 'message' => 'Minimal satu buku harus dipinjam'], 400);
        }

        try {
            // 1. Cek stok untuk semua buku
            foreach ($data['items'] as $item) {
                $book = $this->bookModel->find($item['book_id']);
                if (!$book) {
                    return $this->responseJSON([
                        'status' => false,
                        'message' => 'Buku tidak ditemukan'
                    ], 400);
                }
                
                if ($book['stok'] < $item['jumlah']) {
                    return $this->responseJSON([
                        'status' => false,
                        'message' => 'Stok buku "' . $book['judul'] . '" tidak cukup. Stok tersedia: ' . $book['stok']
                    ], 400);
                }
            }

            // 2. Generate kode peminjaman
            $kode = 'PMJ-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

            // 3. Buat header peminjaman
            $peminjamanData = [
                'user_id' => $user['user_id'],
                'kode_peminjaman' => $kode,
                'tanggal_pinjam' => date('Y-m-d'),
                'status' => 'dipinjam'
            ];

            $peminjamanId = $this->peminjamanModel->insert($peminjamanData);

            // 4. Buat detail peminjaman dan kurangi stok
            foreach ($data['items'] as $item) {
                // Insert detail
                $this->peminjamanDetailModel->insert([
                    'peminjaman_id' => $peminjamanId,
                    'book_id' => $item['book_id'],
                    'jumlah' => $item['jumlah']
                ]);

                // Kurangi stok buku
                $this->bookModel->set('stok', 'stok - ' . $item['jumlah'], false)
                    ->where('id', $item['book_id'])
                    ->update();
            }

            // 5. Get created data untuk response
            $peminjaman = $this->peminjamanModel->find($peminjamanId);
            $details = $this->peminjamanDetailModel
                ->select('peminjaman_detail.*, books.judul, books.penulis')
                ->join('books', 'books.id = peminjaman_detail.book_id')
                ->where('peminjaman_id', $peminjamanId)
                ->findAll();

            return $this->responseJSON([
                'status' => true,
                'message' => 'Peminjaman berhasil dibuat',
                'data' => [
                    'peminjaman' => $peminjaman,
                    'items' => $details
                ]
            ], 201);

        } catch (\Exception $e) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // PUT: Pengembalian buku
    public function update($id = null)
    {
        $user = $this->validateToken();
        if (!$user) {
            return $this->responseJSON(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $peminjaman = $this->peminjamanModel->find($id);
            if (!$peminjaman) {
                return $this->responseJSON(['status' => false, 'message' => 'Peminjaman tidak ditemukan'], 404);
            }

            if ($peminjaman['user_id'] != $user['user_id']) {
                return $this->responseJSON(['status' => false, 'message' => 'Anda tidak memiliki akses'], 403);
            }

            if ($peminjaman['status'] === 'dikembalikan') {
                return $this->responseJSON(['status' => false, 'message' => 'Buku sudah dikembalikan'], 400);
            }

            // Get semua detail peminjaman
            $details = $this->peminjamanDetailModel->where('peminjaman_id', $id)->findAll();

            // Kembalikan stok untuk setiap buku
            foreach ($details as $detail) {
                $this->bookModel->set('stok', 'stok + ' . $detail['jumlah'], false)
                    ->where('id', $detail['book_id'])
                    ->update();
            }

            // Update status peminjaman
            $updateData = [
                'status' => 'dikembalikan',
                'tanggal_kembali' => date('Y-m-d')
            ];

            $this->peminjamanModel->update($id, $updateData);

            return $this->responseJSON([
                'status' => true,
                'message' => 'Buku berhasil dikembalikan'
            ]);

        } catch (\Exception $e) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}