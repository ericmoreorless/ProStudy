//First we declare pins as they are used - these variables won't change value
#define lightSensor A0  // the light sensor is connected to analog pin 0
#define motionSensor 2  // the motion sensor is connected to digital pin 2
#define vibroSensor 3   // the vibration sensor is connected to digital pin 3
#define vibroLedPin 6   // an LED is connected to PWM pin 16
#define motionLedPin 11 // an LED is connected to PWM pin 11
#define lightLedPin 10  // an LED is connected to PWM pin 10
#define threshold 614   // a reading over this value triggers system

//Here we declare variables whose values can change
int lightReading = 0;   // holds integer values from 0 to 1023 (ADC output)
int highCount = 0;      // number of times the vibro-switch was closed
int lowCount = 0;       // number of times the vibro-switch was opened
int samples = 0;        // total number of samples
int i, x;               // counter
bool vibroReading = false;  // holds the digital value of the vibro-switch
bool motionReading = false; // holds the digital value of the motion sensor
bool dump = false;      // decides if we need to dump samples or not
bool add = true;        // decides which way we are incrementing the PWM value of the LED
bool vibration, vibe, light, motion;
String outputNew, outputOld;

void setup() {
  
  //All remaining initialization must be added here
  pinMode(lightLedPin, OUTPUT);
  pinMode(motionLedPin, OUTPUT);
  pinMode(vibroLedPin, OUTPUT);
  pinMode(vibroSensor, INPUT);
  pinMode(motionSensor, INPUT);
  Serial.begin(9600);  
}

void loop() {

  /*
   * vibration sensor
   */

  // check number of samples collected
  if(samples < 50){

    // read the vibro-switch state and store it
    vibroReading = digitalRead(vibroSensor);

    if (vibroReading){

      // switch is closed
      lowCount++;
      
    }else{

      // switch is open
      highCount++;
      
    }

    // increment samples (up to 100)
    samples++;
    
  }else{

    //100 samples reached, reset sample number and dump vars
    samples = 0;
    dump = true;

    //no vibration usualy has (low, high) = (0, 100) | therefore when these values change, we have vibration
    if(lowCount > 5 && highCount < 40){

      //vibration detected, send output
      vibration = true;

    }else{

      //no vibration detected, send output
      vibration = false;
      
    }
    
  }

  // is it time to dump variables?
  if(dump){

    //it is, set to zero and rest dump
    highCount = 0;
    lowCount = 0;
    dump = false;
    
  }

  /*
   * light sensor
   */

  // read the light sensor and store it
  lightReading = analogRead(lightSensor);

  // check if light is on
  if(lightReading < threshold){

    //light is on, send output
    light = true;
    
  }else{

    // light is off, send output
    light = false;
    
  }

  /*
  * motion sensor
  */

  //read the motion sensor state and store it
  motionReading = digitalRead(motionSensor);

  // check if there is motion
  if(motionReading){

    // motion detected, send output
    motion = true;
    
  }else{

    //no motion detected, send output
    motion = false;

  }

  if(vibration){

    analogWrite(vibroLedPin, 200); // sends pulse width

  }else{

    analogWrite(vibroLedPin, 0); // sends pulse width
    
  }

  if(light){

    analogWrite(lightLedPin, 200); // sends pulse width
    
  }else{

     analogWrite(lightLedPin, 0);//turn off led
    
  }

  if(motion){

    analogWrite(motionLedPin, 200); // sends pulse width
    
  }else{

     analogWrite(motionLedPin, 0);//turn off led
    
  }

  outputNew = vibe;
  outputNew += ", ";
  outputNew += light;
  outputNew += ", ";
  outputNew += motion;

  if(outputOld != outputNew){

     Serial.println(outputNew);
    
  }
  
  outputOld = outputNew;

  delay(10);

}
