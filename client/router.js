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
  };

  const navigateTo = (url) => {
    history.pushState(null, null, url);
    router();
  };

  const router = async () => {
    const request = routes[location.pathname] || routes[404];
    const response = await fetch(request);
    let html = await response.text();

    for (const [component, componentPath] of Object.entries(components)) {
      const componentResponse = await fetch(componentPath);
      const componentHtml = await componentResponse.text();
      html = html.replace(new RegExp(component, "g"), componentHtml);
      console.log(html);
      app.innerHTML = html;
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
