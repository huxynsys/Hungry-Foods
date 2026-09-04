<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zara AI Assistant | Hungry Food</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B35;
            --primary-dark: #E55A2B;
            --secondary: #4ECDC4;
            --dark: #1a1a2e;
            --dark2: #16213e;
            --card: #0f3460;
            --text: #e0e0e0;
            --text-muted: #9e9e9e;
            --bubble-user: linear-gradient(135deg, #FF6B35, #E55A2B);
            --bubble-bot: rgba(255,255,255,0.07);
            --border: rgba(255,255,255,0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--dark);
            color: var(--text);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* TOP BAR */
        .topbar {
            background: var(--dark2);
            border-bottom: 1px solid var(--border);
            padding: 0.9rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .topbar-left { display: flex; align-items: center; gap: 1rem; }
        .back-btn {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: color 0.2s;
        }
        .back-btn:hover { color: var(--primary); }
        .zara-avatar {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 0 15px rgba(255,107,53,0.4);
            animation: pulse-glow 2s infinite;
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 15px rgba(255,107,53,0.4); }
            50% { box-shadow: 0 0 25px rgba(255,107,53,0.7); }
        }
        .zara-info h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            margin: 0;
            color: white;
        }
        .online-dot {
            display: flex; align-items: center; gap: 0.4rem;
            font-size: 0.75rem; color: #4caf50;
        }
        .online-dot::before {
            content: '';
            width: 7px; height: 7px;
            background: #4caf50;
            border-radius: 50%;
            animation: blink 1.5s infinite;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .clear-btn {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text-muted);
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .clear-btn:hover { background: rgba(255,107,53,0.15); color: var(--primary); border-color: var(--primary); }

        /* CHAT AREA */
        .chat-area {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            scroll-behavior: smooth;
        }
        .chat-area::-webkit-scrollbar { width: 4px; }
        .chat-area::-webkit-scrollbar-track { background: transparent; }
        .chat-area::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        /* MESSAGES */
        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 0.6rem;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .msg-row.user { flex-direction: row-reverse; }

        .msg-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        .msg-avatar.bot { background: linear-gradient(135deg, var(--primary), var(--secondary)); }
        .msg-avatar.user { background: rgba(255,255,255,0.1); }

        .bubble {
            max-width: 75%;
            padding: 0.8rem 1.1rem;
            border-radius: 18px;
            font-size: 0.9rem;
            line-height: 1.6;
        }
        .bubble.bot {
            background: var(--bubble-bot);
            border: 1px solid var(--border);
            border-bottom-left-radius: 4px;
            color: var(--text);
        }
        .bubble.user {
            background: var(--bubble-user);
            border-bottom-right-radius: 4px;
            color: white;
        }
        .bubble p { margin: 0 0 0.4rem; }
        .bubble p:last-child { margin: 0; }
        .bubble ul { padding-left: 1.2rem; margin: 0.4rem 0 0; }
        .bubble ul li { margin-bottom: 0.2rem; }

        /* QUICK REPLIES */
        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
            padding-left: 38px;
        }
        .quick-btn {
            background: rgba(255,107,53,0.1);
            border: 1px solid rgba(255,107,53,0.3);
            color: var(--primary);
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'DM Sans', sans-serif;
        }
        .quick-btn:hover { background: var(--primary); color: white; }

        /* TYPING INDICATOR */
        .typing-indicator {
            display: none;
            align-items: flex-end;
            gap: 0.6rem;
        }
        .typing-indicator.show { display: flex; }
        .typing-dots {
            background: var(--bubble-bot);
            border: 1px solid var(--border);
            border-bottom-left-radius: 4px;
            border-radius: 18px;
            padding: 0.8rem 1.1rem;
            display: flex;
            gap: 4px;
            align-items: center;
        }
        .typing-dots span {
            width: 7px; height: 7px;
            background: var(--text-muted);
            border-radius: 50%;
            animation: bounce 1.2s infinite;
        }
        .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
        .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes bounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-6px); }
        }

        /* INPUT BAR */
        .input-bar {
            background: var(--dark2);
            border-top: 1px solid var(--border);
            padding: 1rem 1.5rem;
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-shrink: 0;
        }
        .input-bar input {
            flex: 1;
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--border);
            border-radius: 25px;
            padding: 0.75rem 1.25rem;
            color: white;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .input-bar input::placeholder { color: var(--text-muted); }
        .input-bar input:focus { border-color: rgba(255,107,53,0.5); }
        .send-btn {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        .send-btn:hover { transform: scale(1.1); box-shadow: 0 4px 15px rgba(255,107,53,0.5); }
        .send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        /* WELCOME SCREEN */
        .welcome-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            justify-content: center;
            margin-top: 1rem;
        }
        .welcome-chip {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.82rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .welcome-chip:hover { background: rgba(255,107,53,0.15); border-color: var(--primary); color: var(--primary); }

        @media (max-width: 576px) {
            .bubble { max-width: 90%; }
            .chat-area { padding: 1rem; }
        }
    </style>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
    <div class="topbar-left">
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back</a>
        <div class="zara-avatar">🍽️</div>
        <div class="zara-info">
            <h6>Zara</h6>
            <div class="online-dot">Online now</div>
        </div>
    </div>
    <button class="clear-btn" onclick="clearChat()"><i class="fas fa-rotate-right me-1"></i>New Chat</button>
</div>

<!-- CHAT AREA -->
<div class="chat-area" id="chatArea">

    <!-- Welcome message -->
    <div class="msg-row bot" id="welcomeMsg">
        <div class="msg-avatar bot">🍽️</div>
        <div>
            <div class="bubble bot">
                <p>Hey there! 👋 I'm <strong>Zara</strong>, your Hungry Food assistant!</p>
                <p>I can help you with our menu, prices, reservations, orders, catering and more. What can I get for you today? 😊</p>
            </div>
            <div class="welcome-chips">
                <span class="welcome-chip" onclick="sendQuick('Show me the menu')">🍔 View Menu</span>
                <span class="welcome-chip" onclick="sendQuick('What are your prices?')">💰 Prices</span>
                <span class="welcome-chip" onclick="sendQuick('How can I make a reservation?')">📅 Reservations</span>
                <span class="welcome-chip" onclick="sendQuick('Tell me about catering')">🎉 Catering</span>
                <span class="welcome-chip" onclick="sendQuick('What are your opening hours?')">🕐 Hours</span>
                <span class="welcome-chip" onclick="sendQuick('How do I place an order?')">🛒 Order Now</span>
            </div>
        </div>
    </div>

    <!-- Typing indicator -->
    <div class="typing-indicator" id="typingIndicator">
        <div class="msg-avatar bot">🍽️</div>
        <div class="typing-dots">
            <span></span><span></span><span></span>
        </div>
    </div>

</div>

<!-- INPUT BAR -->
<div class="input-bar">
    <input type="text" id="userInput" placeholder="Ask me anything about Hungry Food..." autocomplete="off">
    <button class="send-btn" id="sendBtn" onclick="sendMessage()">
        <i class="fas fa-paper-plane"></i>
    </button>
</div>

<script>
// ─── KNOWLEDGE BASE ───────────────────────────────────────────────
const KB = {
    menu: {
        keywords: ['menu','food','eat','serve','dishes','items','offer','available','have','burger','pizza','pasta','bbq','sandwich','dessert','drink','beverage','chicken','beef','what do you'],
        response: () => ({
            text: `Here's what we serve at Hungry Food 🍽️`,
            list: ['🍔 Burgers & Wraps','🍕 Pizzas','🍝 Pasta & Noodles','🥩 BBQ & Grills','🥪 Sandwiches & Subs','🍗 Fried Chicken','🍰 Desserts & Sweets','🥤 Drinks & Beverages','🍛 Pakistani & Continental'],
            suffix: 'Visit our full menu page for details, photos and prices!',
            quickReplies: ['View full menu', 'What are your prices?', 'How to order?']
        })
    },
    prices: {
        keywords: ['price','cost','how much','expensive','cheap','affordable','pkr','usd','rate','charges','fee'],
        response: () => ({
            text: `Our prices are very affordable 💰`,
            list: ['🍔 Burgers: $3.50 – 9.00','🍕 Pizzas: $7.00 – 18.00','🍝 Pasta: $4.50 – 9.50','🥩 BBQ Platter: $12.00 – 35.00','🥤 Drinks: $1.00 – 3.50','🍰 Desserts: $2.00 – 6.00'],
            suffix: 'Prices may vary. Check our menu page for the latest pricing!',
            quickReplies: ['View full menu', 'Do you have deals?', 'How to order?']
        })
    },
    order: {
        keywords: ['order','buy','purchase','place order','delivery','deliver','home','cart','checkout','how to get'],
        response: () => ({
            text: `Ordering from Hungry Food is easy! 🛒`,
            list: ['1️⃣ Go to our Menu page','2️⃣ Add items to your cart','3️⃣ Proceed to checkout','4️⃣ Enter your details','5️⃣ Confirm your order!'],
            suffix: 'We also take orders via WhatsApp for quick service 📱',
            quickReplies: ['Go to menu', 'Delivery info', 'Contact us']
        })
    },
    reservation: {
        keywords: ['reservation','reserve','book','table','seat','dine','dine-in','sit','booking'],
        response: () => ({
            text: `Making a reservation is simple 📅`,
            list: ['✅ Visit our Reservation page','✅ Choose your date & time','✅ Select number of guests','✅ Add any special requests','✅ Submit — we\'ll confirm shortly!'],
            suffix: 'You can also call or WhatsApp us to reserve your table.',
            quickReplies: ['Book a table', 'Opening hours', 'Contact us']
        })
    },
    catering: {
        keywords: ['catering','cater','event','party','wedding','corporate','bulk','large','group','function','occasion'],
        response: () => ({
            text: `We offer full catering services for all occasions 🎉`,
            list: ['🎂 Weddings & Engagements','🏢 Corporate Events','🎈 Birthday Parties','🎓 Graduation Parties','👨‍👩‍👧 Family Gatherings','🍽️ Custom menus available'],
            suffix: 'Visit our Catering page or contact us directly to discuss your event!',
            quickReplies: ['Catering page', 'Contact us', 'Pricing info']
        })
    },
    hours: {
        keywords: ['hours','timing','open','close','when','time','schedule','available','working hours','day'],
        response: () => ({
            text: `We're open almost every day! 🕐`,
            list: ['📅 Monday – Thursday: 11 AM – 11 PM','📅 Friday – Saturday: 11 AM – 12 AM','📅 Sunday: 12 PM – 10 PM'],
            suffix: 'Hours may change on public holidays. Call us to confirm! 📞',
            quickReplies: ['Make a reservation', 'Contact us', 'How to order?']
        })
    },
    delivery: {
        keywords: ['delivery','deliver','home delivery','free delivery','delivery time','delivery charge','delivery fee','how long'],
        response: () => ({
            text: `Here's our delivery info 🚗`,
            list: ['🕐 Delivery time: 30–60 minutes','📍 Delivery available in nearby areas','💸 Delivery charges may apply based on distance','📱 Order via website or WhatsApp'],
            suffix: 'Contact us on WhatsApp for the fastest order processing!',
            quickReplies: ['Place an order', 'Contact on WhatsApp', 'View menu']
        })
    },
    deals: {
        keywords: ['deal','offer','discount','promo','promotion','special','combo','bundle','sale','coupon','voucher'],
        response: () => ({
            text: `Check out our current deals! 🔥`,
            list: ['🍔 Burger + Drink Combo – Save $1.00','🍕 Family Pizza Deal – 2 Pizzas + 2 Drinks','🥩 BBQ Weekend Special – Every Fri & Sat','🎂 Birthday Discount – 10% off on your birthday!'],
            suffix: 'Deals change regularly — follow us or check our menu page for the latest!',
            quickReplies: ['View menu', 'Place an order', 'Contact us']
        })
    },
    contact: {
        keywords: ['contact','reach','phone','call','email','whatsapp','address','location','where','find','social','instagram','facebook'],
        response: () => ({
            text: `Here's how to reach us 📞`,
            list: ['📱 WhatsApp: Available on our contact page','📧 Email: Available on our contact page','📍 Location: Visit our contact page for address','📘 Facebook & Instagram: @HungryFood'],
            suffix: 'The fastest way to reach us is via WhatsApp!',
            quickReplies: ['Visit contact page', 'Make a reservation', 'Place an order']
        })
    },
    greeting: {
        keywords: ['hi','hello','hey','assalam','salam','good morning','good evening','good afternoon','howdy','sup','whats up','what\'s up'],
        response: () => ({
            text: `Hello! 👋 Great to meet you! I'm Zara, your Hungry Food assistant. How can I help you today?`,
            list: [],
            suffix: '',
            quickReplies: ['View menu', 'Make a reservation', 'Place an order', 'Contact us']
        })
    },
    thanks: {
        keywords: ['thank','thanks','thankyou','thank you','great','awesome','perfect','wonderful','amazing','helpful','nice','good'],
        response: () => ({
            text: `You're welcome! 😊 Happy to help anytime. Is there anything else I can assist you with?`,
            list: [],
            suffix: '',
            quickReplies: ['View menu', 'Place an order', 'Contact us']
        })
    },
    bye: {
        keywords: ['bye','goodbye','see you','later','cya','good night','ok thanks','that\'s all','that is all'],
        response: () => ({
            text: `Thanks for chatting! 😊 Come back anytime you're hungry. See you soon at Hungry Food! 🍽️🧡`,
            list: [],
            suffix: '',
            quickReplies: ['View menu', 'Place an order']
        })
    }
};

// Quick reply links
const pageLinks = {
    'View full menu': 'menu.php',
    'Go to menu': 'menu.php',
    'Catering page': 'catering.php',
    'Visit contact page': 'contact.php',
    'Book a table': 'reservation.php'
};

// ─── CHAT LOGIC ───────────────────────────────────────────────────
let chatHistory = [];

function getResponse(msg) {
    const lower = msg.toLowerCase().trim();

    // Match against knowledge base
    for (const key in KB) {
        const entry = KB[key];
        if (entry.keywords.some(kw => lower.includes(kw))) {
            return entry.response();
        }
    }

    // Default fallback
    return {
        text: `I'm not sure about that, but I'm here to help with Hungry Food! 😊`,
        list: [],
        suffix: 'Try asking me about our menu, prices, reservations, or delivery.',
        quickReplies: ['View menu', 'Make a reservation', 'Contact us', 'Our deals']
    };
}

function buildBubble(data, isUser = false) {
    const row = document.createElement('div');
    row.className = `msg-row ${isUser ? 'user' : 'bot'}`;

    const avatar = document.createElement('div');
    avatar.className = `msg-avatar ${isUser ? 'user' : 'bot'}`;
    avatar.innerHTML = isUser ? '<i class="fas fa-user"></i>' : '🍽️';

    const right = document.createElement('div');

    const bubble = document.createElement('div');
    bubble.className = `bubble ${isUser ? 'user' : 'bot'}`;

    if (isUser) {
        bubble.textContent = data;
    } else {
        let html = `<p>${data.text}</p>`;
        if (data.list && data.list.length) {
            html += '<ul>' + data.list.map(i => `<li>${i}</li>`).join('') + '</ul>';
        }
        if (data.suffix) html += `<p style="margin-top:0.5rem;color:#aaa;font-size:0.85rem;">${data.suffix}</p>`;
        bubble.innerHTML = html;
    }

    right.appendChild(bubble);

    // Quick replies
    if (!isUser && data.quickReplies && data.quickReplies.length) {
        const qr = document.createElement('div');
        qr.className = 'quick-replies';
        data.quickReplies.forEach(label => {
            const btn = document.createElement('button');
            btn.className = 'quick-btn';
            btn.textContent = label;
            if (pageLinks[label]) {
                btn.onclick = () => window.location.href = pageLinks[label];
            } else {
                btn.onclick = () => sendQuick(label);
            }
            qr.appendChild(btn);
        });
        right.appendChild(qr);
    }

    if (isUser) {
        row.appendChild(right);
        row.appendChild(avatar);
    } else {
        row.appendChild(avatar);
        row.appendChild(right);
    }

    return row;
}

function scrollBottom() {
    const area = document.getElementById('chatArea');
    area.scrollTop = area.scrollHeight;
}

function showTyping() {
    document.getElementById('typingIndicator').classList.add('show');
    scrollBottom();
}

function hideTyping() {
    document.getElementById('typingIndicator').classList.remove('show');
}

function appendMessage(data, isUser = false) {
    const area = document.getElementById('chatArea');
    const indicator = document.getElementById('typingIndicator');
    const msg = buildBubble(data, isUser);
    area.insertBefore(msg, indicator);
    scrollBottom();
}

function sendMessage() {
    const input = document.getElementById('userInput');
    const msg = input.value.trim();
    if (!msg) return;

    input.value = '';
    document.getElementById('sendBtn').disabled = true;

    // Show user bubble
    appendMessage(msg, true);

    // Show typing
    showTyping();

    // Simulate response delay
    setTimeout(() => {
        hideTyping();
        const response = getResponse(msg);
        appendMessage(response, false);
        document.getElementById('sendBtn').disabled = false;
        input.focus();
    }, 700 + Math.random() * 500);
}

function sendQuick(text) {
    document.getElementById('userInput').value = text;
    sendMessage();
}

function clearChat() {
    const area = document.getElementById('chatArea');
    const indicator = document.getElementById('typingIndicator');
    // Remove all messages except typing indicator
    while (area.firstChild !== indicator) {
        area.removeChild(area.firstChild);
    }
    // Re-add welcome
    const welcome = buildWelcome();
    area.insertBefore(welcome, indicator);
    scrollBottom();
}

function buildWelcome() {
    const row = document.createElement('div');
    row.className = 'msg-row bot';
    row.innerHTML = `
        <div class="msg-avatar bot">🍽️</div>
        <div>
            <div class="bubble bot">
                <p>Hey there! 👋 I'm <strong>Zara</strong>, your Hungry Food assistant!</p>
                <p>I can help you with our menu, prices, reservations, orders, catering and more. What can I get for you today? 😊</p>
            </div>
            <div class="welcome-chips">
                <span class="welcome-chip" onclick="sendQuick('Show me the menu')">🍔 View Menu</span>
                <span class="welcome-chip" onclick="sendQuick('What are your prices?')">💰 Prices</span>
                <span class="welcome-chip" onclick="sendQuick('How can I make a reservation?')">📅 Reservations</span>
                <span class="welcome-chip" onclick="sendQuick('Tell me about catering')">🎉 Catering</span>
                <span class="welcome-chip" onclick="sendQuick('What are your opening hours?')">🕐 Hours</span>
                <span class="welcome-chip" onclick="sendQuick('How do I place an order?')">🛒 Order Now</span>
            </div>
        </div>`;
    return row;
}

// Enter key to send
document.getElementById('userInput').addEventListener('keydown', e => {
    if (e.key === 'Enter') sendMessage();
});
</script>
</body>
</html>
