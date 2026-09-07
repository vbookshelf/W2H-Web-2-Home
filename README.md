# Web-2-Home Concept (W2H)
Interact with your home server and IoT devices from anywhere by using a simple self-hosted web interface.

<br>

## How does it work?

Think of your computer at home (home server) as a person sitting inside a secure, locked house, and your website as a mailbox on the street. Your home internet router blocks outsiders from knocking on your door (which is standard for home networks). 

With w2h, your computer uses a simple method to receive messages and instructions from you when you are not at home:

- Checking the Mailbox: Every few seconds, your computer steps outside (by sending an outgoing request) to check your website to see if you've sent any messages, commands or files via your website.

- Dropping Off Updates: While it's checking the mailbox, your computer can also leave a status report or update inside the mailbox so your web interface knows what the status is at your home.

- Bringing it Back Inside: If there's a command waiting in the mailbox (e.g. turn on the light), your computer picks it up, brings it back inside the house, executes it, and saves the results. It then goes back outside to the mailbox and leaves a message saying that the instruction has been executed (e.g. the light has been turned on successfully).

Your computer (home server) never has to open its doors to the public internet. It only reaches out to your website to get instructions and messages from you. To ensure that only you can access your website, w2h uses a simple token based access control system, instead of a complicated registration and login system.

<br>

## What is Long Polling?

Instead of asking "anything new?" every 2 seconds and always getting an instant answer, your computer asks once and the web server sits on the answer until it's actually true (or 20 seconds pass).

Think of status.json on the server as a mailbox, and the Mac as someone waiting for mail.

Old way (periodic polling):
The Mac walks to the mailbox, checks it, and walks away — every 2 seconds, forever. Most of the time the mailbox is empty, so it's a wasted trip. And if a letter arrives right after a check, the Mac doesn't find out for almost 2 more seconds (the next scheduled trip).

New way (long polling):
The Mac walks to the mailbox and says "I'll just wait here until something arrives." The server (the mailbox) doesn't send the Mac away empty-handed — it holds the door open and keeps peeking inside every 0.3 seconds on the Mac's behalf, and only replies the instant there's actually a letter (a new chat message, an uploaded file, or an LED command). If nothing shows up after about 20 seconds, the server finally says "still nothing, try again," and the Mac immediately starts waiting again.

Instead of asking "anything new?" every 2 seconds and always getting an instant answer, it asks once and the server sits on the answer until it's actually true (or 20 seconds pass).

You've traded request frequency for connection duration, and duration is the thing to watch if this ever needs to scale to more simultaneous clients than just the one Mac and one phone.

