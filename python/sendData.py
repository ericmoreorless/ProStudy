import serial
import pycurl
import StringIO
import re
import time
import requests
ser = serial.Serial('/dev/ttyACM0', 9600)

sensorDataOld = '0'
sensorDataNew = '1'

try:
    while 1 :
        sensorDataNew = ser.readline()
        if(sensorDataNew != sensorDataOld) :                
            sensorList = sensorDataNew.split(',')
            urlToSend = 'http://erictest.strivemanagementgroup.com/prostudy/piFeed.php'
            payload = {'l':re.sub('[^0-9]', '', sensorList[1]), 'm':re.sub('[^0-9]', '', sensorList[2]), 'd':1}
            r = requests.get(urlToSend, params=payload)
            print(r.url)
            print (r.text)
        sensorDataOld = sensorDataNew
        
except KeyboardInterrupt:
    print 'terminating'
