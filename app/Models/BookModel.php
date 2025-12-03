<?php namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table = 'books';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'judul', 'penulis', 'penerbit', 'tahun_terbit', 
        'isbn', 'jumlah_halaman', 'sinopsis', 'stok'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // BUAT VALIDATION RULES LEBIH FLEXIBLE ATAU COMMENT SEMENTARA
    protected $validationRules = [
        'judul' => 'required|min_length[3]|max_length[255]',
        'penulis' => 'required|min_length[3]|max_length[255]',
        'stok' => 'permit_empty|integer' // permit_empty untuk field optional
    ];
    
    protected $validationMessages = [
        'judul' => [
            'required' => 'Judul buku harus diisi',
            'min_length' => 'Judul minimal 3 karakter',
            'max_length' => 'Judul maksimal 255 karakter'
        ],
        'penulis' => [
            'required' => 'Penulis harus diisi',
            'min_length' => 'Nama penulis minimal 3 karakter',
            'max_length' => 'Nama penulis maksimal 255 karakter'
        ]
    ];
    // app/Models/BookModel.php - tambah method
public function kurangiStok($bookId, $jumlah)
{
    $book = $this->find($bookId);
    if ($book && $book['stok'] >= $jumlah) {
        $this->set('stok', 'stok - ' . $jumlah, false)
             ->where('id', $bookId)
             ->update();
        return true;
    }
    return false;
}

public function tambahStok($bookId, $jumlah)
{
    $this->set('stok', 'stok + ' . $jumlah, false)
         ->where('id', $bookId)
         ->update();
    return true;
}
}