<!DOCTYPE html>
<html>
<head>
    <title>ทำนายการเกิดน้ำท่วม</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        form {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333333;
        }

        label, input, select {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            font-size: 16px;
        }

        input, select {
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <form action="predict.php" method="post">
        <h2>ระบบทำนายการเกิดน้ำท่วม</h2>
        
        <label>จังหวัด:</label>
        <select name="province" required>
            <option value="">-- เลือกจังหวัด --</option>
            <option>Bueng Kan</option>
            <option>Buriram</option>
            <option>Chachoengsao</option>
            <option>Chainat</option>
            <option>Chaiyaphum</option>
            <option>Chanthaburi</option>
            <option>Chiang Mai</option>
            <option>Chiang Rai</option>
            <option>Chonburi</option>
            <option>Chumphon</option>
            <option>Kalasin</option>
            <option>Kamphaeng Phet</option>
            <option>Kanchanaburi</option>
            <option>Khon Kaen</option>
            <option>Krabi</option>
            <option>Lampang</option>
            <option>Lamphun</option>
            <option>Loei</option>
            <option>Lopburi</option>
            <option>Mae Hong Son</option>
            <option>Maha Sarakham</option>
            <option>Nakhon Nayok</option>
            <option>Nakhon Ratchasima</option>
            <option>Nakhon Sawan</option>
            <option>Nakhon Si Thammarat</option>
            <option>Nan</option>
            <option>Narathiwat</option>
            <option>Nong Bua Lamphu</option>
            <option>Nong Khai</option>
            <option>Pattani</option>
            <option>Phang Nga</option>
            <option>Phatthalung</option>
            <option>Phayao</option>
            <option>Phetchabun</option>
            <option>Phetchaburi</option>
            <option>Phichit</option>
            <option>Phitsanulok</option>
            <option>Phrae</option>
            <option>Phuket</option>
            <option>Prachinburi</option>
            <option>Prachuap Khiri Khan</option>
            <option>Ranong</option>
            <option>Ratchaburi</option>
            <option>Rayong</option>
            <option>Roi Et</option>
            <option>Sa Kaeo</option>
            <option>Sakon Nakhon</option>
            <option>Saraburi</option>
            <option>Satun</option>
            <option>Sisaket</option>
            <option>Songkhla</option>
            <option>Sukhothai</option>
            <option>Suphan Buri</option>
            <option>Surat Thani</option>
            <option>Surin</option>
            <option>Tak</option>
            <option>Trang</option>
            <option>Ubon Ratchathani</option>
            <option>Udon Thani</option>
            <option>Uthai Thani</option>
            <option>Uttaradit</option>
            <option>Yala</option>
            <option>Yasothon</option>
        </select>
        <label>เดือน:</label>
        <select name="month" required>
            <option value="">-- เลือกเดือน --</option>
            <option>1</option>
            <option>2</option>
            <option>3</option>
            <option>4</option>
            <option>5</option>
            <option>6</option>
            <option>7</option>
            <option>8</option>
            <option>9</option>
            <option>10</option>
            <option>11</option>
            <option>12</option>
        </select>
        

        <label>ปริมาณน้ำฝนเชิงพื้นที่ที่น้อยที่สุด(มิลลิเมตร):</label>
        <input type="number" step="0.01" name="minrain" required>

        <label>ปริมาณน้ำฝนเชิงพื้นที่ที่มากที่สุด(มิลลิเมตร):</label>
        <input type="number" step="0.01" name="maxrain" required>

        <label>ปริมาณน้ำฝนเชิงพื้นที่เฉลี่ย(มิลลิเมตร):</label>
        <input type="number" step="0.01" name="avgrain" required>

        <label>ค่าเฉลี่ยพื้นที่เสี่ยงน้ำท่วม(ตารางเมตร):</label>
        <input type="number" step="0.01" name="area_avg" required>

        <input type="submit" value="ทำนายผล">
    </form>
</body>
</html>
