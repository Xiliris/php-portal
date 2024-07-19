document.addEventListener("DOMContentLoaded", () => {
  const app = document.getElementById("app");

  const components = {
    "{{navbar}}": "/components/navbar.html",
    "{{footer}}": "/components/footer.html",
  };

  const routes = {
    404: "/pages/error/404.html",
    "/401": "/pages/error/401.html",
    "/": "/pages/home.html",
    "/person-of-interest": "/pages/person-of-interest.html",
    "/coming": "/pages/coming.html",
    "/news": "/pages/news.html",
    "/about": "/pages/about.html",
    "/partners": "/pages/partners.html",
    "/shop": "/pages/shop.html",
    "/donate": "/pages/donate.html",
    "/submit": "/pages/submit.html",
    "/profile": "/pages/auth/profile.html",
    "/change-password": "/pages/auth/change-password.html",
    "/forgot-password": "/pages/auth/forgot-password.html",
    "/login": "/pages/auth/login.html",
    "/logout": "/pages/auth/logout.html",
    "/register": "/pages/auth/register.html",
    "/dashboard": "/pages/admin/dashboard.html",
    "/master-panel": "/pages/admin/master-panel.html",
  };

  const routePermissions = {
    "/dashboard": ["admin", "owner"],
    "/profile": ["user", "admin", "owner"],
    "/logout": ["user", "admin", "owner"],
    "/master-panel": ["owner"],
    "/change-password": ["user", "admin", "owner"],
  };

  const colors = {
    "var(--primary)": "black",
    "var(--primary-light)": "white",
    "var(--secondary)": "slategrey",
    "var(--danger)": "red",
    "var(--background-color)": "rgb(92, 114, 125)",
    "var(--background-color-light)": "rgb(235, 235, 235)",
  };

  const cache = {};

  const fetchAndCache = async (url) => {
    if (cache[url]) return cache[url];
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Failed to fetch ${url}`);
    const text = await response.text();
    cache[url] = text;
    return text;
  };

  const navigateTo = (url) => {
    history.pushState(null, null, url);
    router();
  };

  const checkPermissions = (user, path) => {
    if (routePermissions[path]) {
      const requiredRoles = routePermissions[path];
      if (!user || !requiredRoles.includes(user.role)) {
        return false;
      }
    }
    return true;
  };

  const router = async () => {
    const path = location.pathname;
    const request = routes[path] || routes[404];

    try {
      const user = await checkAuth();
      const hasPermission = checkPermissions(user, path);

      if (!hasPermission) {
        navigateTo("/401");
        return;
      }

      let html = await fetchAndCache(request);

      for (const [component, componentPath] of Object.entries(components)) {
        const componentHtml = await fetchAndCache(componentPath);
        html = html.replace(new RegExp(component, "g"), componentHtml);
      }

      for (const [color, value] of Object.entries(colors)) {
        html = html.replace(
          new RegExp(color.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1"), "g"),
          value
        );
      }

      app.innerHTML = html;

      html.split("<script>").forEach((script) => {
        if (script.includes("</script>")) {
          const scriptContent = script.split("</script>")[0];
          eval(scriptContent);
        }
      });

      document.querySelectorAll("style").forEach((style) => {
        if (style.id) {
          style.remove();
        }
      });

      html.split("<style>").forEach((style, index) => {
        if (style.includes("</style>")) {
          const styleContent = style.split("</style>")[0];
          const styleElement = document.createElement("style");
          styleElement.id = `style-${index}`;
          styleElement.innerHTML = styleContent;
          document.head.appendChild(styleElement);
        }
      });
    } catch (error) {
      console.error("Fetch error:", error);
      app.innerHTML = "<h1>Failed to load the page.</h1>";
    }
  };

  let debounceTimer;
  document.body.addEventListener("click", (e) => {
    if (e.target.matches("[data-link]")) {
      e.preventDefault();
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => navigateTo(e.target.href), 100);
    }
  });

  window.addEventListener("popstate", router);

  router();
});

async function checkAuth() {
  const response = await fetch("/api/routes/user.php", {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
  });

  if (response.ok) {
    const data = await response.json();
    return data.user;
  } else {
    return null;
  }
}
