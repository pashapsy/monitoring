#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <DHT.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <ESP8266WebServer.h>
#include <UniversalTelegramBot.h>
#include <ArduinoJson.h>

// ================== LCD I2C ==================
LiquidCrystal_I2C lcd(0x27, 16, 2);

// ================== Sensor ===================
#define MQ2PIN A0
#define DHTPIN D5
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

// ================== Output ===================
#define LED_MERAH D6
#define LED_BIRU D7
#define BUZZER D8

// ================== WiFi =====================
const char* ssid = "Lab Riset TI";
const char* password = "labrisetti";

// ================== Telegram Bot =============
#define BOTtoken "8144240581:AAF-0aaErL-en0FA9_m40s_bOzlWWLgk_kU"
#define CHAT_ID "-4795936771"

WiFiClientSecure Client;
UniversalTelegramBot bot(BOTtoken, Client);

unsigned long lastTimeBotRan;
const int botRequestDelay = 1000;

// ============== Lokasi & Status ==============
const char* lokasiList[] = { "Ruang Server", "Ruang IT", "Ruang Data Center" };
int lokasiIndex = 0;
unsigned long lastLokasiSwitchTime = 0;
const unsigned long lokasiSwitchInterval = 60000;

// ============== Notifikasi Telegram ===========
bool alreadyNotified = false;
unsigned long lastNotifyTime = 0;
const unsigned long notifyInterval = 15000;

// ============== Data Kirim Server =============
unsigned long lastDataSendTime = 0; // Waktu terakhir data dikirim
const unsigned long dataSendInterval = 20000;

// ============== Buzzer non-blocking ==============
unsigned long previousMillis = 0;
const long interval = 500;  // Interval ON/OFF buzzer (ms)
bool buzzerState = false;

// ============== WiFi Connect ==================
void connectWiFi() {
  Serial.println("Menghubungkan ke WiFi...");
  WiFi.begin(ssid, password);
  digitalWrite(BUZZER, LOW);
  int attempt = 0;

  while (WiFi.status() != WL_CONNECTED && attempt < 20) {
    delay(500);
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Terhubung!");
    Serial.print("IP ESP8266: ");
    Serial.println(WiFi.localIP());
    digitalWrite(LED_MERAH, LOW);
    digitalWrite(LED_BIRU, HIGH);
  } else {
    Serial.println("\n❌ Gagal Terhubung ke WiFi!");
    digitalWrite(LED_MERAH, HIGH);
    digitalWrite(LED_BIRU, LOW);
  }
}

// ============== Handle Pesan Telegram =========
void handleNewMessages(int numNewMessages) {
  for (int i = 0; i < numNewMessages; i++) {
    String chat_id = bot.messages[i].chat_id;
    String text = bot.messages[i].text;
    String from_name = bot.messages[i].from_name;
    const char* lokasi = lokasiList[lokasiIndex];

    // Baca ulang kadar asap untuk mengetahui status saat ini
    int asap = analogRead(MQ2PIN);
    String status = "Aman";
    if (asap >= 201 && asap <= 300) status = "Siaga";
    else if (asap >= 301 && asap <= 500) status = "Bahaya";
    else if (asap >= 501) status = "Darurat";

    Serial.println("📩 Pesan masuk: " + text + " dari " + from_name);

    if (text == "/start") {
      String welcome = "👋 Selamat datang, " + from_name + "!\n\n";
      welcome += "Perintah:\n";
      welcome += "🌡 /Temperatur - Cek suhu\n";
      welcome += "🌫 /Asap - Cek kadar asap\n\n";
      bot.sendMessage(chat_id, welcome, "");
    }

    if (text == "/Temperatur") {
      float suhu = dht.readTemperature();
      if (isnan(suhu)) {
        bot.sendMessage(chat_id, "⚠ Gagal membaca sensor suhu!", "");
      } else {
        bot.sendMessage(chat_id, "🌡 Suhu di " + String(lokasi) + ": " + String(suhu, 1) + " °C", "");
      }
    }

    if (text == "/Asap") {
      int asap = analogRead(MQ2PIN);
      String status = "Aman";
      if (asap >= 201 && asap <= 300) status = "Siaga";
      else if (asap >= 301 && asap <= 500) status = "Bahaya";
      else if (asap >= 501) status = "Darurat";

      String asapMsg = "🌫 Kadar asap di " + String(lokasi) + ":\n";
      asapMsg += String(asap) + " ppm\nStatus: " + status;
      bot.sendMessage(chat_id, asapMsg, "");
    }
  }
}

// ============== Setup ========================
void setup() {
  Serial.begin(115200);
  Wire.begin(D2, D1);
  lcd.begin(16, 2);
  lcd.setBacklight(255);

  pinMode(LED_MERAH, OUTPUT);
  pinMode(LED_BIRU, OUTPUT);
  pinMode(BUZZER, OUTPUT);
  pinMode(MQ2PIN, INPUT);

  digitalWrite(BUZZER, LOW);

  dht.begin();
  delay(2000);

  lcd.clear();
  lcd.print("Connecting...");
  digitalWrite(LED_MERAH, HIGH);
  digitalWrite(LED_BIRU, LOW);

  WiFi.mode(WIFI_STA);
  connectWiFi();

  Client.setInsecure();  // untuk HTTPS Telegram
  bot = UniversalTelegramBot(BOTtoken, Client);

  lastTimeBotRan = millis();
}

// ============== Loop Utama ===================
void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }

  // Ganti lokasi tiap interval
  if (millis() - lastLokasiSwitchTime >= lokasiSwitchInterval) {
    lokasiIndex = (lokasiIndex + 1) % 3;
    lastLokasiSwitchTime = millis();
    Serial.println("🔁 Lokasi berganti ke: " + String(lokasiList[lokasiIndex]));
  }

  const char* lokasi = lokasiList[lokasiIndex];
  float suhu = dht.readTemperature();
  if (isnan(suhu)) {
    delay(1000);
    suhu = dht.readTemperature();
  }

  int asap = analogRead(MQ2PIN);
  String status = "Aman";
  if (asap >= 201 && asap <= 300) status = "Siaga";
  else if (asap >= 301 && asap <= 500) status = "Bahaya";
  else if (asap >= 501) status = "Darurat";

  // Serial monitor
Serial.print("Suhu: "); Serial.print(suhu);
Serial.print(" C | Asap: "); Serial.print(asap);
Serial.print(" | Status: "); Serial.print(status);
Serial.print(" | Lokasi: "); Serial.println(lokasi);

  // Kirim notifikasi Telegram
   if ((asap >= 500 || suhu >= 31) && WiFi.status() == WL_CONNECTED) {
    if (!alreadyNotified || millis() - lastNotifyTime >= notifyInterval) {
      String notifMsg = "🚨 PERINGATAN! 🚨\n\n";
      notifMsg += "🌡 Suhu: " + String(suhu, 1) + " °C\n";
      notifMsg += "🌫 Asap: " + String(asap) + " ppm\n";
      notifMsg += "📍 Lokasi: " + String(lokasi) + "\n";
      notifMsg += "Status: " + status + "\n\nSegera cek lokasi!";

      bool sent = bot.sendMessage(CHAT_ID, notifMsg, "Markdown");

      if (sent) {
        Serial.println("✅ Notifikasi Telegram berhasil dikirim.");
        alreadyNotified = true;
        lastNotifyTime = millis();
      } else {
        Serial.println("❌ Gagal mengirim notifikasi Telegram!");
      }
    }
  } else {
    alreadyNotified = false;
  }
  // Buzzer & LED indikator dengan blinking (berulang tanpa delay)
unsigned long currentMillis = millis();

if (suhu > 31 && asap > 500) {
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis;
    buzzerState = !buzzerState;
    digitalWrite(BUZZER, buzzerState ? HIGH : LOW);
  }
  digitalWrite(LED_MERAH, HIGH);
  digitalWrite(LED_BIRU, LOW);

} else if (asap > 1000) {
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis;
    buzzerState = !buzzerState;
    digitalWrite(BUZZER, buzzerState ? HIGH : LOW);
  }
  digitalWrite(LED_MERAH, HIGH);
  digitalWrite(LED_BIRU, LOW);

} else if (asap >= 500) {
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis;
    buzzerState = !buzzerState;
    digitalWrite(BUZZER, buzzerState ? HIGH : LOW);
  }
  digitalWrite(LED_MERAH, HIGH);
  digitalWrite(LED_BIRU, LOW);

} else if (suhu > 31 && suhu < 100) {
  if (currentMillis - previousMillis >= interval) {
    previousMillis = currentMillis;
    buzzerState = !buzzerState;
    digitalWrite(BUZZER, buzzerState ? HIGH : LOW);
  }
  digitalWrite(LED_MERAH, HIGH);
  digitalWrite(LED_BIRU, LOW);

} else {
  // Kondisi aman
  digitalWrite(BUZZER, LOW);
  digitalWrite(LED_MERAH, LOW);
  digitalWrite(LED_BIRU, HIGH);
}
  // LCD tampilan
  lcd.setCursor(0, 0);
  lcd.print("Suhu: ");
  lcd.print(!isnan(suhu) ? String(suhu) + " C " : "Error     ");

  lcd.setCursor(0, 1);
  lcd.print("Asap: ");
  lcd.print(asap);
  lcd.print(" ppm     ");

 // Kirim ke server lokal
if (millis() - lastDataSendTime >= dataSendInterval) {
  WiFiClient client;
  HTTPClient http;

  http.begin(client, "http://192.168.43.144/Monitoring/kirim.php");
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  String postData = "suhu=" + String(suhu, 2) + "&kadar_asap=" + String(asap) + "&status=" + status + "&lokasi=" + String(lokasi);

  int httpCode = http.POST(postData);

  if (httpCode > 0) {
    Serial.print("Kode Respon HTTP: ");
    Serial.println(httpCode);
    String payload = http.getString();
    Serial.println(payload);

    if (httpCode == 200) {
      Serial.println("Berhasil mengirim data!");
      Serial.println("Data: Suhu=" + String(suhu, 2) + "°C, Asap=" + String(asap) + ", Status=" + status);
    }
  } else {
    Serial.println("Gagal mengirim data.");
  }

  http.end();
  lastDataSendTime = millis();
}
  // Cek pesan baru dari Telegram
  if (millis() > lastTimeBotRan + botRequestDelay) {
    int numNewMessages = bot.getUpdates(bot.last_message_received + 1);
    while (numNewMessages) {
      handleNewMessages(numNewMessages);
      numNewMessages = bot.getUpdates(bot.last_message_received + 1);
    }
    lastTimeBotRan = millis();
  }

  delay(5000);
}