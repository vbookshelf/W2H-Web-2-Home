

// Define the built-in LED pin for clarity
const int ledPin = LED_BUILTIN; 


void setup() {

  Serial.begin(9600);
  
  // Initialize the LED pin as an output
  pinMode(ledPin, OUTPUT);

  // Ensure LED is off at the start
  digitalWrite(ledPin, LOW);

}

void loop() {

   // ALWAYS-ON SERIAL COMMAND LISTENER 
  if (Serial.available() > 0) {
    
    String command = Serial.readStringUntil('\n');
    command.trim();
  
    // Handle the command to turn the LED ON
    if (command.equals("LED_ON")) {
      digitalWrite(ledPin, HIGH);
      Serial.println("OK: LED turned on"); // Send confirmation
    }
    // Handle the command to turn the LED OFF
    else if (command.equals("LED_OFF")) {
      digitalWrite(ledPin, LOW);
      Serial.println("OK: LED turned off"); // Send confirmation
    }
      
  }

}
