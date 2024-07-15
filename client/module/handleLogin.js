function handleLogin() {
  loginForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(loginForm);

    try {
      const loginResponse = await fetch("/php-portal/api/routes/login.php", {
        method: "POST",
        body: formData,
      });

      const responseData = await loginResponse.json();

      if (responseData.success) {
        alert(responseData.message);
        window.location.href = "/php-portal/client";
      } else {
        alert(responseData.message);
      }
    } catch (error) {
      console.error("Error:", error);
    }
  });
}

export default handleLogin;
