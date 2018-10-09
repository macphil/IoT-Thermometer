#include "DHTesp.h"
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include "config.h"

DHTesp dht;
WiFiClient client;

const char* ssid = WLANSSID;
const char* password = WLANPASS;
const char* apiurl = APIURL;

void setup()
{
  Serial.begin(115200);

  dht.setup(5); // Connect DHT sensor to GPIO 5 (D1)
  Serial.print("Connecting to ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    digitalWrite(LED_BUILTIN, LOW);
    delay(200);
    digitalWrite(LED_BUILTIN, HIGH);
    Serial.print(".");
  }
  digitalWrite(LED_BUILTIN, LOW);
  Serial.println("");
  Serial.println("WiFi connected");
}

void loop()
{
  float humidity = dht.getHumidity();
  float temperature = dht.getTemperature();

  String json = CreateLogJson(temperature, humidity);

  Serial.print("Json to POST:");
  Serial.println(json);

  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(apiurl);
    http.addHeader("Accept", "application/json");
    http.addHeader("Content-Type", "application/json");
    int httpCode = http.POST(json);
    String payload = http.getString();

    Serial.println(httpCode);   //Print HTTP return code
    Serial.println(payload);    //Print request response payload

    http.end();  //Close connection

  } else {
    Serial.println("Error in WiFi connection");
  }

  int sleepSeconds = 60;
  Serial.println("Waiting…");
  delay(sleepSeconds * 1000);  //Send a request every 60 seconds
}


String CreateLogJson(float temperature, float humidity) {

  String humidityString = String(dht.getHumidity(), DEC);
  String tempString = String(dht.getTemperature(), DEC);

  String jsonString = "{ \r\n";
  jsonString += "\t\"name\" : \"DHT22_NodeMCU\", \r\n";
  jsonString += "\t\"temperature\" : \"" + tempString + "\",\r\n";
  jsonString += "\t\"humidity\" : \"" + humidityString + "\"\r\n";
  jsonString += "}\r\n";

  return jsonString;
}


