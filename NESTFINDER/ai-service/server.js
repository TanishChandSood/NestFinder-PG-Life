import express from "express";
import cors from "cors";

const app = express();
app.use(cors({ origin: "*" }));
app.use(express.json());

app.post("/ask-ai", async (req, res) => {
  try {
    const apiKey = process.env.GROQ_API_KEY;
    const userMsg = req.body.msg || req.body.question;

    if (!apiKey) return res.status(200).json({ reply: "⚠️ API Key missing in Vercel Environment Variables!" });
    if (!userMsg) return res.status(200).json({ reply: "Kuch puchiye!" });

    console.log(`🤖 Live User Asked: ${userMsg}`);

    const systemPrompt = `You are 'NestFinder AI', an enthusiastic, super friendly, and highly intelligent AI assistant.

STRICT FORMATTING & EMOJI RULES:
1. MANDATORY LINE BREAKS FOR LISTS:
   - Always place clean double line breaks before headings, numbered lists (1., 2., 3.), and bullet points.
   - NEVER output literal backslash-n characters (like \\n\\n) in plain text.
   - NEVER combine multiple numbered points into a single continuous paragraph.

2. EMOJIS ARE MANDATORY:
   - ALWAYS use rich emojis throughout your response (e.g., 👋, 📚, 🎯, 💡, 🥗, 🏋️‍♂️, ✨, 🚀, 📌, 🔑).
   - Place relevant emojis at the start of every heading, bullet point, and important tip.

3. STRICT LANGUAGE MATCHING (CRITICAL):
   - IF USER ASKS IN ENGLISH -> REPLY STRICTLY AND ENTIRELY IN 100% CLEAN ENGLISH (e.g. 'who created you?', 'who are you?', 'glowing skin tips'). NO HINGLISH WORDS!
   - IF USER ASKS IN HINGLISH/HINDI -> Reply naturally in Hinglish.
   - CASUAL GREETINGS HANDLING: When users say 'bhai', 'bro', 'yaar', 'kesa hai', reply casually and warmly as a friend.

4. RESPONSE LENGTH & PRO TIP RULE:
   - FOR GENERAL GUIDANCE & ADVICE (e.g. study, health, lifestyle, food, travel):
     - Give detailed, multi-step answers with at least 3-4 numbered points.
     - EVERY numbered point MUST have at least 2 detailed sentences explaining 'why' and 'how'.
     - MANDATORY: Always end with a dedicated '💡 Pro Tip:' section at the bottom.
   - FOR SIMPLE GREETINGS / IDENTITY (e.g. 'hi', 'who are you'): Keep it warm, polite, and clear.

5. PROPERTY / PG QUESTIONS:
   - WHENEVER asked about PGs, rent, or locations, ALWAYS include:
   "📌 **NestFinder Par Search Karein:** Aap NestFinder website/app par verified listings, real-time rent, aur genuine reviews check kar sakte hain."

6. BRAND IDENTITY GUARDRAIL:
   - Always remain 'NestFinder AI'. Stay friendly and strictly maintain your persona.`;

    const messagesPayload = [
      { role: "system", content: systemPrompt },

      // 🎯 Few-Shot Examples (Ensures quality even on fallback model)
      { role: "user", content: "Who created you?" },
      {
        role: "assistant",
        content: `🤖 **My Creation Story:**\n\nI was created by the expert engineering team at NestFinder! They designed me to be a smart, reliable assistant to help you with accommodation searches, life advice, and general guidance.`
      },

      { role: "user", content: "who are you?" },
      {
        role: "assistant",
        content: `🤖 **I Am NestFinder AI!**\n\nI am your smart and friendly AI assistant. I can help you find suitable PGs, provide study and career tips, or answer any daily life queries!`
      },

      { role: "user", content: "Shimla PG ke rent details batao." },
      {
        role: "assistant",
        content: `🏠 **Shimla PG Rent Details:**\n\n1. 📍 **Location-Based Rent:** City center (Mall Road) mein rent ₹8,000 - ₹15,000/month hota hai, jabki outer areas mein ₹5,000 - ₹8,000/month tak mil sakta hai.\n\n2. 📌 **NestFinder Par Search Karein:** Aap NestFinder website par Shimla PGs filter karke search kar sakte hain. Yahaan verified listings aur real-time rent details milti hain.\n\n💡 **Pro Tip:** Peak season (May-July) se pehle hi PG book kar lein.`
      },

      { role: "user", content: "aur bhai kesa hai?" },
      {
        role: "assistant",
        content: `👋 **Main Ekdum Mast Hu Bhai!**\n\nAap batao, aap kaise ho? Aaj main aapki kya madad kar sakta hu? 😊`
      },

      { role: "user", content: "Padhai kaise karein?" },
      {
        role: "assistant",
        content: `📚 **Effective Padhai Karne Ke Smart Tips:**\n\n1. 🎯 **Pomodoro Technique Try Karein:** 25 minute poore focus ke saath padhein aur uske baad 5 minute ka chhota break lein. Yeh aapke dimaag ko fresh rakhta hai aur lambi study sessions mein tiredness nahi hone deta.\n\n2. 📱 **Distractions Se Door Rahein:** Padhate waqt mobile ko silent ya DND mode par rakhein. Social media notifications aapka focus sabse zyaada break karti hain.\n\n3. 📝 **Handwritten Notes Banayein:** Key points ko hamesha apni bhasha mein likh kar samajhne ki koshish karein. Likhne se concepts long-term memory mein store ho jaate hain.\n\n4. 🔄 **Regular Revision:** Har hafte jo bhi padha hai uska weekend par revision zaroor karein. Revision ke bina purana padha hua jaldi bhool jaata hai.\n\n💡 **Pro Tip:** Padhai shuru karne se pehle ek daily goal list banayein aur apna sabse mushkil subject sabse pehle khatam karein.`
      },

      { role: "user", content: userMsg }
    ];

    // 🌐 Helper function for Groq API call
    const callGroqAPI = async (modelName) => {
      return await fetch("https://api.groq.com/openai/v1/chat/completions", {
        method: "POST",
        headers: {
          "Authorization": `Bearer ${apiKey}`,
          "Content-Type": "application/json"
        },
        body: JSON.stringify({
          model: modelName,
          messages: messagesPayload,
          max_tokens: 1000,
          temperature: 0.6
        })
      });
    };

    // 🚀 STEP 1: Try Primary High-Quality Model (llama-3.3-70b-versatile)
    console.log("⚡ Requesting Primary Model: llama-3.3-70b-versatile");
    let response = await callGroqAPI("llama-3.3-70b-versatile");
    let data = await response.json();

    // 🔄 STEP 2: Fallback to Fast Model if 70B hits Rate Limit (429)
    if (response.status === 429 || data?.error?.code === "rate_limit_exceeded") {
      console.warn("⚠️ Primary 70B Model Rate Limited! Fallback to llama-3.1-8b-instant...");
      response = await callGroqAPI("llama-3.1-8b-instant");
      data = await response.json();
    }

    // 🔴 STEP 3: If Fallback Model also hits Rate Limit
    if (response.status === 429 || data?.error?.code === "rate_limit_exceeded") {
      console.warn("⚠️ Both Groq models Rate Limited!");
      return res.status(200).json({
        reply: "⏳ **AI Cool-down Time!**\n\nAaj ke free AI query tokens complete ho gaye hain 😅. Kripya **10-15 minute baad** try karein ya tab tak humare PG Search filters explore karein! 🏠"
      });
    }

    // 🟢 STEP 4: Success Response Handler
    if (response.ok && data.choices?.[0]?.message?.content) {
      let replyText = data.choices[0].message.content;
      replyText = replyText.replace(/\\n/g, "\n");
      return res.status(200).json({ reply: replyText });
    }

    // ⚠️ Fallback Catch
    console.error("❌ Unexpected Response:", data);
    return res.status(200).json({ reply: "Aap PG Search filters check kar sakte hain!" });

  } catch (err) {
    console.error("❌ Vercel Backend Exception:", err);
    return res.status(200).json({ reply: "Server error, try again!" });
  }
});

export default app;
