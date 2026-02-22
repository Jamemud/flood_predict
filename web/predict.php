<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ผลการทำนายความเสี่ยงน้ำท่วม</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background:#f5f7fb;
      padding:30px;
    }
    .container {
      max-width:700px;
      margin:auto;
      background:white;
      border-radius:12px;
      padding:30px;
      box-shadow:0 4px 18px rgba(0,0,0,.08);
    }
    h2 {
      color:#0057b8;
      text-align:center;
      margin-top:0;
    }
    .result { font-size:18px; margin-top:20px; line-height:1.8; }
    .label { font-weight:bold; }

    .prediction {
      font-size:22px;
      font-weight:bold;
      padding:15px;
      margin-top:25px;
      border-radius:10px;
      border:2px solid transparent;
    }
    .green { color:#1b5e20; border-color:#2e7d32; background:#e6f4ea; }
    .red   { color:#b71c1c; border-color:#d32f2f; background:#fdecea; }

    .meta {
      font-size:14px;
      color:#444;
      margin-top:8px;
      white-space:pre-wrap;
      font-weight:500;
    }

    .error {
      color:#b00020;
      background:#ffe5e5;
      padding:15px;
      margin-top:20px;
      border-radius:10px;
      white-space:pre-wrap;
    }

    .actions {
      margin-top:22px;
      display:flex;
      gap:10px;
      justify-content:center;
    }
    .btn {
      display:inline-block;
      padding:12px 18px;
      border-radius:10px;
      text-decoration:none;
      font-weight:700;
      border:1px solid transparent;
      cursor:pointer;
    }
    .btn-primary { background:#2e7d32; color:#fff; }
    .btn-primary:hover { filter:brightness(0.95); }
    .btn-secondary { background:#eef2ff; color:#1f2a44; border-color:#d7defc; }
    .btn-secondary:hover { filter:brightness(0.98); }
  </style>
</head>
<body>
<div class="container">
  <h2>ผลการทำนายความเสี่ยงน้ำท่วม</h2>

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
  echo "<div class='actions'><a class='btn btn-secondary' href='index.php'>กลับไปหน้าแบบฟอร์ม</a></div>";
  echo "</div></body></html>";
  exit;
}

// แสดงค่าที่กรอก
echo "<div class='result'>";
echo "<p><span class='label'>จังหวัด:</span> " . htmlspecialchars($province) . "</p>";
echo "<p><span class='label'>เดือน:</span> " . htmlspecialchars($month) . "</p>";
echo "<p><span class='label'>ปริมาณน้ำฝนเชิงพื้นที่ที่น้อยที่สุด:</span> " . htmlspecialchars($minrain) . " มิลลิเมตร</p>";
echo "<p><span class='label'>ปริมาณน้ำฝนเชิงพื้นที่ที่มากที่สุด:</span> " . htmlspecialchars($maxrain) . " มิลลิเมตร</p>";
echo "<p><span class='label'>ปริมาณน้ำฝนเชิงพื้นที่เฉลี่ย:</span> " . htmlspecialchars($avgrain) . " มิลลิเมตร</p>";
echo "<p><span class='label'>ค่าเฉลี่ยพื้นที่เสี่ยงน้ำท่วม:</span> " . htmlspecialchars($area_avg) . " ตารางเมตร</p>";
echo "</div>";

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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode < 200 || $httpCode >= 300) {
  echo "<div class='error'>เรียก ML API ไม่สำเร็จ</div>";
  echo "<div class='meta'>URL: " . htmlspecialchars($mlUrl) . "</div>";
  echo "<div class='meta'>HTTP: " . htmlspecialchars((string)$httpCode) . "</div>";
  if ($curlErr) echo "<div class='meta'>cURL error: " . htmlspecialchars($curlErr) . "</div>";
  if ($response) echo "<div class='meta'>Response: " . htmlspecialchars($response) . "</div>";
  echo "<div class='actions'><a class='btn btn-secondary' href='index.php'>กลับไปทำนายอีกครั้ง</a></div>";
  echo "</div></body></html>";
  exit;
}

$data = json_decode($response, true);
if (!is_array($data)) {
  echo "<div class='error'>ผลลัพธ์จาก ML ไม่ใช่ JSON</div>";
  echo "<div class='meta'>Raw: " . htmlspecialchars($response) . "</div>";
  echo "<div class='actions'><a class='btn btn-secondary' href='index.php'>กลับไปทำนายอีกครั้ง</a></div>";
  echo "</div></body></html>";
  exit;
}

if (!empty($data["error"])) {
  echo "<div class='error'>ML Error: " . htmlspecialchars($data["error"]) . "</div>";
  echo "<div class='actions'><a class='btn btn-secondary' href='index.php'>กลับไปทำนายอีกครั้ง</a></div>";
  echo "</div></body></html>";
  exit;
}

$pred  = (int)($data["prediction"] ?? 0);
$prob  = $data["probability_flooding"] ?? null;
$label = (string)($data["label_th"] ?? ($pred===1 ? "ความเสี่ยงน้ำท่วมสูง" : "ความเสี่ยงน้ำท่วมน้อย"));

echo "<div class='prediction " . ($pred===1 ? "red" : "green") . "'>";
echo htmlspecialchars($label);

echo "<div class='meta'>";
echo "Prediction: " . htmlspecialchars((string)$pred);
if ($prob !== null) {
  $pct = (float)$prob * 100.0;
  echo " | ความน่าจะเป็นน้ำท่วม: " . htmlspecialchars(number_format($pct, 2)) . "%";
}
echo "</div>";

echo "</div>";

// ✅ ปุ่มกลับไปทำนายอีกครั้ง
echo "<div class='actions'>";
echo "  <a class='btn btn-primary' href='index.php'>กลับไปทำนายอีกครั้ง</a>";
echo "</div>";
?>

</div>
</body>
</html>
