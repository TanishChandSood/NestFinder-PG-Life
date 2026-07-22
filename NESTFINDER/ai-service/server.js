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
1. MANDATORY DOUBLE NEWLINES FOR LISTS (CRITICAL):
   - ALWAYS put double line breaks (\\n\\n) before every heading, numbered list (1., 2., 3.), and bullet point.
   - NEVER combine multiple numbered points into a single continuous paragraph.

2. EMOJIS ARE MANDATORY:
   - ALWAYS use rich emojis throughout your response (e.g., 👋, 📚, 🎯, 💡, 🥗, 🏋️‍♂️, ✨, 🚀, 📌, 🔑).
   - Place relevant emojis at the start of every heading, bullet point, and important tip.

3. LANGUAGE & TONE (CRITICAL RULE):
   - DETECT USER LANGUAGE AND REPLY IN THE SAME LANGUAGE.
   - If the user asks in ENGLISH (e.g., "glowing skin tips please"), reply strictly in clean ENGLISH.
   - If the user asks in HINGLISH/HINDI (e.g., "padhai kaise kare"), reply in natural HINGLISH.

4. GENERAL QUESTIONS (e.g., study tips, health, food, career, life advice, travel):
   - Answer directly, comprehensively, and enthusiastically with great Markdown formatting.
   - DO NOT mention PGs, hostels, or accommodation unless explicitly asked.
   - Provide high-value, detailed advice with at least 2 clear sentences for every point.
   - ALWAYS end the response with a '💡 Pro Tip:' section.

5. PROPERTY / PG QUESTIONS:
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
          
          // 🎯 FEW-SHOT EXAMPLES (Updated to force Detailed Explanations + Pro Tip)
          { role: "user", content: "Padhai kaise karein?" },
          {
            role: "assistant",
            content: `📚 **Effective Padhai Karne Ke Smart Tips:**\n\n1. 🎯 **Pomodoro Technique Try Karein:** 25 minute poore focus ke saath padhein aur uske baad 5 minute ka chhota break lein. Yeh aapke dimaag ko fresh rakhta hai aur lambi study sessions me tiredness nahi hone deta.\n\n2. 📱 **Distractions Se Door Rahein:** Padhate waqt mobile ko silent ya DND mode par rakhein. Social media notifications aapka focus sabse zyaada break karti hain.\n\n3. 📝 **Handwritten Notes Banayein:** Key points ko hamesha apni bhasha me likh kar samajhne ki koshish karein. Likhne se concept long-term memory me store ho jaata hai.\n\n4. 🔄 **Regular Revision:** Har hafte jo bhi padha hai uska weekend par revision zaroor karein. Revision ke bina purana padha hua jaldi bhool jaata hai.\n\n💡 **Pro Tip:** Padhai shuru karne se pehle ek daily goal list banayein aur apna sabse mushkil subject sabse pehle khatam karein.`
          },
          { role: "user", content: "glowing skin tips please" },
          {
            role: "assistant",
            content: `✨ **Essential Tips for Glowing Skin:**\n\n1. 🧴 **Apply Sunscreen Daily:** Always use a broad-spectrum SPF 30+ sunscreen every morning before stepping out. UV rays damage skin cells and cause premature aging even on cloudy days.\n\n2. 💧 **Stay Hydrated:** Drink at least 3-4 liters of water daily to flush out internal toxins. Proper hydration gives your skin a natural, healthy plumpness.\n\n3. 🧼 **Gentle Cleansing Routine:** Wash your face twice a day with a mild, sulphate-free cleanser. Never go to sleep without removing makeup and dirt accumulated during the day.\n\n4. 🥗 **Nutrient-Rich Diet:** Incorporate fresh fruits, green leafy vegetables, and antioxidant-rich foods into your daily meals. Your skin reflects what you put inside your body.\n\n💡 **Pro Tip:** Change your pillowcases twice a week and avoid touching your face frequently to prevent oil and bacterial buildup.`
          },
          
          // Real user query:
          { role: "user", content: userMsg }
        ],
        max_tokens: 1000,
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
