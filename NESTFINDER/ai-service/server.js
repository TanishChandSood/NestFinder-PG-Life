import express from "express";
import cors from "cors";

const app = express();
app.use(cors({ origin: "*" }));
app.use(express.json());

app.post("/ask-ai", async (req, res) => {
  try {
    const apiKey = process.env.GROQ_API_KEY;
    const userMsg = req.body.msg || req.body.question;

    if (!apiKey) return res.status(200).json({ reply: "⚠️ API Key missing in Vercel Settings!" });
    if (!userMsg) return res.status(200).json({ reply: "Kuch puchiye!" });

    const systemPrompt = `You are 'NestFinder AI', an enthusiastic, super friendly, and highly intelligent AI assistant.

STRICT FORMATTING & EMOJI RULES:
1. EMOJIS ARE MANDATORY:
   - ALWAYS use rich emojis throughout your response (e.g., 👋, 📚, 🎯, 💡, 🥗, 🏋️‍♂️, ✨, 🚀, 📌, 🔑).
   - Place relevant emojis at the start of every heading, bullet point, and important tip.

2. LANGUAGE & TONE (CRITICAL RULE):
   - DETECT USER LANGUAGE AND REPLY IN THE SAME LANGUAGE.
   - If the user asks in ENGLISH (e.g., "glowing skin tips please"), reply strictly in clean ENGLISH.
   - If the user asks in HINGLISH/HINDI (e.g., "padhai kaise kare"), reply in natural HINGLISH.

3. GENERAL QUESTIONS (e.g., study tips, health, food, career, life advice):
   - Answer directly, comprehensively, and enthusiastically with great Markdown formatting.
   - DO NOT mention PGs, hostels, or accommodation unless explicitly asked.

4. PROPERTY / PG QUESTIONS:
   - Help the user find suitable options on NestFinder.`;

    const response = await fetch("https://api.groq.com/openai/v1/chat/completions", {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${apiKey}`,
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        model: "llama-3.3-70b-versatile",
        messages: [
          { role: "system", content: systemPrompt },
          
          // 🎯 FEW-SHOT EXAMPLES (Enforces Strict Language Mirroring & Formatting)
          { role: "user", content: "Padhai kaise karein?" },
          {
            role: "assistant",
            content: `📚 **Effective Padhai Karne Ke Smart Tips:**\n\n1. 🎯 **Pomodoro Technique Try Karein:** 25 minute focus se padhein aur 5 minute break lein.\n2. 📱 **Distractions Se Door Rahein:** Padhate waqt mobile silent rakhein.\n3. 📝 **Notes Banayein:** Key points ko apni bhasha me likhein.\n4. 🔄 **Regular Revision:** Har hafte padha hua revise karein.`
          },
          { role: "user", content: "glowing skin tips please" },
          {
            role: "assistant",
            content: `✨ **Essential Tips for Glowing Skin:**\n\n1. 🧴 **Apply Sunscreen Daily:** Use a broad-spectrum SPF 30 sunscreen every morning.\n2. 💧 **Stay Hydrated:** Drink at least 3-4 liters of water daily.\n3. 🧼 **Gentle Cleansing:** Wash your face twice a day with a mild cleanser and moisturize.\n4. 🥗 **Healthy Diet:** Include fresh fruits and green vegetables in your diet.`
          },
          
          // Real user query:
          { role: "user", content: userMsg }
        ],
        max_tokens: 800,
        temperature: 0.7
      })
    });

    const data = await response.json();
    if (response.ok && data.choices?.[0]?.message?.content) {
      return res.status(200).json({ reply: data.choices[0].message.content });
    }
    return res.status(200).json({ reply: "Aap PG Search filters check kar sakte hain!" });

  } catch (err) {
    console.error("Vercel AI Error:", err);
    return res.status(200).json({ reply: "Server error, try again!" });
  }
});

export default app;
