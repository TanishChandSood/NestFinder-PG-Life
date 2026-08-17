import express from "express";
import cors from "cors";
import dotenv from "dotenv";

dotenv.config();

const app = express();
app.use(cors({ origin: "*" }));
app.use(express.json());

// 🟢 Health Check Route
app.get("/", (req, res) => {
  res.status(200).send("NestFinder AI Server with Live Tavily Search is Running! 🚀");
});

const MODEL_NAME = "openai/gpt-oss-120b";

app.post("/ask-ai", async (req, res) => {
  try {
    const API_KEYS = [
      process.env.GROQ_API_KEY_1 || process.env.GROQ_API_KEY,
      process.env.GROQ_API_KEY_2,
    ].filter(Boolean);

    const userMsg = req.body.msg || req.body.question;
    
    if (API_KEYS.length === 0) {
      return res.status(200).json({ reply: "⚠️ Groq API Key missing in Environment Variables!" });
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

    // 🌐 LIVE INTERNET SEARCH (Using TAVILY API - RAG System)
    let liveWebContext = "";
    if (process.env.TAVILY_API_KEY) {
      try {
        console.log(`🔍 Searching live internet via Tavily for: "${userMsg}"...`);
        const tavilyRes = await fetch("https://api.tavily.com/search", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            api_key: process.env.TAVILY_API_KEY,
            query: userMsg,
            search_depth: "basic",
            include_answer: false,
            max_results: 3
          })
        });

        const tavilyData = await tavilyRes.json();

        // 🟢 Check if Tavily actually found results
        if (tavilyData.results && tavilyData.results.length > 0) {
          const topSnippets = tavilyData.results.map(r => r.content).join(" | ");
          liveWebContext = `\n- Live Internet Search Context: ${topSnippets}`;
          console.log(`✅ Tavily Web search success! Fresh data fetched.`);
        } else {
          console.log(`⚠️ Tavily Web search returned empty for this query.`);
        }
      } catch (searchErr) {
        console.warn(`❌ Tavily Web search failed:`, searchErr.message);
      }
    } else {
        console.warn(`⚠️ TAVILY_API_KEY not found in env variables!`);
    }

    const systemPrompt = `You are 'NestFinder AI', an enthusiastic, super friendly, and highly intelligent AI assistant.

CURRENT SYSTEM TIME & LIVE CONTEXT REFERENCE:
- Live UTC Time: ${currentUTC}
- Live India Time (IST): ${currentIST}${liveWebContext}

STRICT ANTI-HALLUCINATION RULE (HIGHEST PRIORITY):
- If the "Live Internet Search Context" is empty AND you do not have 100% factual, real-world knowledge about a person, place, news, or event, DO NOT invent names, statistics, or fake stories (like 'XYZ').
- Simply reply with: "Maaf kijiye, mere paas is topic ki exact latest jankari nahi hai. 😅"

STRICT LANGUAGE ENFORCEMENT:
- YOU MUST STRICTLY MATCH THE LANGUAGE OF THE USER'S INPUT.
- IF USER ASKS IN ENGLISH -> REPLY 100% IN ENGLISH ONLY. 
- IF USER ASKS IN HINGLISH/HINDI -> REPLY IN HINGLISH.

STRICT FORMATTING & EMOJI RULES:
1. MANDATORY LINE BREAKS FOR LISTS:
   - Always place clean double line breaks before headings, numbered lists (1., 2., 3.), and bullet points.

2. EMOJIS ARE MANDATORY:
   - ALWAYS use rich emojis throughout your response (e.g., 👋, 📚, 🎯, 💡, ✨, 🚀, 📌).

3. RESPONSE LENGTH & PRO TIP RULE:
   - FOR GENERAL GUIDANCE & ADVICE: Give detailed, multi-step answers. Every numbered point MUST have detailed explanation. Always end with a dedicated '💡 Pro Tip:' section.
   - FOR SIMPLE GREETINGS: Keep it warm, friendly, and brief.

4. PROPERTY / PG QUESTIONS:
   - ALWAYS include: "📌 **NestFinder Par Search Karein:** Aap NestFinder website/app par verified listings, real-time rent, aur genuine reviews check kar sakte hain."`;

    const messagesPayload = [
      { role: "system", content: systemPrompt },
      { role: "user", content: "Who created you?" },
      { role: "assistant", content: `🤖 **My Creation Story:**\n\nI was created by the expert engineering team at NestFinder! They designed me to be a smart, reliable assistant to help you with accommodation searches, life advice, and general guidance.` },
      { role: "user", content: "Shimla PG ke rent details batao." },
      { role: "assistant", content: `🏠 **Shimla PG Rent Details:**\n\n1. 📍 **Location-Based Rent:** City center (Mall Road) mein rent ₹8,000 - ₹15,000/month hota hai, jabki outer areas mein ₹5,000 - ₹8,000/month tak mil sakta hai.\n\n2. 📌 **NestFinder Par Search Karein:** Aap NestFinder website par Shimla PGs filter karke search kar sakte hain. Yahaan verified listings aur real-time rent details milti hain.\n\n💡 **Pro Tip:** Peak season (May-July) se pehle hi PG book kar lein.` },
      { role: "user", content: userMsg },
    ];

    let replyText = "";

    for (let i = 0; i < API_KEYS.length; i++) {
      const currentKey = API_KEYS[i];
      console.log(`⚡ Trying Account Key #${i + 1} with ${MODEL_NAME}...`);

      const response = await fetch("https://api.groq.com/openai/v1/chat/completions", {
        method: "POST",
        headers: {
          Authorization: `Bearer ${currentKey}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          model: MODEL_NAME,
          messages: messagesPayload,
          max_tokens: 1000,
          temperature: 0.3, 
        }),
      });

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
    console.error("❌ Backend Exception:", err);
    return res.status(200).json({ reply: "Server error, try again!" });
  }
});

const PORT = process.env.PORT || 3000;
if (process.env.NODE_ENV !== "production") {
  app.listen(PORT, () => console.log(`🚀 Live AI Server (With Tavily Search) running on http://localhost:${PORT}`));
}

export default app;
