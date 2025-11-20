from flask import Flask, request, jsonify
import rsa

app = Flask(__name__)

public_key_pem = """-----BEGIN RSA PUBLIC KEY-----
MIGJAoGBAPV2XgEt6B5PbB7XHrYDICVktw/kbTBkx7n9pVOem2y/GLkp8Q0bkOgm
2oy7DPuZTjTbseYbcY8RinDUlP+5V9KZ+laDS4NCpKcNHJT0IH6Ij2QZ+dSMrNmc
j+3r/zVINgT7bqeyZjLEb3yf3oOMVFFzIr2/IcIj0xtHmDv3eBTxAgMBAAE=
-----END RSA PUBLIC KEY-----
"""

try:
    public_key = rsa.PublicKey.load_pkcs1(public_key_pem.encode('utf-8'))
except Exception as e:
    print("chave publica nao carregou porra -> erro:", e)
    raise e

@app.route("/amazonpay", methods=["GET"])
def amazonpay():
    cardnumber = request.args.get("cardnumber")
    if not cardnumber:
        return jsonify({"error": "a variavel cardnumber nao foi informadaaa porraaa!!!!"}), 400

    try:
        ciphertext = rsa.encrypt(cardnumber.encode('utf-8'), public_key)
        encrypted_hex = ciphertext.hex()
        return jsonify({"resultado": encrypted_hex})
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(port=4848, debug=True)
