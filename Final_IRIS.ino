#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <LCD-I2C.h>
#include <Adafruit_MQTT.h>
#include <Adafruit_MQTT_Client.h>
#include <Arduino_JSON.h>

// --- Pins ---
#define SS_PIN 5
#define RST_PIN 4

// --- WiFi ---
const char* ssid = "Realme 12+ 5G";
const char* password = "spandy2206";

// --- Server APIs ---
const char* serverName = "http://10.188.15.79/IRIS/rfid_api.php";
const char* emailAPI   = "http://10.188.15.79/IRIS/send_email.php";

// --- Adafruit IO ---
#define AIO_SERVER     "io.adafruit.com"
#define AIO_SERVERPORT 1883
#define AIO_USERNAME   "Spandan_Parikh"
#define AIO_KEY        ""

WiFiClient client;
Adafruit_MQTT_Client mqtt(&client, AIO_SERVER, AIO_SERVERPORT, AIO_USERNAME, AIO_KEY);
Adafruit_MQTT_Publish rfidFeed = Adafruit_MQTT_Publish(&mqtt, AIO_USERNAME "/feeds/rfid_data");

MFRC522 mfrc522(SS_PIN, RST_PIN);
LCD_I2C lcd(0x27, 16, 2);

String lastScannedTag = "";
int scanCount = 0;

// ------------------- Duplicate scan tracking -------------------
#define MAX_TAGS 20
String scannedTags[MAX_TAGS];
unsigned long lastScanTime[MAX_TAGS];
const unsigned long MIN_INTERVAL = 5000;   // 5 seconds

// ---------------- Email Timer -----------------
unsigned long lastEmailTime = 0;
const unsigned long EMAIL_INTERVAL = 3600000; // 1 hour

// ---------------- WiFi Connect ----------------
void connectWiFi() {
  Serial.print("Connecting to WiFi...");
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected: " + WiFi.localIP().toString());
}

// ---------------- MQTT Connect ----------------
void connectMQTT() {
  while (!mqtt.connected()) {
    Serial.print("Connecting to MQTT...");
    if (mqtt.connect()) {
      Serial.println("Connected!");
    } else {
      Serial.print("Failed. Retrying in 2s...");
      delay(2000);
    }
  }
}

// ------------- Send RFID to Database & get Name ----------
// Note: status is now updated from server response
String sendToDatabase(String rfidTag, String &status) {
  String studentName = "";
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "rfid=" + rfidTag + "&status=" + status;
    int httpResponseCode = http.POST(postData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("DB Response: " + response);

      JSONVar jsonObj = JSON.parse(response);
      if (JSON.typeof(jsonObj) != "undefined") {
        bool success = (bool)jsonObj["success"];
        if (success) {
          studentName = (const char*)jsonObj["name"];
          status = (const char*)jsonObj["status"];  // <-- get server status
        } else {
          Serial.println(String("Server Message: ") + (const char*)jsonObj["message"]);
          return "";
        }
      } else {
        Serial.println("Parsing failed!");
      }
    } else {
      Serial.println("DB Error: " + String(httpResponseCode));
    }

    http.end();
  } else {
    Serial.println("WiFi not connected.");
  }
  return studentName;
}

// ---------- Send to Adafruit IO --------------
void sendRFIDData(String data) {
  if (!rfidFeed.publish(data.c_str())) {
    Serial.println("Adafruit IO publish failed.");
  } else {
    Serial.println("Published to Adafruit IO: " + data);
  }
}

// ---------------- Trigger Email API ----------------
void sendEmailReport() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(emailAPI);

    int httpCode = http.GET();
    if (httpCode > 0) {
      Serial.println("Email Sent: " + http.getString());
    } else {
      Serial.println("Email Trigger Failed: " + String(httpCode));
    }
    http.end();
  }
}

// ---------------- RFID Reading ----------------
void readRFID() {
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) return;

  String rfidTag = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    rfidTag += String(mfrc522.uid.uidByte[i], HEX);
  }
  rfidTag.toUpperCase();
  Serial.println("RFID: " + rfidTag);

  unsigned long currentMillis = millis();
  int index = -1;
  for (int i = 0; i < MAX_TAGS; i++) {
    if (scannedTags[i] == rfidTag) {
      index = i;
      break;
    }
  }

  String status = "IN"; // initial placeholder, will be overwritten by API

  if (index != -1) {
    unsigned long timeDiff = currentMillis - lastScanTime[index];
    if (timeDiff < MIN_INTERVAL) {
      Serial.println("Scan ignored: wait 5 seconds for duplicate");
      return;
    }
    lastScanTime[index] = currentMillis;
  } else {
    for (int i = 0; i < MAX_TAGS; i++) {
      if (scannedTags[i] == "") {
        scannedTags[i] = rfidTag;
        lastScanTime[i] = currentMillis;
        break;
      }
    }
  }

  // --------- Send to DB and get Student Name & correct status ----------
  String studentName = sendToDatabase(rfidTag, status);

  if (studentName == "") {
    studentName = rfidTag;
    Serial.println("API failed, showing UID on LCD.");
  } else {
    Serial.println("Student Name from API: " + studentName);
    Serial.println("Status from API: " + status);  // <-- confirmed accurate
  }

  // --------- Update Scan Count ----------
  if (status == "IN") {
    scanCount++;
  } else {
    if (scanCount > 0) scanCount--;
  }

  // --------- Send to Adafruit IO ----------
  sendRFIDData(rfidTag + " - " + status);

  // --------- LCD Output ----------
  lcd.setCursor(0, 0);
  lcd.print("                ");
  lcd.setCursor(0, 0);
  lcd.print(status + ": " + studentName);

  lcd.setCursor(0, 1);
  lcd.print("Scan Count:      ");
  lcd.setCursor(12, 1);
  lcd.print(scanCount);

  lastScannedTag = rfidTag;

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}

// ----------------- Setup ---------------------
void setup() {
  Serial.begin(115200);
  Wire.begin();
  lcd.begin(&Wire);
  lcd.display();
  lcd.backlight();
  lcd.clear();
  lcd.print("RFID Starting...");

  SPI.begin();
  mfrc522.PCD_Init();

  connectWiFi();
  connectMQTT();
  lastEmailTime = millis();
}

// ----------------- Loop ----------------------
void loop() {
  if (!mqtt.connected()) connectMQTT();
  mqtt.processPackets(10);

  readRFID();

  if (millis() - lastEmailTime >= EMAIL_INTERVAL) {
    sendEmailReport();
    lastEmailTime = millis();
  }

  delay(500);
}
