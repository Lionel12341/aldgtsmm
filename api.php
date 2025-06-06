<?php
header('Content-Type: application/json');

$API_URL = 'https://indosmm.id/api/v2';  // ganti jika perlu
$API_KEY = 'e883ea1e43756537f7ab6ffce9d262e0';       // ganti dengan API key Anda dari indosmm.id

$dataFile = __DIR__.'/data.json';

function loadOrders(){
    global $dataFile;
    if(!file_exists($dataFile)) return [];
    $json = file_get_contents($dataFile);
    $data = json_decode($json, true);
    return $data ?: [];
}

function saveOrders($orders){
    global $dataFile;
    file_put_contents($dataFile, json_encode($orders, JSON_PRETTY_PRINT));
}

$action = $_GET['action'] ?? null;

if($action === 'services'){
    // Ambil daftar layanan dari Indosmm API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    $postfields = http_build_query([
        'key' => $API_KEY,
        'action' => 'services',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    $response = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($response, true);
    if($json && isset($json['services'])){
        echo json_encode(['success'=>true, 'services'=>$json['services']]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Gagal memuat layanan']);
    }
    exit;
}

if($action === 'order'){
    $input = json_decode(file_get_contents('php://input'), true);
    $username = trim($input['username'] ?? '');
    $service = trim($input['service'] ?? '');
    $link = trim($input['link'] ?? '');
    $quantity = intval($input['quantity'] ?? 0);

    if(!$username || !$service || !$link || $quantity <= 0){
        echo json_encode(['success'=>false, 'message'=>'Data order tidak lengkap atau salah']);
        exit;
    }

    // Kirim order ke API Indosmm
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    $postfields = http_build_query([
        'key' => $API_KEY,
        'action' => 'order',
        'service' => $service,
        'link' => $link,
        'quantity' => $quantity
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if($err){
        echo json_encode(['success'=>false, 'message'=>'Gagal mengirim order: '.$err]);
        exit;
    }

    $json = json_decode($response, true);
    if(!$json || !isset($json['order'])){
        echo json_encode(['success'=>false, 'message'=>'Response API tidak valid']);
        exit;
    }

    // Simpan order lokal
    $orders = loadOrders();
    $newOrder = [
        'order_id' => $json['order'],
        'username' => $username,
        'service' => $service,
        'service_name' => '',
        'link' => $link,
        'quantity' => $quantity,
        'status' => 'Pending',
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Mencari nama layanan dari response, jika ada
    if(isset($json['services'])){
        foreach($json['services'] as $s){
            if($s['service'] == $service){
                $newOrder['service_name'] = $s['name'];
                break;
            }
        }
    }

    if(!$newOrder['service_name']){
        $newOrder['service_name'] = $service;
    }

    $orders[] = $newOrder;
    saveOrders($orders);

    echo json_encode(['success'=>true, 'order_id'=>$newOrder['order_id']]);
    exit;
}

if($action === 'status'){
    $username = $_GET['username'] ?? '';
    if(!$username){
        echo json_encode(['success'=>false, 'message'=>'Username dibutuhkan']);
        exit;
    }

    $orders = loadOrders();

    $userOrders = array_filter($orders, function($o) use ($username) {
        return $o['username'] === $username;
    });

    // Update status order tiap order dari API Indosmm
    $updatedOrders = [];
    foreach($userOrders as $order){
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
        curl_close($ch);

        $json = json_decode($response, true);
        if($json && isset($json['status'])){
            $order['status'] = $json['status'];
        }
        $updatedOrders[] = $order;
    }

    // Update data order lokal
    $allOrders = loadOrders();
    foreach($allOrders as &$o){
        foreach($updatedOrders as $uo){
            if($o['order_id'] == $uo['order_id']){
                $o = $uo;
                break;
            }
        }
    }
    saveOrders($allOrders);

    echo json_encode(['success'=>true, 'orders'=>$updatedOrders]);
    exit;
}

// Default fallback
echo json_encode(['success'=>false, 'message'=>'Action tidak valid']);
exit;
