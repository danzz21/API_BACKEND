<?php namespace App\Models;

use CodeIgniter\Model;

class PeminjamanDetailModel extends Model
{
    protected $table = 'peminjaman_detail';
    protected $primaryKey = 'id';
    protected $allowedFields = ['peminjaman_id', 'book_id', 'jumlah'];
    protected $useTimestamps = false;
    
    // Get detail with book info
    public function getWithBook($peminjaman_id)
    {
        return $this->select('peminjaman_detail.*, books.judul, books.penulis, books.isbn')
                    ->join('books', 'books.id = peminjaman_detail.book_id')
                    ->where('peminjaman_detail.peminjaman_id', $peminjaman_id)
                    ->findAll();
    }
    
    // Get total buku dipinjam per transaksi
    public function getTotalBuku($peminjaman_id)
    {
        $result = $this->selectSum('jumlah')
                      ->where('peminjaman_id', $peminjaman_id)
                      ->first();
        return $result ? $result['jumlah'] : 0;
    }
}