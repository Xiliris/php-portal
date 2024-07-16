document.addEventListener("DOMContentLoaded", () => {
  const app = document.getElementById("app");

  const components = {
    "{{footer}}": "/components/footer.html",
    "{{navbar}}": "/components/navbar.html",
  };

  const routes = {
    404: "/pages/404.html",
    "/": "/pages/home.html",
    "/about": "/pages/about.html",
    "/contact": "/pages/contact.html",
    "/login": "/pages/login.html",
    "/register": "/pages/register.html",
    "/dashboard": "/pages/dashboard.html",
  };

  const navigateTo = (url) => {
    history.pushState(null, null, url);
    router();
  };

  const router = async () => {
    const request = routes[location.pathname] || routes[404];
    try {
      const response = await fetch(request);
      if (!response.ok) throw new Error("Network response was not ok.");
      let html = await response.text();

      for (const [component, componentPath] of Object.entries(components)) {
        const componentResponse = await fetch(componentPath);
        if (!componentResponse.ok)
          throw new Error("Network response was not ok.");
        const componentHtml = await componentResponse.text();
        html = html.replace(new RegExp(component, "g"), componentHtml);
      }

      app.innerHTML = html;

      html.split("<script>").forEach((script) => {
        if (script.includes("</script>")) {
          const scriptContent = script.split("</script>")[0];
          eval(scriptContent);
        }
      });
    } catch (error) {
      console.error("Fetch error:", error);
      app.innerHTML = "<h1>Failed to load the page.</h1>";
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
