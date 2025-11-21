<?php namespace App\Controllers;

use App\Models\BookModel;

class Books extends BaseController
{
    protected $bookModel;

    public function __construct()
    {
        $this->bookModel = new BookModel();
        helper('jwt');
    }

    public function index()
    {
        $books = $this->bookModel->orderBy('created_at', 'DESC')->findAll();
        
        return $this->responseJSON([
            'status' => true,
            'data' => $books
        ]);
    }

    public function show($id = null)
    {
        $book = $this->bookModel->find($id);
        
        if (!$book) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Buku tidak ditemukan'
            ], 404);
        }

        return $this->responseJSON([
            'status' => true,
            'data' => $book
        ]);
    }

    public function create()
    {
       $user = $this->validateToken();
    
    // Debug token validation
    log_message('debug', 'Token validation result: ' . print_r($user, true));
    
    if (!$user) {
        $token = service('request')->getHeaderLine('Authorization');
        log_message('debug', 'Authorization header: ' . $token);
        
        return $this->responseJSON([
            'status' => false,
            'message' => 'Unauthorized - Token required or invalid'
        ], 401);
    }

        $request = service('request');
        $data = $request->getJSON(true);

        // Validation
        if (empty($data['judul']) || empty($data['penulis'])) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Judul dan penulis harus diisi'
            ], 400);
        }

        try {
            $bookId = $this->bookModel->insert($data);
            
            if (!$bookId) {
                return $this->responseJSON([
                    'status' => false,
                    'message' => 'Gagal menambah buku'
                ], 500);
            }

            // Get created book
            $book = $this->bookModel->find($bookId);

            return $this->responseJSON([
                'status' => true,
                'message' => 'Buku berhasil ditambahkan',
                'data' => $book
            ], 201);

        } catch (\Exception $e) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update($id = null)
{
    $user = $this->validateToken();
    if (!$user) {
        return $this->responseJSON([
            'status' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    // Debug: check ID
    log_message('debug', 'Update method called with ID: ' . ($id ?? 'NULL'));

    // Jika ID tidak ada di parameter, coba dari segment URL
    if (!$id) {
        // Coba ambil ID dari URL segment
        $uri = service('uri');
        $segments = $uri->getSegments();
        $id = end($segments); // Ambil segment terakhir
        log_message('debug', 'ID from URL segments: ' . $id);
    }

    if (!$id || !is_numeric($id)) {
        return $this->responseJSON([
            'status' => false,
            'message' => 'Book ID is required and must be numeric'
        ], 400);
    }

    $book = $this->bookModel->find($id);
    if (!$book) {
        return $this->responseJSON([
            'status' => false,
            'message' => 'Buku tidak ditemukan'
        ], 404);
    }

    $request = service('request');
    $data = $request->getJSON(true);

    try {
        log_message('debug', 'Updating book ID: ' . $id . ' with data: ' . print_r($data, true));

        // Method 1: Pakai built-in update (seharusnya work)
        $updated = $this->bookModel->update($id, $data);
        
        if (!$updated) {
            $errors = $this->bookModel->errors();
            return $this->responseJSON([
                'status' => false,
                'message' => 'Gagal mengupdate buku: ' . implode(', ', $errors)
            ], 500);
        }

        $updatedBook = $this->bookModel->find($id);

        return $this->responseJSON([
            'status' => true,
            'message' => 'Buku berhasil diupdate',
            'data' => $updatedBook
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Update exception: ' . $e->getMessage());
        return $this->responseJSON([
            'status' => false,
            'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
        ], 500);
    }
}

   public function delete($id = null)
{
    $user = $this->validateToken();
    if (!$user) {
        return $this->responseJSON([
            'status' => false,
            'message' => 'Unauthorized'
        ], 401);
    }

    // Debug: check ID
    log_message('debug', 'Delete method called with ID: ' . ($id ?? 'NULL'));

    // Jika ID tidak ada di parameter, coba dari segment URL
    if (!$id) {
        $uri = service('uri');
        $segments = $uri->getSegments();
        $id = end($segments);
        log_message('debug', 'ID from URL segments: ' . $id);
    }

    if (!$id || !is_numeric($id)) {
        return $this->responseJSON([
            'status' => false,
            'message' => 'Book ID is required and must be numeric'
        ], 400);
    }

    $book = $this->bookModel->find($id);
    if (!$book) {
        return $this->responseJSON([
            'status' => false,
            'message' => 'Buku tidak ditemukan'
        ], 404);
    }

    try {
        log_message('debug', 'Deleting book ID: ' . $id);

        // Method 1: Pakai built-in delete
        $deleted = $this->bookModel->delete($id);
        
        // Method alternatif jika masih error:
        // $deleted = $this->bookModel->where('id', $id)->delete();
        
        log_message('debug', 'Delete result: ' . ($deleted ? 'true' : 'false'));

        if (!$deleted) {
            return $this->responseJSON([
                'status' => false,
                'message' => 'Gagal menghapus buku'
            ], 500);
        }

        return $this->responseJSON([
            'status' => true,
            'message' => 'Buku berhasil dihapus'
        ]);

    } catch (\Exception $e) {
        log_message('error', 'Delete exception: ' . $e->getMessage());
        log_message('error', 'Stack trace: ' . $e->getTraceAsString());
        
        return $this->responseJSON([
            'status' => false,
            'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
        ], 500);
    }
}
}