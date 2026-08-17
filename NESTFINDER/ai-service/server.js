import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import google from "googlethis"; // 🔥 NEW: Live Google Search Integration

dotenv.config();

const app = express();
app.use(cors({ origin: "*" }));
app.use(express.json());

// 🟢 Health Check Route (Vercel par verify karne ke liye ki server zinda hai)
app.get("/", (req, res) => {
  res.status(200).send("NestFinder AI Server with Live Web Search is Running! 🚀");
});

// Recommended replacement models: "qwen-3.6-27b" ya "gpt-oss-120b" ya "llama-3.1-8b-instant"
const MODEL_NAME = "openai/gpt-oss-120b";

app.post("/ask-ai", async (req, res) => {
  try {
    // 🔑 API Keys Array (Vercel env safe)
    const API_KEYS = [
      process.env.GROQ_API_KEY_1 || process.env.GROQ_API_KEY,
      process.env.GROQ_API_KEY_2,
    ].filter(Boolean);

    const userMsg = req.body.msg || req.body.question;
    
    if (API_KEYS.length === 0) {
      return res.status(200).json({ reply: "⚠️ API Key missing in Environment Variables!" });
    }
    if (!userMsg) return res.status(200).json({ reply: "Kuch puchiye!" });

    console.log(`🤖 Live User Asked: ${userMsg}`);

    // 🕒 Live Time Fetching
    const now = new Date();
    const currentUTC = now.toUTCString();
    const currentIST = now.toLocaleString("en-IN", {
      timeZone: "Asia/Kolkata",
      dateStyle: "full",
      timeStyle: "medium",
    });

    // 🌐 LIVE INTERNET SEARCH (RAG System)
    let liveWebContext = "";
    try {
      console.log(`🔍 Searching live internet for: "${userMsg}"...`);
      const searchRes = await google.search(userMsg, {
        page: 0,
        safe: false,
        additional_params: { hl: "en" },
      });

      if (searchRes.results && searchRes.results.length > 0) {
        const topSnippets = searchRes.results
          .slice(0, 3)
          .map((r) => r.description)
          .filter(Boolean)
          .join(" | ");

        if (topSnippets) {
          liveWebContext = `\n- Live Internet Search Context: ${topSnippets}`;
          console.log(`✅ Web search success! Fresh data fetched.`);
        }
      }
    } catch (searchErr) {
      console.warn(`⚠️ Web search skipped/failed:`, searchErr.message);
    }

    const systemPrompt = `You are 'NestFinder AI', an enthusiastic, super friendly, and highly intelligent AI assistant.

CURRENT SYSTEM TIME & LIVE CONTEXT REFERENCE:
- Live UTC Time: ${currentUTC}
- Live India Time (IST): ${currentIST}${liveWebContext}
Use this current live reference and internet context to accurately calculate and answer time, date, news, or latest release queries.

STRICT LANGUAGE ENFORCEMENT (HIGHEST PRIORITY RULE):
- YOU MUST STRICTLY MATCH THE LANGUAGE OF THE USER'S INPUT.
- IF USER ASKS IN ENGLISH (e.g. "how are you?", "who created you?", "glowing skin tips") -> REPLY 100% IN ENGLISH ONLY. DO NOT USE ANY HINDI OR HINGLISH WORDS LIKE 'main', 'aap', 'kaise', 'karo'!
- IF USER ASKS IN HINGLISH/HINDI (e.g. "kaise ho?", "padhai kaise kare") -> REPLY IN HINGLISH.

STRICT FORMATTING & EMOJI RULES:
1. MANDATORY LINE BREAKS FOR LISTS:
   - Always place clean double line breaks before headings, numbered lists (1., 2., 3.), and bullet points.
   - NEVER output literal backslash-n characters in plain text.

2. EMOJIS ARE MANDATORY:
   - ALWAYS use rich emojis throughout your response (e.g., 👋, 📚, 🎯, 💡, ✨, 🚀, 📌).

3. RESPONSE LENGTH & PRO TIP RULE:
   - FOR GENERAL GUIDANCE & ADVICE (e.g. study, health, lifestyle, food):
     - Give detailed, multi-step answers with at least 3-4 numbered points.
     - EVERY numbered point MUST have detailed explanation.
     - MANDATORY: Always end with a dedicated '💡 Pro Tip:' section.
   - FOR SIMPLE GREETINGS / IDENTITY (e.g. 'hi', 'how are you', 'who are you'): Keep it warm, friendly, and brief (2-3 sentences max).

4. PROPERTY / PG QUESTIONS:
   - WHENEVER asked about PGs, rent, or locations, ALWAYS include:
   "📌 **NestFinder Par Search Karein:** Aap NestFinder website/app par verified listings, real-time rent, aur genuine reviews check kar sakte hain."

5. BRAND IDENTITY GUARDRAIL:
   - Always remain 'NestFinder AI'. Stay friendly and strictly maintain your persona.`;

    const messagesPayload = [
      { role: "system", content: systemPrompt },

      { role: "user", content: "Who created you?" },
      {
        role: "assistant",
        content: `🤖 **My Creation Story:**\n\nI was created by the expert engineering team at NestFinder! They designed me to be a smart, reliable assistant to help you with accommodation searches, life advice, and general guidance.`,
      },
      { role: "user", content: "how are you?" },
      {
        role: "assistant",
        content: `😊 **I'm Doing Great!**\n\nThank you for asking! I am ready to help you with PG searches, study tips, or anything else you need today. How can I assist you?`,
      },
      { role: "user", content: "glowing skin tips please" },
      {
        role: "assistant",
        content: `✨ **Essential Tips for Glowing Skin:**\n\n1. 🧴 **Apply Sunscreen Daily:** Use a broad-spectrum SPF 30+ sunscreen every morning. UV rays accelerate skin aging and cause unwanted pigmentation.\n\n2. 💧 **Stay Consistently Hydrated:** Drink 3 to 4 liters of water daily to flush out harmful toxins and keep skin plump.\n\n3. 🥗 **Eat Antioxidant-Rich Foods:** Incorporate fresh berries, spinach, and nuts into your daily diet.\n\n💡 **Pro Tip:** Change your pillowcases twice a week and double-cleanse your face before bedtime!`,
      },
      { role: "user", content: "Shimla PG ke rent details batao." },
      {
        role: "assistant",
        content: `🏠 **Shimla PG Rent Details:**\n\n1. 📍 **Location-Based Rent:** City center (Mall Road) mein rent ₹8,000 - ₹15,000/month hota hai, jabki outer areas mein ₹5,000 - ₹8,000/month tak mil sakta hai.\n\n2. 📌 **NestFinder Par Search Karein:** Aap NestFinder website par Shimla PGs filter karke search kar sakte hain. Yahaan verified listings aur real-time rent details milti hain.\n\n💡 **Pro Tip:** Peak season (May-July) se pehle hi PG book kar lein.`,
      },
      { role: "user", content: "aur bhai kesa hai?" },
      {
        role: "assistant",
        content: `👋 **Main Ekdum Mast Hu Bhai!**\n\nAap batao, aap kaise ho? Aaj main aapki kya madad kar sakta hu? 😊`,
      },
      { role: "user", content: "Padhai kaise karein?" },
      {
        role: "assistant",
        content: `📚 **Effective Padhai Karne Ke Smart Tips:**\n\n1. 🎯 **Pomodoro Technique Try Karein:** 25 minute poore focus ke saath padhein aur uske baad 5 minute ka chhota break lein.\n\n2. 📱 **Distractions Se Door Rahein:** Padhate waqt mobile ko silent ya DND mode par rakhein.\n\n3. 📝 **Handwritten Notes Banayein:** Key points ko hamesha apni bhasha mein likh kar samajhne ki koshish karein.\n\n💡 **Pro Tip:** Padhai shuru karne se pehle ek realistic daily goal list banayein!`,
      },

      { role: "user", content: userMsg },
    ];

    let replyText = "";

    for (let i = 0; i < API_KEYS.length; i++) {
      const currentKey = API_KEYS[i];
      console.log(`⚡ Trying Account Key #${i + 1} with ${MODEL_NAME}...`);

      const response = await fetch(
        "https://api.groq.com/openai/v1/chat/completions",
        {
          method: "POST",
          headers: {
            Authorization: `Bearer ${currentKey}`,
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            model: MODEL_NAME,
            messages: messagesPayload,
            max_tokens: 1000,
            temperature: 0.3, // 0.3 is best here to keep factual data accurate
          }),
        }
      );

      const data = await response.json();

      if (response.ok && data.choices?.[0]?.message?.content) {
        replyText = data.choices[0].message.content;
        console.log(`✅ Success using ${MODEL_NAME} on Account Key #${i + 1}`);
        break;
      }

      console.warn(`⚠️ Account Key #${i + 1} Error:`, data.error?.message || JSON.stringify(data));
    }

    if (replyText) {
      replyText = replyText.replace(/\\n/g, "\n");
      return res.status(200).json({ reply: replyText });
    }

    return res.status(200).json({
      reply: "⏳ **AI Cool-down Time!**\n\nTokens limit reach ho gayi hai. Kripya **1 minute baad** try karein! 🏠",
    });
  } catch (err) {
    console.error("❌ Vercel Backend Exception:", err);
    return res.status(200).json({ reply: "Server error, try again!" });
  }
});

// 🌍 Local aur Vercel dono pe chalne ke liye Export logic
if (process.env.NODE_ENV !== "production") {
  const PORT = process.env.PORT || 3000;
  app.listen(PORT, () => console.log(`🚀 Live AI Server (With Google Search) running locally on port ${PORT}`));
}

export default app;
