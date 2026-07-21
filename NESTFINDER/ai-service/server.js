import express from "express";
import cors from "cors";

const app = express();

app.use(cors({ origin: "*" }));
app.use(express.json());

app.post("/ask-ai", async (req, res) => {
  try {
    const apiKey = process.env.GROQ_API_KEY;
    const userMsg = req.body.msg || req.body.question;

    if (!apiKey) {
      return res.status(200).json({ reply: "⚠️ Vercel par GROQ_API_KEY missing hai!" });
    }

    if (!userMsg) {
      return res.status(200).json({ reply: "Kuch toh puchiye!" });
    }

    // Advanced Markdown Prompt to match Local UI Exactly
    const systemPrompt = `
You are "PG Life Smart AI" (NestFinder Assistant). Your job is to help users find PGs, Hostels, and Flatmates using structured, formatted Markdown responses.

STRICT FORMATTING RULES:
1. ALWAYS use Markdown styling with emojis, bold text (**text**), and bullet points (•).
2. If the user asks about a CITY or LOCATION (e.g., "shimla", "delhi", "pune", "mumbai"):
   - Respond in this EXACT structure:

Ji bilkul! Maine aapke parameters ke hisab se best PGs dhoondh liye hain:

🏠 **[Realistic PG Name, e.g. The Ridge View Premium Homestay]**
• Rent: **₹[Amount]/month**
• Type: **[Unisex / Boys / Girls]**
• Distance: 📍 **[Number] KM away** aapki real location se.

[View Room](#)

3. If the user asks a general query like "pg konsa best hoga?" or "hi":
   - Respond in this EXACT structure:

🤖 **AI Assistant:**
Aap kis city ya area mein PG dhoondh rahe hain? "Best PG" location par depend karta hai.

Bata dijiye:
📍 **City/Area** (jaise Chandigarh, Delhi, Bangalore, etc.)
👨/👧 **Type** (boys/girls/co-living)
💰 **Budget** (jaise ₹5,000 - ₹15,000)

4. ALWAYS keep tone friendly, professional, and in natural Hinglish.
`;

    const response = await fetch("https://api.groq.com/openai/v1/chat/completions", {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${apiKey}`,
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        model: "llama-3.3-70b-versatile",
        messages: [
          {
            role: "system",
            content: systemPrompt
          },
          {
            role: "user",
            content: userMsg
          }
        ],
        max_tokens: 250,
        temperature: 0.3
      })
    });

    const data = await response.json();

    if (response.ok && data.choices?.[0]?.message?.content) {
      const aiReply = data.choices[0].message.content;
      return res.status(200).json({ reply: aiReply });
    }

    return res.status(200).json({ 
      reply: "🤖 AI filter busy hai, aap direct PG search filter use karein!" 
    });

  } catch (err) {
    console.error("Server Error:", err.message);
    return res.status(200).json({ reply: "🤖 Server error, thodi der me try karein!" });
  }
});

app.get("/", (req, res) => res.send("PG Life AI Backend Live!"));

export default app;
