<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ผลการทำนายความเสี่ยงน้ำท่วม</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f5f7fb; padding:30px; }
    .container { max-width:760px; margin:auto; background:white; border-radius:12px; padding:30px; box-shadow:0 4px 18px rgba(0,0,0,.08); }
    h2 { color:#0057b8; text-align:center; margin:0 0 10px; }
    .subtitle { text-align:center; color:#6b7280; margin:0 0 20px; font-size:14px; }
    .result { font-size:18px; margin-top:20px; line-height:1.8; }
    .label { font-weight:700; }
    .prediction { font-size:22px; font-weight:800; padding:16px; margin-top:25px; border-radius:10px; border:2px solid transparent; }
    .green { color:#0a7a2f; border-color:#0a7a2f; background:#e6f4ea; }
    .red { color:#b00020; border-color:#b00020; background:#fdecea; }
    .meta { font-size:14px; color:#374151; margin-top:10px; }
    .error { color:#b00020; background:#ffe5e5; padding:15px; margin-top:20px; border-radius:10px; white-space:pre-wrap; }
    .small { font-size:13px; color:#444; margin-top:8px; white-space:pre-wrap; }

    .actions { margin-top:22px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
    .btn { display:inline-block; padding:12px 18px; border-radius:10px; text-decoration:none; font-weight:700; border:1px solid transparent; }
    .btn-primary { background:#16a34a; color:white; }
    .btn-primary:hover { filter:brightness(0.95); }
    .btn-secondary { background:#ffffff; color:#111827; border-color:#d1d5db; }
    .btn-secondary:hover { background:#f3f4f6; }
  </style>
</head>
<body>
<div class="container">
  <h2>ผลการทำนายความเสี่ยงน้ำท่วม</h2>
  <p class="subtitle">สรุปผลจากโมเดล Machine Learning (Random Forest)</p>

<?php
// ✅ รับเฉพาะ POST
$province = $_POST['province'] ?? '';
$month    = $_POST['month'] ?? '';
$minrain  = $_POST['minrain'] ?? '';
$maxrain  = $_POST['maxrain'] ?? '';
$avgrain  = $_POST['avgrain'] ?? '';
$area_avg = $_POST['area_avg'] ?? '';

if ($province==='' || $month==='' || $minrain==='' || $maxrain==='' || $avgrain==='' || $area_avg==='') {
  echo "<div class='error'>ข้อมูลไม่ครบ กรุณากรอกให้ครบทุกช่อง</div>";
  echo "<div class='actions'><a class='btn btn-primary' href='index.php'>กลับไปทำนายอีกครั้ง</a></div>";
  echo "</div></body></html>";
  exit;
}

// ✅ เรียก ML API ผ่าน Docker network (ชื่อ service = ml)
$mlUrl = "http://ml:5000/predict";

$payload = json_encode([
  "province"   => $province,
  "month"      => (int)$month,
  "rain_min"   => (float)$minrain,
  "rain_max"   => (float)$maxrain,
  "rain_avg"   => (float)$avgrain,
  "flood_area" => (float)$area_avg
], JSON_UNESCAPED_UNICODE);

// --- call ML (curl if available; fallback to file_get_contents) ---
$response = null;
$httpCode = 0;
$curlErr  = '';

if (function_exists('curl_init')) {
  $ch = curl_init($mlUrl);
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_TIMEOUT => 10,
  ]);
  $response = curl_exec($ch);
  $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlErr  = (string)curl_error($ch);
  curl_close($ch);
} else {
  $ctx = stream_context_create([
    "http" => [
      "method" => "POST",
      "header" => "Content-Type: application/json\r\n",
      "content" => $payload,
      "timeout" => 10
    ]
  ]);
  $response = @file_get_contents($mlUrl, false, $ctx);
  // $http_response_header exists if request went out
  if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
    $httpCode = (int)$m[1];
  }
}

if ($response === false || $response === null || $httpCode < 200 || $httpCode >= 300) {
  echo "<div class='error'>เรียก ML API ไม่สำเร็จ</div>";
  echo "<div class='small'>URL: " . htmlspecialchars($mlUrl) . "</div>";
  echo "<div class='small'>HTTP: " . htmlspecialchars((string)$httpCode) . "</div>";
  if ($curlErr) echo "<div class='small'>cURL error: " . htmlspecialchars($curlErr) . "</div>";
  if (is_string($response) && $response !== '') echo "<div class='small'>Response: " . htmlspecialchars($response) . "</div>";
  echo "<div class='actions'><a class='btn btn-primary' href='index.php'>กลับไปทำนายอีกครั้ง</a></div>";
  echo "</div></body></html>";
  exit;
}

$data = json_decode($response, true);
if (!is_array($data)) {
  echo "<div class='error'>ผลลัพธ์จาก ML ไม่ใช่ JSON</div>";
  echo "<div class='small'>Raw: " . htmlspecialchars((string)$response) . "</div>";
  echo "<div class='actions'><a class='btn btn-primary' href='index.php'>กลับไปทำนายอีกครั้ง</a></div>";
  echo "</div></body></html>";
  exit;
}

if (!empty($data["error"])) {
  echo "<div class='error'>ML Error: " . htmlspecialchars((string)$data["error"]) . "</div>";
  echo "<div class='actions'><a class='btn btn-primary' href='index.php'>กลับไปทำนายอีกครั้ง</a></div>";
  echo "</div></body></html>";
  exit;
}

$pred  = (int)($data["prediction"] ?? 0);
$prob  = $data["probability_flooding"] ?? null;
$label = (string)($data["label_th"] ?? ($pred===1 ? "ความเสี่ยงน้ำท่วมสูง" : "ความเสี่ยงน้ำท่วมน้อย"));

// --- Show input summary ---
echo "<div class='result'>";
echo "<p><span class='label'>จังหวัด:</span> " . htmlspecialchars($province) . "</p>";
echo "<p><span class='label'>เดือน:</span> " . htmlspecialchars((string)$month) . "</p>";
echo "<p><span class='label'>ปริมาณน้ำฝนเชิงพื้นที่ที่น้อยที่สุด:</span> " . htmlspecialchars((string)$minrain) . " มิลลิเมตร</p>";
echo "<p><span class='label'>ปริมาณน้ำฝนเชิงพื้นที่ที่มากที่สุด:</span> " . htmlspecialchars((string)$maxrain) . " มิลลิเมตร</p>";
echo "<p><span class='label'>ปริมาณน้ำฝนเชิงพื้นที่เฉลี่ย:</span> " . htmlspecialchars((string)$avgrain) . " มิลลิเมตร</p>";
echo "<p><span class='label'>ค่าเฉลี่ยพื้นที่เสี่ยงน้ำท่วม:</span> " . htmlspecialchars((string)$area_avg) . " ตารางเมตร</p>";
echo "</div>";

// --- Show prediction ---
echo "<div class='prediction " . ($pred===1 ? "red" : "green") . "'>";
echo htmlspecialchars($label);

echo "<div class='meta'>";
if ($prob !== null) {
  $pct = (float)$prob * 100.0;
  echo "ระดับความน่าจะเป็นน้ำท่วม: " . htmlspecialchars(number_format($pct, 2)) . "%";
} else {
  echo "Prediction: " . htmlspecialchars((string)$pred);
}
echo "</div>";

echo "</div>";

// --- Actions ---
echo "<div class='actions'>";
echo "  <a class='btn btn-primary' href='index.php'>กลับไปทำนายอีกครั้ง</a>";
echo "  <a class='btn btn-secondary' href='index.php'>ย้อนกลับ</a>";
echo "</div>";
?>

</div>
</body>
</html>
