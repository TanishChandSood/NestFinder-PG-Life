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

2. GENERAL QUESTIONS (e.g., study tips, health, food, career, life advice):
   - Answer directly, comprehensively, and enthusiastically with great Markdown formatting.
   - DO NOT mention PGs, hostels, or accommodation unless explicitly asked.

3. PROPERTY / PG QUESTIONS:
   - Help the user find suitable options on NestFinder.

4. LANGUAGE & TONE:
   - Respond in friendly, warm Hinglish / English.
   - Use bold text for key terms and clean bullet points.`;

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
          
          // 🎯 FEW-SHOT EXAMPLES (Enforces strict Emoji & General Knowledge boundary)
          { role: "user", content: "Padhai kaise karein?" },
          {
            role: "assistant",
            content: `📚 **Effective Padhai Karne Ke Smart Tips:**\n\n1. 🎯 **Pomodoro Technique Try Karein:** 25 minute focus se padhein aur 5 minute break lein.\n2. 📱 **Distractions Se Door Rahein:** Padhate waqt mobile silent rakhein.\n3. 📝 **Notes Banayein:** Key points ko apni bhasha me likhein.\n4. 🔄 **Regular Revision:** Har hafte padha hua revise karein.`
          },
          { role: "user", content: "Sehat ke liye kya khana chahiye?" },
          {
            role: "assistant",
            content: `🥗 **Healthy Diet & Nutrition Tips:**\n\n1. 🥦 **Hari Sabziyan & Fruits:** Apne khane me daily taaza phal aur sabziyan shamil karein.\n2. 💧 **Paani Khoob Piyein:** Din me 3-4 litre paani piyein.\n3. 🏋️‍♂️ **Protein Rich Food:** Daal, anda, paneer intake badhayein.`
          },
          
          // Real user query:
          { role: "user", content: userMsg }
        ],
        max_tokens: 800, // 🚀 Fixed token limit (was 100)
        temperature: 0.7 // 🚀 Fixed creative temperature (was 0.2)
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
