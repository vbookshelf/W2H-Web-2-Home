# Web-2-Home Concept (W2H)
Interact with your home server and it's connected IoT devices from anywhere by sending instructions from your website. No Tailscale, Amazon IoT or other third party apps required.

Tech stack:<br>
Python + Html + CSS + JS + PHP

<br>

<img src="images/image2.png" alt="App screenshot" height="500">
<p>Remotely chat with the agent running on your home server</p>

<br>

<img src="images/image3.png" alt="App screenshot" height="500">
<p>Remotely control connected devices</p>

<br>

<img src="images/image1.jpg" alt="Arduino Nano connected to Macbook Air via USB." height="350">
<p>This Arduino LED can be switched on and off from your website.<br>
Sensors, motors and other electronic devices can be controlled from your computer using the I/O (input/ouput) pins on the Arduino.</p>

<br>

## How does it work?

Basic idea: Your home server (just a normal computer) uses a polling method to receive messages and instructions from you via your website. Polling simply means that it makes a request to your website every few seconds i.e. it knocks on the door and asks: "Are there any messages for me."

By default, your home network blocks anything on the internet from coming in. With w2h, every few seconds your computer sends an outgoing request to your website to see if you've sent it any messages, commands or files. This allows your home server to receive instructions without compromising the security of your network. In other words, when you issue an instruction via your website, the instruction does not immediately go to your home server. It waits until your home server calls in and collects the instruction, which it then executes.

To ensure that only you can access your website, w2h uses a simple token based access control system.

<br>

## Periodic Polling vs Long Polling

The w2h system uses long polling.

Periodic polling:<br>
The home server goes to your website, checks for messages, and walks away — every 2 seconds, forever. Most of the time there are no messages, so it's a wasted trip. And if a mesage arrives right after a check, your server doesn't find out for almost 2 more seconds (the next scheduled check).

Long polling:<br>
The home server goes to your website and says "I'll just wait here for 20 seconds or until something arrives." The website (web server) holds the door open and keeps checking for messages every 0.3 seconds. If a message arrives, the website hands it over. If nothing shows up after 20 seconds, the web server finally says "Come back later." The home server leaves, comes back after two seconds, and waits again.

With long polling, request frequency is reduced and connection duration is increased. The net effect is to reduce the latency associated with periodic polling.

<br>

## Downsides of the w2h system

- Streaming is not possible.
- Latency associated with polling may be higher than the P2P connection that third party services provide.


<br>

## What you can do with the example app

Use the web browser on your phone to send instructions to your laptop (home server).

- Send messages from a mobile chat interface and get dummy responses from the code running on your laptop.
- Use the control panel to press a button that remotely turns an Arduino LED on and off.
- Send files from your phone to your laptop.
- Check the latency of this approach.
- See the token access control system in action.

<br>
