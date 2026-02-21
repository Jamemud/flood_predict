# 🌊 Flood Risk Prediction System

ระบบทำนายความเสี่ยงน้ำท่วมด้วย Machine Learning (Random Forest)  
พัฒนาโดยใช้:

- Python (Flask)
- Scikit-learn
- PHP (Frontend)
- Docker & Docker Compose

---

## โครงสร้างระบบ

flood_predict/
│
├── docker-compose.yml
├── Dockerfile.ml
├── Dockerfile.web
│
├── ml/ # Flask ML API
│ ├── app.py
│ ├── flood_model.pkl
│ ├── label_encoder.pkl
│ └── requirements.txt
│
├── web/ # PHP Frontend
│ ├── index.php
│ └── predict.php
│
└── README.md
# flood_predict
