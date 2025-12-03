<?php namespace App\Models;

use CodeIgniter\Model;

class PeminjamanModel extends Model
{
    protected $table = 'peminjaman';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'kode_peminjaman', 'tanggal_pinjam', 
        'tanggal_kembali', 'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    
    // Generate kode peminjaman
    public function generateKode()
    {
        $prefix = 'PMJ-' . date('Y') . '-';
        $last = $this->like('kode_peminjaman', $prefix)
                    ->orderBy('kode_peminjaman', 'DESC')
                    ->first();
        
        if (!$last) {
            return $prefix . '001';
        }
        
        $lastNumber = intval(substr($last['kode_peminjaman'], strlen($prefix)));
        return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
    
    // Get with user details
    public function getWithUser()
    {
        return $this->select('peminjaman.*, users.username, users.email')
                    ->join('users', 'users.id = peminjaman.user_id');
    }
}