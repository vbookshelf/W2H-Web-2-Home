# Web2Home (W2H)
Interact with your home server and IoT devices using a simple self-hosted web interface.

Currently to interact remotely with your home server or to send remote instructions to your IoT devices you need to use third party services like Tailscale, AWS IoT, or mobile apps.

W2H gives you full control over your infrastructure. It uses a polling-based architecture where your home server peridocally fetches instructions and messages from a website that you build and host on hosting platforms like Dreamhost or Host Gator. This allows you to interact with your home server via your website while keeping your home server insulated from the rest of the internet. The downside is some additional latency and streaming is not supported. 

<br>

## How does it work?

Think of your computer at home (home server) as a person sitting inside a secure, locked house, and your website as a mailbox on the street. Your home internet router blocks outsiders from knocking on your door (which is standard for home networks). 

With w2h, your computer uses a simple method to receive messages and instructions from you when you are not at home:

- Checking the Mailbox: Every few seconds, your computer steps outside (by sending an outgoing request) to check your website to see if you've sent any messages, commands or files via your website.

- Dropping Off Updates: While it's checking the mailbox, your computer can also leave a status report or update inside the mailbox so your web interface knows what the status is at your home.

- Bringing it Back Inside: If there's a command waiting in the mailbox (e.g. turn on the light), your computer picks it up, brings it back inside the house, executes it, and saves the results. It then goes back outside to the mailbox and leaves a message saying that the instruction has been executed (e.g. the light has been turned on successfully).

Your computer (home server) never has to open its doors to the public internet. It only reaches out to your website to get instructions and messages from you. To ensure that only you can access your website, w2h uses a simple token based access control system, instead of a complicated registration and login system.

<br>

