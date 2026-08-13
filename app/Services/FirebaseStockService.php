<?php
class FirebaseStockService {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    private function get_firebase_access_token() {
        $keyFile = 'c:/xampp/htdocs/CURTISS/curtiss-erp-cc0c0-firebase-adminsdk-fbsvc-a0895b17f1.json';
        if (!file_exists($keyFile)) return false;
        
        $keyData = json_decode(file_get_contents($keyFile), true);
        if (!$keyData) return false;
        
        $now = time();
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        
        // Scope includes messaging (FCM) and database (RTDB)
        $payload = json_encode([
            'iss' => $keyData['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging https://www.googleapis.com/auth/firebase.database https://www.googleapis.com/auth/userinfo.email',
            'aud' => $keyData['token_uri'],
            'exp' => $now + 3600,
            'iat' => $now
        ]);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = '';
        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $keyData['private_key'], "sha256WithRSAEncryption");
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $keyData['token_uri']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        
        $tokenData = json_decode($res, true);
        return $tokenData['access_token'] ?? false;
    }

    public function broadcast_stock_update($productId, $newStockQty, $reservedQty, $excludeUserId = 0) {
        $accessToken = $this->get_firebase_access_token();
        if (!$accessToken) {
            error_log("Failed to get Firebase Access Token for HTTP v1 API");
            return;
        }

        // 1. UPDATE FIREBASE REALTIME DATABASE (for Web UI)
        $rtdbUrl = 'https://curtiss-erp-cc0c0-default-rtdb.firebaseio.com/stock_levels/' . $productId . '.json';
        $rtdbData = [
            'stock_qty' => floatval($newStockQty),
            'reserved_qty' => floatval($reservedQty),
            'timestamp' => time()
        ];
        
        $chRtdb = curl_init();
        curl_setopt($chRtdb, CURLOPT_URL, $rtdbUrl);
        curl_setopt($chRtdb, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($chRtdb, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($chRtdb, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chRtdb, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($chRtdb, CURLOPT_POSTFIELDS, json_encode($rtdbData));
        curl_exec($chRtdb);
        curl_close($chRtdb);

        // 2. SEND FCM PUSH NOTIFICATIONS (for Android Apps)
        $this->db->query("SELECT fcm_token FROM users WHERE fcm_token IS NOT NULL AND fcm_token != '' AND id != :exclude_id");
        $this->db->bind(':exclude_id', $excludeUserId);
        $users = $this->db->resultSet();
        
        if (!empty($users)) {
            $tokens = array_map(function($u) { return $u->fcm_token; }, $users);
            $fcmUrl = 'https://fcm.googleapis.com/v1/projects/curtiss-erp-cc0c0/messages:send';
            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
            ];
            
            foreach ($tokens as $token) {
                $fields = [
                    'message' => [
                        'token' => $token,
                        'data' => [
                            'type' => 'stock_update',
                            'product_id' => strval($productId),
                            'stock_qty' => strval($newStockQty),
                            'reserved_qty' => strval($reservedQty)
                        ]
                    ]
                ];
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $fcmUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_exec($ch);
                curl_close($ch);
            }
        }
    }
}
