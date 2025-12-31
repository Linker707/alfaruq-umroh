<?php
// config/api-local.php - Untuk testing lokal
class LocalAPI {
    private $baseUrl = 'http://localhost/alfaroq-admin-api/api/';
    
    // Ambil galleries dari API lokal
    public function getGalleries($limit = 12) {
        $url = $this->baseUrl . 'galleries?limit=' . $limit;
        
        // Gunakan file_get_contents atau cURL
        $context = stream_context_create([
            'http' => [
                'timeout' => 5 // Timeout 5 detik
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === FALSE) {
            // Fallback ke database lokal jika API tidak bisa diakses
            return $this->getLocalGalleries($limit);
        }
        
        $data = json_decode($response, true);
        return $data['data'] ?? [];
    }
    
    // Ambil packages dari API lokal
    public function getPackages($limit = 10) {
        $url = $this->baseUrl . 'packages?limit=' . $limit;
        $response = @file_get_contents($url);
        
        if ($response === FALSE) {
            return $this->getLocalPackages($limit);
        }
        
        $data = json_decode($response, true);
        return $data['data'] ?? [];
    }
    
    // Upload image ke API lokal
    public function uploadImage($fileTmpPath, $fileName, $type = 'galleries') {
        $url = $this->baseUrl . 'upload';
        
        // Gunakan cURL untuk upload
        $ch = curl_init();
        
        $postData = [
            'image' => new CURLFile($fileTmpPath, mime_content_type($fileTmpPath), $fileName)
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    // ========== FALLBACK KE DATABASE LOKAL ==========
    
    private function getLocalGalleries($limit) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT * FROM galleries WHERE is_active = 1 LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function getLocalPackages($limit) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT * FROM packages WHERE is_active = 1 LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>