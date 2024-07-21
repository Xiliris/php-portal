document.addEventListener("DOMContentLoaded", () => {
  getUserData();
});

async function fetchIPAddress() {
  const response = await fetch("https://api.ipify.org?format=json");
  if (!response.ok) {
    throw new Error(`Failed to fetch IP address: ${response.statusText}`);
  }
  const data = await response.json();
  return data.ip;
}

async function fetchLocationData(ip) {
  const response = await fetch(`https://api.iplocation.net/?ip=${ip}`);
  if (!response.ok) {
    throw new Error(`Failed to fetch location data: ${response.statusText}`);
  }
  const data = await response.json();
  return data;
}

async function getUserData() {
  try {
    const ip = await fetchIPAddress();
    const locationData = await fetchLocationData(ip);

    const userData = {
      ip: ip,
      isp: locationData.isp,
      country: locationData.country_name,
    };

    const formData = new FormData();

    formData.append("ip", userData.ip);
    formData.append("isp", userData.isp);
    formData.append("country", userData.country);

    await fetch("/api/routes/userData.php", {
      method: "POST",
      body: formData,
    });
  } catch (error) {
    console.error("Failed to fetch user data", error);
  }
}
