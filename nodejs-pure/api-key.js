const axios = require("axios");

const API_KEY = process.env.IVAO_API_KEY || "YOUR_API_KEY_HERE";

const clientWithHeader = axios.create({
  baseURL: "https://api.ivao.aero/v2",
  headers: {
    "X-API-Key": API_KEY,
  },
});

async function fetchAirportsWithHeader() {
  try {
    const response = await clientWithHeader.get("/airports");
    console.log("Airports:", response.data);
  } catch (err) {
    if (err.response) {
      console.error(`Error ${err.response.status}:`, err.response.data);
    } else {
      console.error("Request error:", err.message);
    }
  }
}

fetchAirportsWithHeader();
