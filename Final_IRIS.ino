#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <LCD-I2C.h>
#include <Adafruit_MQTT.h>
#include <Adafruit_MQTT_Client.h>
#include <Arduino_JSON.h>   // Using Arduino_JSON

// --- Pins ---
#define SS_PIN 5
#define RST_PIN 4

// --- WiFi ---
const char* ssid = "..";
const char* password = "..";

// --- Server APIs ---
const char* serverName = "http://10.188.15.79/IRIS/rfid_api.php";
const char* emailAPI   = "http://10.188.15.79/IRIS/send_email.php";

// --- Adafruit IO ---
#define AIO_SERVER     "io.adafruit.com"
#define AIO_SERVERPORT 1883
#define AIO_USERNAME   "Spandan_Parikh"
#define AIO_KEY        ".."

WiFiClient client;
Adafruit_MQTT_Client mqtt(&client, AIO_SERVER, AIO_SERVERPORT, AIO_USERNAME, AIO_KEY);
Adafruit_MQTT_Publish rfidFeed = Adafruit_MQTT_Publish(&mqtt, AIO_USERNAME "/feeds/rfid_data");

MFRC522 mfrc522(SS_PIN, RST_PIN);
LCD_I2C lcd(0x27, 16, 2);

String lastScannedTag = "";
int scanCount = 0;

// ------------------- Duplicate scan tracking -------------------
#define MAX_TAGS 40
String scannedTags[MAX_TAGS];           // store recent RFID tags
unsigned long lastScanTime[MAX_TAGS];   // store last scan time in millis()
const unsigned long DUPLICATE_INTERVAL = 20000; // 20 sec

// ---------------- Email scheduling (1 hour) ----------------
const unsigned long EMAIL_INTERVAL = 3600000UL; // 1 hour in milliseconds
unsigned long emailTimerStart = 0;
bool emailScheduled = false;

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

// ------------- Send RFID to Database & get Name + Status (Server decides) ----------
/*
  Option B behavior:
  - POST only 'rfid' to server
  - Server returns JSON: { "success": true, "name": "John Doe", "status": "IN" }
  - We return studentName and set statusOut by reference
*/
String sendToDatabase(String rfidTag, String &statusOut) {
  String studentName = "";
  statusOut = "";

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "rfid=" + rfidTag; // server decides status
    int httpResponseCode = http.POST(postData);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("DB Response: " + response);

      // Parse JSON using Arduino_JSON
      JSONVar jsonObj = JSON.parse(response);
      if (JSON.typeof(jsonObj) == "undefined") {
        Serial.println("Parsing failed!");
      } else {
        bool success = false;
        if (jsonObj.hasOwnProperty("success")) {
          success = (bool)jsonObj["success"];
        }
        if (success) {
          if (jsonObj.hasOwnProperty("name")) {
            studentName = (const char*)jsonObj["name"];
          }
          if (jsonObj.hasOwnProperty("status")) {
            statusOut = String((const char*)jsonObj["status"]);
            statusOut.toUpperCase();
          }
        } else {
          // server returned an error (could be duplicate or other)
          if (jsonObj.hasOwnProperty("message")) {
            Serial.println(String("Server Message: ") + (const char*)jsonObj["message"]);
          } else {
            Serial.println("Server returned success=false");
          }
          studentName = "";
        }
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

// ------------- Trigger Email API -------------
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
  } else {
    Serial.println("Cannot send email: WiFi not connected.");
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

  // ------------------ Check 20-second duplicate ------------------
  unsigned long currentMillis = millis();
  bool isDuplicate = false;

  int index = -1;
  for (int i = 0; i < MAX_TAGS; i++) {
    if (scannedTags[i] == rfidTag) {
      index = i;
      if (currentMillis - lastScanTime[i] < DUPLICATE_INTERVAL) {
        isDuplicate = true;
      }
      break;
    }
  }

  if (isDuplicate) {
    Serial.println("Duplicate scan ignored (within 20s).");
    return;
  }

  // Update or add the tag in the array
  if (index == -1) {
    // find empty slot or use LRU replacement (simple approach: first empty)
    for (int i = 0; i < MAX_TAGS; i++) {
      if (scannedTags[i] == "") {
        scannedTags[i] = rfidTag;
        lastScanTime[i] = currentMillis;
        index = i;
        break;
      }
    }
    // if still -1 (no empty slots), overwrite the oldest
    if (index == -1) {
      int oldestIdx = 0;
      unsigned long oldestTime = lastScanTime[0];
      for (int i = 1; i < MAX_TAGS; i++) {
        if (lastScanTime[i] < oldestTime) {
          oldestTime = lastScanTime[i];
          oldestIdx = i;
        }
      }
      scannedTags[oldestIdx] = rfidTag;
      lastScanTime[oldestIdx] = currentMillis;
      index = oldestIdx;
    }
  } else {
    lastScanTime[index] = currentMillis; // update existing
  }

  // ----------------- Server decides status & name -----------------
  String status = "";
  String studentName = sendToDatabase(rfidTag, status);

  // If server returned no studentName (error / duplicate), don't update display or count
  if (studentName == "" || status == "") {
    // do not increment scanCount; already printed server message in sendToDatabase
    return;
  }

  // Schedule email if not already scheduled (first successful scan)
  if (!emailScheduled) {
    emailTimerStart = millis();
    emailScheduled = true;
    Serial.println("Email scheduled to be sent in 1 hour.");
  }

  // Send to Adafruit IO
  sendRFIDData(rfidTag + " - " + status);

  // --- LCD Output (smooth) ---
  lcd.setCursor(0, 0);
  lcd.print("                ");  // clear line 1
  lcd.setCursor(0, 0);
  lcd.print(status + ": " + studentName);

  lcd.setCursor(0, 1);
  lcd.print("Scan Count:      ");
  lcd.setCursor(12, 1);
  lcd.print(++scanCount);

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

  // init scannedTags times to 0 to avoid garbage
  for (int i = 0; i < MAX_TAGS; i++) {
    scannedTags[i] = "";
    lastScanTime[i] = 0;
  }
}

// ----------------- Loop ----------------------
void loop() {
  // MQTT reconnect / process
  if (!mqtt.connected()) connectMQTT();
  mqtt.processPackets(10);

  // Handle RFID reads
  readRFID();

  // Email timer check
  if (emailScheduled) {
    unsigned long now = millis();
    if (now - emailTimerStart >= EMAIL_INTERVAL) {
      Serial.println("1 hour elapsed — sending email report...");
      sendEmailReport();
      emailScheduled = false; // reset; next scheduling will happen after next successful scan
    }
  }

  delay(300);
}
x
