document.addEventListener("DOMContentLoaded", () => {
  const app = document.getElementById("app");

  const components = {
    "{{footer}}": "/php-portal/client/components/footer.html",
  };

  const routes = {
    404: "/php-portal/client/pages/404.html",
    "/php-portal/client/": "/php-portal/client/pages/home.html",
    "/": "/php-portal/client/pages/home.html",
    "/about": "/php-portal/client/pages/about.html",
    "/contact": "/php-portal/client/pages/contact.html",
    "/login": "/php-portal/client/pages/login.html",
    "/register": "/php-portal/client/pages/register.html",
  };

  const navigateTo = (url) => {
    history.pushState(null, null, url);
    router();
  };

  const router = async () => {
    const request = routes[location.pathname] || routes[404];
    try {
      const response = await fetch(request);
      if (!response.ok) {
        throw new Error("Failed to fetch page.");
      }
      let html = await response.text();

      for (const [component, componentPath] of Object.entries(components)) {
        const componentResponse = await fetch(componentPath);
        const componentHtml = await componentResponse.text();
        html = html.replace(new RegExp(component, "g"), componentHtml);
      }

      app.innerHTML = html;

      if (location.pathname === "/login") {
        const loginForm = document.getElementById("loginForm");

        loginForm.addEventListener("submit", async (e) => {
          e.preventDefault();
          const formData = new FormData(loginForm);

          try {
            const loginResponse = await fetch(
              "/php-portal/api/routes/login.php",
              {
                method: "POST",
                body: formData,
              }
            );

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

      if (location.pathname === "/register") {
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
    } catch (error) {
      console.error("Router Error:", error);
      app.innerHTML =
        "<h1>Router Error</h1><p>An error occurred while routing. Please try again later.</p>";
    }
  };

  document.body.addEventListener("click", (e) => {
    if (e.target.matches("[data-link]")) {
      e.preventDefault();
      navigateTo(e.target.href);
    }
  });

  window.addEventListener("popstate", router);

  router();
});
