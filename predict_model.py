# -*- coding: utf-8 -*-
import sys
import os
import json
import joblib
import pandas as pd

def out_json(obj, code=0):
    # ให้ PHP อ่านได้ง่าย และรองรับภาษาไทย
    print(json.dumps(obj, ensure_ascii=False))
    sys.exit(code)

def main():
    if len(sys.argv) != 7:
        out_json({"error": "Usage: python predict_model.py <province> <month> <MinRain> <MaxRain> <AvgRain> <AvgFloodRiskArea>"}, 1)

    try:
        province = sys.argv[1]
        month = int(sys.argv[2])
        min_rain = float(sys.argv[3])
        max_rain = float(sys.argv[4])
        avg_rain = float(sys.argv[5])
        area_avg = float(sys.argv[6])
    except Exception as e:
        out_json({"error": f"Invalid input: {e}"}, 1)

    base_dir = os.path.dirname(os.path.abspath(__file__))
    model_path = os.path.join(base_dir, "model", "flood_model.pkl")
    le_path = os.path.join(base_dir, "model", "label_encoder.pkl")

    if not os.path.exists(model_path):
        out_json({"error": f"Model not found: {model_path}"}, 1)
    if not os.path.exists(le_path):
        out_json({"error": f"LabelEncoder not found: {le_path}"}, 1)

    try:
        model = joblib.load(model_path)
        le = joblib.load(le_path)
    except Exception as e:
        out_json({"error": f"Load model failed: {e}"}, 1)

    try:
        prov_enc = int(le.transform([province])[0])
    except Exception as e:
        out_json({"error": f"Province '{province}' not in LabelEncoder: {e}"}, 1)

    cols = ["province", "month", "MinRain", "MaxRain", "AvgRain", "AvgFloodRiskArea(Square meter)"]
    X = pd.DataFrame([[prov_enc, month, min_rain, max_rain, avg_rain, area_avg]], columns=cols)

    try:
        pred = int(model.predict(X)[0])
        proba = None
        if hasattr(model, "predict_proba"):
            proba = float(model.predict_proba(X)[0][1])
    except Exception as e:
        out_json({"error": f"Predict failed: {e}"}, 1)

    # ป้ายกำกับไทย (ตามที่คุณต้องการ)
    label_th = "ความเสี่ยงน้ำท่วมสูง" if pred == 1 else "ความเสี่ยงน้ำท่วมต่ำ"

    out_json({
        "model": "RandomForest",
        "prediction": pred,
        "probability_flooding": proba,
        "label_th": label_th,
        "paths": {
            "model_path": model_path,
            "label_encoder_path": le_path
        }
    }, 0)

if __name__ == "__main__":
    main()
