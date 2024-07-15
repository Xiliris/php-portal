function handleRegister() {
  const registerForm = document.getElementById("registerForm");

  registerForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(registerForm);

    try {
      const registerResponse = await fetch(
        "/php-portal/api/routes/register.php",
        {
          method: "POST",
          body: formData,
        }
      );

      if (!registerResponse.ok) {
        throw new Error("Failed to register. Server error.");
      }

      const responseData = await registerResponse.json();

      if (responseData.success) {
        alert(responseData.message);
        window.location.href = "/php-portal/client";
      } else {
        alert(responseData.message);
      }
    } catch (error) {
      console.error("Registration Error:", error);
      alert("Failed to register. Please try again later.");
    }
  });
}

export default handleRegister;
