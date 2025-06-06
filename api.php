<?php
class Api
{
    public $api_url = 'https://indosmm.id/api/v2';
    public $api_key = 'e883ea1e43756537f7ab6ffce9d262e0'; // Ganti dengan API Key Indosmm kamu

    public function order($data)
    {
        $post = array_merge(['key' => $this->api_key, 'action' => 'add'], $data);
        return json_decode((string)$this->connect($post));
    }

    public function status($order_id)
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'status',
            'order' => $order_id
        ]));
    }

    public function multiStatus($order_ids)
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'status',
            'orders' => implode(",", (array)$order_ids)
        ]));
    }

    public function services()
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'services',
        ]));
    }

    public function refill(int $orderId)
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'refill',
            'order' => $orderId,
        ]));
    }

    public function multiRefill(array $orderIds)
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'refill',
            'orders' => implode(',', $orderIds),
        ]), true);
    }

    public function refillStatus(int $refillId)
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'refill_status',
            'refill' => $refillId,
        ]));
    }

    public function multiRefillStatus(array $refillIds)
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'refill_status',
            'refills' => implode(',', $refillIds),
        ]), true);
    }

    public function cancel(array $orderIds)
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'cancel',
            'orders' => implode(',', $orderIds),
        ]), true);
    }

    public function balance()
    {
        return json_decode($this->connect([
            'key' => $this->api_key,
            'action' => 'balance',
        ]));
    }

    private function connect($post)
    {
        $_post = [];
        if (is_array($post)) {
            foreach ($post as $name => $value) {
                $_post[] = $name . '=' . urlencode($value);
            }
        }

        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if (is_array($post)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $result = curl_exec($ch);
        if (curl_errno($ch) != 0 && empty($result)) {
            $result = false;
        }
        curl_close($ch);
        return $result;
    }
}

// --------------------------------------
// Handling request dari frontend (misal via AJAX)
// --------------------------------------

header('Content-Type: application/json');

$api = new Api();
$api->api_key = 'e883ea1e43756537f7ab6ffce9d262e0'; // Ganti API Key di sini juga jika perlu

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'services':
        $services = $api->services();
        if ($services) {
            echo json_encode(['success' => true, 'data' => $services]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengambil layanan']);
        }
        break;

    case 'balance':
        $balance = $api->balance();
        if ($balance) {
            echo json_encode(['success' => true, 'data' => $balance]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengambil saldo']);
        }
        break;

    case 'order':
        $postData = $_POST;
        unset($postData['action']);
        $order = $api->order($postData);
        if ($order && isset($order->order)) {
            echo json_encode(['success' => true, 'data' => $order]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal membuat order']);
        }
        break;

    case 'status':
        $orderId = $_GET['order'] ?? $_POST['order'] ?? 0;
        $status = $api->status($orderId);
        if ($status) {
            echo json_encode(['success' => true, 'data' => $status]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengambil status order']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Aksi tidak dikenal']);
        break;
}
