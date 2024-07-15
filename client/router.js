document.addEventListener("DOMContentLoaded", () => {
  const app = document.getElementById("app");

  const components = {
    "{{footer}}": "/php-portal/client/components/footer.html",
    "{{navbar}}": "/php-portal/client/components/navbar.html",
  };

  const routes = {
    404: "/php-portal/client/pages/404.html",
    "/php-portal/client/": "/php-portal/client/pages/home.html",
    "/": "/php-portal/client/pages/home.html",
    "/about": "/php-portal/client/pages/about.html",
    "/contact": "/php-portal/client/pages/contact.html",
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
