<?php
header('Content-Type: application/json');

$API_KEY = 'e883ea1e43756537f7ab6ffce9d262e0'; // Ganti dengan API key indosmm.id asli
$API_URL = 'https://indosmm.id/api/v2';

$action = $_GET['action'] ?? '';

function loadUsers(){
    $file = __DIR__.'/users.json';
    if(!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}

function saveUsers($users){
    file_put_contents(__DIR__.'/users.json', json_encode($users, JSON_PRETTY_PRINT));
}

function loadOrders(){
    $file = __DIR__.'/data.json';
    if(!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}

function saveOrders($orders){
    file_put_contents(__DIR__.'/data.json', json_encode($orders, JSON_PRETTY_PRINT));
}

// Login sederhana
if($action === 'login'){
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');

    $users = loadUsers();
    foreach($users as $u){
        if($u['username'] === $username && $u['password'] === $password){
            echo json_encode(['success'=>true]);
            exit;
        }
    }
    echo json_encode(['success'=>false, 'message'=>'Username atau password salah']);
    exit;
}

// Ambil daftar layanan dari Indosmm
if($action === 'services'){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    $postfields = http_build_query([
        'key' => $API_KEY,
        'action' => 'services'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

    $response = curl_exec($ch);
    curl_close($ch);

    $json = json_decode($response, true);
    if(isset($json['services'])){
        echo json_encode(['success'=>true, 'services'=>$json['services']]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Gagal mengambil layanan']);
    }
    exit;
}

// Buat order baru
if($action === 'order'){
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $service = trim($input['service'] ?? '');
    $link = trim($input['link'] ?? '');
    $quantity = intval($input['quantity'] ?? 0);

    if(!$username || !$service || !$link || $quantity < 1){
        echo json_encode(['success'=>false, 'message'=>'Data tidak lengkap']);
        exit;
    }

    // Kirim order ke Indosmm
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    $postfields = http_build_query([
        'key' => $API_KEY,
        'action' => 'add',
        'service' => $service,
        'link' => $link,
        'quantity' => $quantity
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

    $response = curl_exec($ch);
    $json = json_decode($response, true);

    if(isset($json['order'])){
        // Simpan order lokal
        $orders = loadOrders();
        $orders[] = [
            'username' => $username,
            'order_id' => $json['order'],
            'service' => $service,
            'service_name' => '', // nanti diisi dari layanan, optional
            'link' => $link,
            'quantity' => $quantity,
            'status' => 'Pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        saveOrders($orders);

        echo json_encode(['success'=>true, 'order_id'=>$json['order']]);
    } else {
        echo json_encode(['success'=>false, 'message'=>$json['error'] ?? 'Gagal membuat order']);
    }
    exit;
}

// Ambil status order user
if($action === 'status'){
    $username = $_GET['username'] ?? '';
    if(!$username){
        echo json_encode(['success'=>false, 'message'=>'Username diperlukan']);
        exit;
    }

    $orders = loadOrders();
    $userOrders = array_filter($orders, fn($o) => $o['username'] === $username);

    if(!$userOrders){
        echo json_encode(['success'=>true, 'orders'=>[]]);
        exit;
    }

    // Update status dari Indosmm untuk tiap order
    foreach($userOrders as &$order){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        $postfields = http_build_query([
            'key' => $API_KEY,
            'action' => 'status',
            'order' => $order['order_id']
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

        $response = curl_exec($ch);
        $json = json_decode($response, true);

        if(isset($json['status'])){
            $order['status'] = $json['status'];
        }
    }
    saveOrders(array_values($orders));

    echo json_encode(['success'=>true, 'orders'=>array_values($userOrders)]);
    exit;
}

echo json_encode(['success'=>false, 'message'=>'Action tidak valid']);
