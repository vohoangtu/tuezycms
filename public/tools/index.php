<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Shared\Infrastructure\Security\ContentEncryption;
use Shared\Infrastructure\Security\KeyValidator;

// Simple authentication (in production, use proper authentication)
session_start();

// Load .env file
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}

$password = $_ENV['TOOLS_PASSWORD'] ?? 'admin123';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_POST['password']) || $_POST['password'] !== $password) {
        die(json_encode(['error' => 'Invalid password']));
    }

    $action = $_POST['action'];

    if ($action === 'generate_key') {
        $encryption = new ContentEncryption();
        $key = ContentEncryption::generateKey();
        $encryption->saveKey($key);
        
        $validator = new KeyValidator();
        $hash = $validator->generateSourceHash();
        
        echo json_encode([
            'success' => true,
            'key' => $key,
            'source_hash' => $hash,
            'message' => 'Key generated and source hash created successfully'
        ]);
        exit;
    }

    if ($action === 'validate_source') {
        $validator = new KeyValidator();
        $isValid = $validator->validateSourceIntegrity();
        
        echo json_encode([
            'success' => $isValid,
            'message' => $isValid ? 'Source code integrity validated' : 'Source code integrity check failed'
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuzyCMS - Tools</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        input[type="password"], input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus, input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-bottom: 10px;
        }
        button:hover {
            background: #5568d3;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .result.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .result.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .key-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            word-break: break-all;
            font-family: monospace;
            font-size: 12px;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 TuzyCMS Tools</h1>
        
        <div class="warning">
            <strong>⚠️ Cảnh báo:</strong> Chỉ sử dụng công cụ này khi cài đặt lần đầu hoặc khi cần tạo lại key. 
            Key sẽ được sử dụng để mã hóa/giải mã nội dung trong database.
        </div>

        <form id="keyForm">
            <div class="form-group">
                <label for="password">Mật khẩu:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" name="action" value="generate_key">Tạo Key Mới</button>
        </form>

        <form id="validateForm">
            <button type="submit" name="action" value="validate_source">Kiểm tra tính toàn vẹn Source Code</button>
        </form>

        <div id="result" class="result"></div>
    </div>

    <script>
        document.getElementById('keyForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'generate_key');
            
            const result = document.getElementById('result');
            result.style.display = 'none';
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    result.className = 'result success';
                    result.innerHTML = `
                        <strong>✓ Thành công!</strong><br>
                        ${data.message}<br>
                        <div class="key-display">
                            <strong>Key:</strong><br>
                            ${data.key}<br><br>
                            <strong>Source Hash:</strong><br>
                            ${data.source_hash}
                        </div>
                        <p style="margin-top: 10px; font-size: 12px; color: #666;">
                            ⚠️ Lưu key này ở nơi an toàn. Nếu mất key, toàn bộ dữ liệu mã hóa sẽ không thể đọc được.
                        </p>
                    `;
                } else {
                    result.className = 'result error';
                    result.innerHTML = `<strong>✗ Lỗi:</strong> ${data.error || data.message}`;
                }
                result.style.display = 'block';
            } catch (error) {
                result.className = 'result error';
                result.innerHTML = `<strong>✗ Lỗi:</strong> ${error.message}`;
                result.style.display = 'block';
            }
        });

        document.getElementById('validateForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'validate_source');
            formData.append('password', document.getElementById('password').value);
            
            const result = document.getElementById('result');
            result.style.display = 'none';
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    result.className = 'result success';
                    result.innerHTML = `<strong>✓ Thành công!</strong><br>${data.message}`;
                } else {
                    result.className = 'result error';
                    result.innerHTML = `<strong>✗ Lỗi:</strong> ${data.message}`;
                }
                result.style.display = 'block';
            } catch (error) {
                result.className = 'result error';
                result.innerHTML = `<strong>✗ Lỗi:</strong> ${error.message}`;
                result.style.display = 'block';
            }
        });
    </script>
</body>
</html>

