/*
  Final_IRIS.ino — Template version
  Copy to Final_IRIS.ino and fill in your credentials.
  Final_IRIS.ino is gitignored to prevent secret leaks.
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <LCD-I2C.h>
#include <Adafruit_MQTT.h>
#include <Adafruit_MQTT_Client.h>
#include <Arduino_JSON.h>

#define SS_PIN 5
#define RST_PIN 4

// --- Fill in your credentials ---
const char* ssid     = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";

const char* serverName = "http://YOUR_SERVER_IP/IRIS/rfid_api.php";
const char* emailAPI   = "http://YOUR_SERVER_IP/IRIS/send_email.php";

#define AIO_USERNAME   "YOUR_ADAFRUIT_IO_USERNAME"
#define AIO_KEY        "YOUR_ADAFRUIT_IO_KEY"

// --- Rest of the firmware code below ---
// (Copy the full implementation from your working Final_IRIS.ino)
