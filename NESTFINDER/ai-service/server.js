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

    // STRICT SYSTEM PROMPT: NO FAKE PGS, CLEAN CONVERSATIONAL SUPPORT
    const systemPrompt = `
You are "NestFinder AI", a helpful, friendly customer support assistant for the NestFinder PG booking website.

STRICT OPERATIONAL RULES:
1. NEVER invent, fake, or hallucinate PG names, fake prices, or fake addresses.
2. Your ONLY job is to answer general questions (e.g. greetings like "hi", "kaise ho", "how to book", "rules") warmly in maximum 2-3 short lines in Hinglish.
3. If the user asks for PGs in a specific city/location, politely guide them: "Aap search bar mein city/filters select karke hamare real verified PGs check kar sakte hain!"
4. Do NOT output raw markdown links like [View Room](#) or complex syntax. Keep text plain, clean, and concise.
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
          { role: "system", content: systemPrompt },
          { role: "user", content: userMsg }
        ],
        max_tokens: 120,
        temperature: 0.2
      })
    });

    const data = await response.json();

    if (response.ok && data.choices?.[0]?.message?.content) {
      const aiReply = data.choices[0].message.content;
      return res.status(200).json({ reply: aiReply });
    }

    return res.status(200).json({ 
      reply: "🤖 Server busy hai, aap direct search filter use kar sakte hain!" 
    });

  } catch (err) {
    console.error("Server Error:", err.message);
    return res.status(200).json({ reply: "🤖 Server error, thodi der me try karein!" });
  }
});

app.get("/", (req, res) => res.send("PG Life AI Backend Live!"));

export default app;
