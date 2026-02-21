# -*- coding: utf-8 -*-
from flask import Flask, request, jsonify
import os
import joblib

app = Flask(__name__)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, "flood_model.pkl")
ENCODER_PATH = os.path.join(BASE_DIR, "label_encoder.pkl")

# โหลดตอนเริ่มรัน service
model = joblib.load(MODEL_PATH)
encoder = joblib.load(ENCODER_PATH)

@app.post("/predict")
def predict():
    data = request.get_json(force=True)

    province = str(data["province"])
    month = int(data["month"])
    rain_min = float(data["rain_min"])
    rain_max = float(data["rain_max"])
    rain_avg = float(data["rain_avg"])
    flood_area = float(data["flood_area"])

    province_encoded = int(encoder.transform([province])[0])
    X = [[province_encoded, month, rain_min, rain_max, rain_avg, flood_area]]

    pred = int(model.predict(X)[0])
    prob = float(model.predict_proba(X)[0][1])

    label_th = "เสี่ยงน้ำท่วม" if pred == 1 else "ไม่เสี่ยงน้ำท่วม"
    label_en = "High risk of flooding" if pred == 1 else "Low risk of flooding"

    return jsonify({
        "model": "Random Forest",
        "prediction": pred,
        "probability_flooding": prob,
        "label_th": label_th,
        "label_en": label_en
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
