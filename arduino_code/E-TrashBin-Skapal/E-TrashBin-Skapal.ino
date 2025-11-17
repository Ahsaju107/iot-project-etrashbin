#include <NewPing.h>
#include <ESP32Servo.h>
#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <UniversalTelegramBot.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <HTTPClient.h>
#include "esp_system.h"
#include "esp_task_wdt.h"

// ==========================================
// WATCHDOG TIMER CONFIGURATION
// ==========================================
// #define WDT_TIMEOUT 30

void printResetReason() {
  esp_reset_reason_t r = esp_reset_reason();
  Serial.print("Reset reason (num): ");
  Serial.println((int)r);
  Serial.print("Reset reason (text): ");
  switch (r) {
    case ESP_RST_POWERON: Serial.println("POWERON"); break;
    case ESP_RST_EXT: Serial.println("EXT"); break;
    case ESP_RST_SW: Serial.println("SOFTWARE"); break;
    case ESP_RST_PANIC: Serial.println("PANIC"); break;
    case ESP_RST_INT_WDT: Serial.println("INT_WDT"); break;
    case ESP_RST_TASK_WDT: Serial.println("TASK_WDT"); break;
    case ESP_RST_WDT: Serial.println("WDT"); break;
    case ESP_RST_DEEPSLEEP: Serial.println("DEEPSLEEP"); break;
    case ESP_RST_BROWNOUT: Serial.println("BROWNOUT"); break;
    case ESP_RST_SDIO: Serial.println("SDIO"); break;
    default: Serial.println("UNKNOWN"); break;
  }
}

// ==========================================
// WIFI CONFIGURATION
// ==========================================
const char* ssid = "COEPALASAH";
const char* password = "info1996";

// ==========================================
// BOT TELEGRAM CONFIGURATION
// ==========================================
#define BOTtoken "8509010159:AAHBhEypb8dJzweh5YV_6KAzR_ZW3yttuaU"
#define CHAT_ID "6987610796"
WiFiClientSecure client;
UniversalTelegramBot bot(BOTtoken, client);

// INIT dengan -1 agar bisa detect pertama kali
int prev_kapasitas_organik = -1;
int prev_kapasitas_anorganik = -1;
int prev_kapasitas_logam = -1;

const unsigned long alertCooldown = 1UL * 60UL * 1000UL;  // 1 menit
unsigned long lastAlertOrganik = 0;
unsigned long lastAlertAnorganik = 0;
unsigned long lastAlertLogam = 0;

// ==========================================
// SERVER CONFIGURATION
// ==========================================
const char* server = "http://192.168.43.96/trashbin/esp32_api.php";
const int deviceId = 5;
const char* deviceName = "XI TKJ 3";

// ==========================================
// UART (ESP32-CAM)
// ==========================================
const int RXp2 = 16;
const int TXp2 = 17;

// ==========================================
// ESP32 CAM CONTROL
// ==========================================
const int wake_pin = 18;
const int sleep_pin = 19;
bool cam_awake = false;
unsigned long last_detected = 0;

// ==========================================
// ULTRASONIC SENSORS (NewPing)
// ==========================================
const int JARAK_MAKSIMAL = 60;
NewPing sensor_organik(14, 35, JARAK_MAKSIMAL);
NewPing sensor_anorganik(27, 32, JARAK_MAKSIMAL);
NewPing sensor_logam(5, 33, JARAK_MAKSIMAL);

const int trig_pin1 = 13;
const int echo_pin1 = 34;
long duration;
float distance;

// ==========================================
// PROXIMITY SENSOR (Metal Detection)
// ==========================================
const int prox_pin = 23;
bool detected = false;

// ==========================================
// SERVO MOTORS
// ==========================================
const int servo_pin1 = 25;
const int servo_pin2 = 26;

const int servo1_default = 90;
const int servo2_default = 85;
const int servo2_dump_front = 30;
const int servo2_dump_back = 165;

Servo servo1, servo2;

// ==========================================
// LCD DISPLAY
// ==========================================
LiquidCrystal_I2C lcd(0x27, 20, 4);
int displayState = 0;

// ==========================================
// TIMING VARIABLES
// ==========================================
unsigned long lastHeartBeat = 0;
const unsigned long heartBeatInterval = 30000;

unsigned long lastKapasitasUpdate = 0;
const unsigned long kapasitasUpdateInterval = 10000;

unsigned long lastDisplayUpdate = 0;
const unsigned long displayUpdateInterval = 5000;

// ==========================================
// STATE MACHINE
// ==========================================
enum SortingState { IDLE,
                    DETECTING,
                    PROCESSING,
                    DUMPING };
SortingState sortingState = IDLE;

// ==========================================
// SETUP
// ==========================================
void setup() {
  Serial.begin(115200);
  delay(100);

  Serial2.begin(74880, SERIAL_8N1, RXp2, TXp2);
  Serial2.setRxBufferSize(256);

  printResetReason();
  Serial.println("=================================");
  Serial.println("ESP32 E-TrashBin Starting...");
  Serial.println("=================================");

  // esp_task_wdt_config_t wdt_config = {
  //   .timeout_ms = WDT_TIMEOUT * 1000,
  //   .idle_core_mask = 0,
  //   .trigger_panic = true
  // };
  // esp_task_wdt_init(&wdt_config);
  // esp_task_wdt_add(NULL);
  // Serial.println("Watchdog configured: 30s");
  Serial.println("✅ Using default watchdog");
  pinMode(wake_pin, OUTPUT);
  pinMode(sleep_pin, OUTPUT);
  pinMode(prox_pin, INPUT);
  pinMode(trig_pin1, OUTPUT);
  pinMode(echo_pin1, INPUT);
  digitalWrite(wake_pin, LOW);
  digitalWrite(sleep_pin, LOW);

  Wire.begin();
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("    E-TrashBin");
  lcd.setCursor(0, 1);
  lcd.print("  Starting...");

  //

  ESP32PWM::allocateTimer(0);
  ESP32PWM::allocateTimer(1);
  ESP32PWM::allocateTimer(2);
  ESP32PWM::allocateTimer(3);

  servo1.setPeriodHertz(50);
  servo2.setPeriodHertz(50);
  delay(100);

  servo1.attach(servo_pin1, 500, 2500);
  servo2.attach(servo_pin2, 500, 2500);
  delay(100);

  servo1.write(servo1_default);
  delay(50);
  servo2.write(servo2_default);
  delay(100);

  //

  setup_wifi();

  //
  delay(500);

  Serial.println("Sending initial heartbeat...");
  kirimHeartBeat();

  //

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("    System Ready");
  delay(1000);
  lcd.clear();

  Serial.println("=================================");
  Serial.println("System Ready!");
  Serial.println("=================================\n");
}

// ==========================================
// MAIN LOOP
// ==========================================
void loop() {

  unsigned long currentTime = millis();

  if (currentTime - lastHeartBeat >= heartBeatInterval) {
    kirimHeartBeat();
    lastHeartBeat = currentTime;
  }

  if (currentTime - lastKapasitasUpdate >= kapasitasUpdateInterval) {
    updateKapasitas();
    lastKapasitasUpdate = currentTime;
  }

  if (currentTime - lastDisplayUpdate >= displayUpdateInterval) {
    display();
    lastDisplayUpdate = currentTime;
  }

  handleSorting();

  if (cam_awake && millis() - last_detected > 20UL * 60UL * 1000UL) {
    cam_awake = false;
    Serial2.println("sleep");
    Serial.println("💤 Kamera masuk mode deepsleep");
  }

  delay(10);
}

// ==========================================
// SORTING STATE MACHINE
// ==========================================

void handleSorting() {
  static unsigned long stateTimer = 0;
  static String hasil_ai = "";

  switch (sortingState) {
    case IDLE:
      {
        float ultrasonic = ultrasonic1();
        Serial.print("Distance: ");
        Serial.print(ultrasonic);
        Serial.println(" cm");


        if (ultrasonic > 0 && ultrasonic < 40.00) {
          delay(50);
          if (ultrasonic > 0 && ultrasonic < 40.00) {
            if (!cam_awake) {
              wake_cam();
              cam_awake = true;
              Serial.println("🔆 Kamera dibangunkan...");
            }

            last_detected = millis();
            sortingState = DETECTING;
            stateTimer = millis();
            hasil_ai = "";
            detected = false;

            Serial.println("📦 Objek terdeteksi, mulai identifikasi...");

            // TAMBAHKAN: Cek metal SEGERA setelah deteksi objek
            delay(100);  // Beri waktu sensor stabil
            int metal_check = digitalRead(prox_pin);
            Serial.print("🔍 Quick metal check: ");
            Serial.println(metal_check == 0 ? "METAL" : "NON-METAL");
          }
        }
        break;
      }

    case DETECTING:
      {
        // CEK METAL DULU dengan prioritas tinggi (500ms pertama)
        if (millis() - stateTimer < 1000) {
          int metal_value = digitalRead(prox_pin);
          if (metal_value == 0) {
            detected = true;
            sortingState = PROCESSING;
            Serial.println("🔩 METAL DETECTED! Skip camera check");
            return;  // Langsung keluar, skip AI check
          }
          // Belum 500ms, terus cek metal
          return;
        }

        // Setelah 500ms, jika bukan metal, baru cek AI
        if (millis() - stateTimer > 500 && millis() - stateTimer < 3000) {
          // Cek metal sekali lagi untuk mastiin
          int metal_value = digitalRead(prox_pin);
          if (metal_value == 0) {
            detected = true;
            sortingState = PROCESSING;
            Serial.println("🔩 METAL DETECTED (late detection)! Skip camera check");
            return;
          }

          // Baca hasil AI
          if (Serial2.available()) {
            String s = Serial2.readStringUntil('\n');
            s.trim();
            if (s.length() > 0) {
              if (s.indexOf("READY") < 0 && s.indexOf("IDLE") < 0) {
                hasil_ai = s;
                sortingState = PROCESSING;
                Serial.print("🤖 AI Result: ");
                Serial.println(hasil_ai);
                return;
              }
            }
          }
        }

        // Timeout setelah 3 detik
        if (millis() - stateTimer > 3000) {
          sortingState = PROCESSING;
          Serial.println("⏱️ Detection timeout");
        }

        break;
      }

    case PROCESSING:
      {
        // PRIORITAS: Metal > AI Result > Fallback
        if (detected) {
          Serial.println("🔩 Buang ke tempat LOGAM");
          metal();
          kirimSorting("logam");
        } else if (hasil_ai.length() > 0) {
          if (hasil_ai.equalsIgnoreCase("organik")) {
            Serial.println("🍃 Buang ke ORGANIK");
            organik();
            kirimSorting("organik");
          } else if (hasil_ai.equalsIgnoreCase("anorganik")) {
            Serial.println("🥤 Buang ke ANORGANIK");
            anorganik();
            kirimSorting("anorganik");
          } else {
            Serial.print("❓ Pesan tidak dikenal: ");
            Serial.println(hasil_ai);
            anorganik();
            kirimSorting("anorganik");
          }
        } else {
          Serial.println("⏱️ Timeout - fallback ke anorganik");
          anorganik();
          kirimSorting("anorganik");
        }

        sortingState = DUMPING;
        stateTimer = millis();

        break;
      }

    case DUMPING:
      {
        if (millis() - stateTimer > 2000) {
          updateKapasitas();
          sortingState = IDLE;
          Serial.println("✅ Sorting selesai\n");
        }
        break;
      }
  }
}

// ==========================================
// WIFI SETUP
// ==========================================
void setup_wifi() {
  Serial.print("Connecting to ");
  Serial.println(ssid);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 30) {
    delay(500);
    Serial.print(".");
    attempts++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi connected!");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());

    // PENTING: Setup Telegram SETELAH WiFi tersambung
    client.setInsecure();
    Serial.println("✅ Telegram configured");

    // Test kirim pesan
    delay(500);

    bot.sendMessage(CHAT_ID, "🤖 E-TrashBin Online", "");

    Serial.println("✅ Startup message sent");
  } else {
    Serial.println("\n❌ WiFi failed!");
  }
}

// ==========================================
// ULTRASONIC SENSOR
// ==========================================
float ultrasonic1() {
  digitalWrite(trig_pin1, LOW);
  delayMicroseconds(2);
  digitalWrite(trig_pin1, HIGH);
  delayMicroseconds(10);
  digitalWrite(trig_pin1, LOW);

  duration = pulseIn(echo_pin1, HIGH, 30000);
  distance = duration * 0.034 / 2;

  if (distance == 0.00 || distance > 400) distance = -1.00;
  return distance;
}

// ==========================================
// HTTP FUNCTIONS
// ==========================================
void kirimHeartBeat() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️ WiFi not connected - skip heartbeat");
    return;
  }

  Serial.println("💓 Starting heartbeat...");
  // TAMBAHKAN INI

  HTTPClient http;
  WiFiClient client;

  client.setTimeout(2000);
  http.setTimeout(2000);
  http.setReuse(false);

  Serial.println("Connecting to server...");
  // TAMBAHKAN INI

  if (!http.begin(client, String(server))) {
    Serial.println("❌ HTTP begin failed");
    client.stop();
    // TAMBAHKAN INI
    return;
  }

  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  int rssi = WiFi.RSSI();
  int wifiSignal = map(rssi, -100, -40, 0, 100);
  wifiSignal = constrain(wifiSignal, 0, 100);

  String postData = "device_id=" + String(deviceId);
  postData += "&action=heartbeat";
  postData += "&wifi_signal=" + String(wifiSignal);

  Serial.println("Sending POST...");
  // TAMBAHKAN INI

  int httpCode = http.POST(postData);

  // TAMBAHKAN INI

  if (httpCode == 200) {
    Serial.println("💚 Heartbeat OK | Signal: " + String(wifiSignal) + "%");
  } else if (httpCode > 0) {
    Serial.printf("⚠️ HTTP Code: %d\n", httpCode);
  } else {
    Serial.printf("❌ HTTP Error: %s\n", http.errorToString(httpCode).c_str());
  }

  http.end();
  client.stop();
  // TAMBAHKAN INI

  Serial.println("💓 Heartbeat complete");
}

void updateKapasitas() {
  if (WiFi.status() != WL_CONNECTED) return;

  // TAMBAHKAN DI AWAL

  unsigned int organik = sensor_organik.ping_cm();
  delay(30);
  unsigned int anorganik = sensor_anorganik.ping_cm();
  delay(30);
  unsigned int logam = sensor_logam.ping_cm();

  if (organik == 0) organik = JARAK_MAKSIMAL;
  if (anorganik == 0) anorganik = JARAK_MAKSIMAL;
  if (logam == 0) logam = JARAK_MAKSIMAL;

  int kapasitas_organik = (int)(((float)(JARAK_MAKSIMAL - organik) / (float)JARAK_MAKSIMAL) * 100.0f);
  int kapasitas_anorganik = (int)(((float)(JARAK_MAKSIMAL - anorganik) / (float)JARAK_MAKSIMAL) * 100.0f);
  int kapasitas_logam = (int)(((float)(JARAK_MAKSIMAL - logam) / (float)JARAK_MAKSIMAL) * 100.0f);

  kapasitas_organik = constrain(kapasitas_organik, 0, 100);
  kapasitas_anorganik = constrain(kapasitas_anorganik, 0, 100);
  kapasitas_logam = constrain(kapasitas_logam, 0, 100);

  HTTPClient http;
  WiFiClient client;

  client.setTimeout(2000);
  http.setTimeout(2000);
  http.setReuse(false);

  // TAMBAHKAN SEBELUM HTTP BEGIN

  if (!http.begin(client, String(server))) {
    client.stop();
    // TAMBAHKAN INI
    return;
  }

  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  String postData = "device_id=" + String(deviceId);
  postData += "&action=update_kapasitas";
  postData += "&kapasitas_organik=" + String(kapasitas_organik);
  postData += "&kapasitas_anorganik=" + String(kapasitas_anorganik);
  postData += "&kapasitas_logam=" + String(kapasitas_logam);

  // TAMBAHKAN SEBELUM POST

  int httpCode = http.POST(postData);

  // TAMBAHKAN SETELAH POST

  if (httpCode == 200) {
    Serial.printf("📊 O:%d%% A:%d%% L:%d%%\n", kapasitas_organik, kapasitas_anorganik, kapasitas_logam);
  } else {
    Serial.printf("⚠️ Update kapasitas failed: %d\n", httpCode);
  }

  http.end();
  client.stop();

  // CEK ALERT
  checkAndSendAlert(kapasitas_organik, kapasitas_anorganik, kapasitas_logam);
}

void kirimSorting(String jenis_sampah) {
  if (WiFi.status() != WL_CONNECTED) return;

  // TAMBAHKAN DI AWAL

  HTTPClient http;
  WiFiClient client;

  client.setTimeout(2000);
  http.setTimeout(2000);
  http.setReuse(false);

  if (!http.begin(client, String(server))) {
    client.stop();
    // TAMBAHKAN INI
    return;
  }

  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  String postData = "device_id=" + String(deviceId);
  postData += "&action=add_sorting";
  postData += "&jenis_sampah=" + jenis_sampah;

  http.POST(postData);
  http.end();
  client.stop();
}

// ==========================================
// DISPLAY LCD
// ==========================================
void display() {
  unsigned int organik = sensor_organik.ping_cm();
  delay(30);
  unsigned int anorganik = sensor_anorganik.ping_cm();
  delay(30);
  unsigned int logam = sensor_logam.ping_cm();

  if (organik == 0) organik = JARAK_MAKSIMAL;
  if (anorganik == 0) anorganik = JARAK_MAKSIMAL;
  if (logam == 0) logam = JARAK_MAKSIMAL;

  int kapasitas_organik = (int)(((float)(JARAK_MAKSIMAL - organik) / (float)JARAK_MAKSIMAL) * 100.0f);
  int kapasitas_anorganik = (int)(((float)(JARAK_MAKSIMAL - anorganik) / (float)JARAK_MAKSIMAL) * 100.0f);
  int kapasitas_logam = (int)(((float)(JARAK_MAKSIMAL - logam) / (float)JARAK_MAKSIMAL) * 100.0f);

  kapasitas_organik = constrain(kapasitas_organik, 0, 100);
  kapasitas_anorganik = constrain(kapasitas_anorganik, 0, 100);
  kapasitas_logam = constrain(kapasitas_logam, 0, 100);

  lcd.clear();

  switch (displayState) {
    case 0:
      lcd.setCursor(4, 0);
      lcd.print("Organik");
      lcd.setCursor(6, 1);
      lcd.print(kapasitas_organik);
      lcd.print("%");
      break;
    case 1:
      lcd.setCursor(3, 0);
      lcd.print("Anorganik");
      lcd.setCursor(6, 1);
      lcd.print(kapasitas_anorganik);
      lcd.print("%");
      break;
    case 2:
      lcd.setCursor(5, 0);
      lcd.print("Logam");
      lcd.setCursor(6, 1);
      lcd.print(kapasitas_logam);
      lcd.print("%");
      break;
  }

  displayState = (displayState + 1) % 3;
}

// ==========================================
// CAMERA CONTROL
// ==========================================
void wake_cam() {
  digitalWrite(wake_pin, HIGH);
  delay(200);
  digitalWrite(wake_pin, LOW);
}

void sleep_cam() {
  digitalWrite(sleep_pin, HIGH);
  delay(200);
  digitalWrite(sleep_pin, LOW);
}

// ==========================================
// SERVO FUNCTIONS
// ==========================================
void metal() {
  servo2.write(servo2_dump_back);
  delay(500);
  servo2.write(servo2_default);
}

void organik() {
  servo1.write(150);
  delay(200);
  servo2.write(servo2_dump_front);
  delay(500);
  servo2.write(servo2_default);
  delay(200);
  servo1.write(servo1_default);
}

void anorganik() {
  servo1.write(30);
  delay(200);
  servo2.write(servo2_dump_front);
  delay(500);
  servo2.write(servo2_default);
  delay(200);
  servo1.write(servo1_default);
}

// void smoothMove(Servo &servo, int startPos, int endPos, int stepDelay) {
//   int step = (startPos < endPos) ? 1 : -1;

//   for (int pos = startPos; pos != endPos; pos += step) {
//     servo.write(pos);
//     delay(stepDelay);
//   }

//   servo.write(endPos);

// }

// ==========================================
// TELEGRAM ALERT (SIMPLE VERSION)
// ==========================================
void checkAndSendAlert(int kapO, int kapA, int kapL) {
  unsigned long now = millis();

  Serial.printf("Check alert: O=%d%% (prev=%d) A=%d%% (prev=%d) L=%d%% (prev=%d)\n",
                kapO, prev_kapasitas_organik, kapA, prev_kapasitas_anorganik, kapL, prev_kapasitas_logam);

  // ORGANIK
  if (kapO >= 85 && prev_kapasitas_organik < 85 && (now - lastAlertOrganik > alertCooldown)) {
    Serial.println("🚨 Trigger alert: ORGANIK");
    sendAlert("ORGANIK", kapO, kapA, kapL);
    lastAlertOrganik = now;
  }

  // ANORGANIK
  if (kapA >= 85 && prev_kapasitas_anorganik < 85 && (now - lastAlertAnorganik > alertCooldown)) {
    Serial.println("🚨 Trigger alert: ANORGANIK");
    sendAlert("ANORGANIK", kapO, kapA, kapL);
    lastAlertAnorganik = now;
  }

  // LOGAM
  if (kapL >= 85 && prev_kapasitas_logam < 85 && (now - lastAlertLogam > alertCooldown)) {
    Serial.println("🚨 Trigger alert: LOGAM");
    sendAlert("LOGAM", kapO, kapA, kapL);
    lastAlertLogam = now;
  }

  prev_kapasitas_organik = kapO;
  prev_kapasitas_anorganik = kapA;
  prev_kapasitas_logam = kapL;
}

void sendAlert(String jenis, int kapO, int kapA, int kapL) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️ WiFi not connected - skip sendAlert");
    return;
  }

  String msg = "🚨 ALERT PENUH!\n\n";
  msg += "Device: " + String(deviceName) + "\n";
  msg += "Kompartemen: " + jenis + "\n\n";
  msg += "Organik: " + String(kapO) + "%\n";
  msg += "Anorganik: " + String(kapA) + "%\n";
  msg += "Logam: " + String(kapL) + "%\n\n";
  msg += "Segera kosongkan!";

  Serial.println("📤 Sending alert to Telegram...");
  Serial.print("IP: ");
  Serial.println(WiFi.localIP());
  Serial.print("RSSI: ");
  Serial.println(WiFi.RSSI());



  // Periksa token/koneksi: getMe() mengembalikan bool pada beberapa versi library
  bool meOk = bot.getMe();
  Serial.print("bot.getMe() => ");
  Serial.println(meOk ? "OK" : "FAILED");

  // Coba kirim sampai 3x; gunakan long untuk menampung baik int message id atau bool
  long lastResult = 0;
  for (int attempt = 1; attempt <= 3; ++attempt) {
    Serial.printf("Attempt %d to send message...\n", attempt);
    long res = (long)bot.sendMessage(String(CHAT_ID), msg, "");  // cast aman: bool -> 0/1, int id tetap
    lastResult = res;
    Serial.printf("sendMessage result (raw): %ld\n", res);

    if (res > 0) {
      Serial.println("✅ Alert sent (message id > 0 or success)");
      break;
    } else {
      Serial.println("❌ sendMessage returned 0 - retrying in 1s...");
      delay(1000);
    }
  }

  if (lastResult == 0) {
    Serial.println("‼️ All attempts failed. Possible causes:");
    Serial.println("- network/TLS issue");
    Serial.println("- token/chat_id invalid (but startup ok suggests token ok)");
    Serial.println("- Telegram rate limit / server busy");
    Serial.println("Check bot.getMe() output and network logs.");
  }
}
