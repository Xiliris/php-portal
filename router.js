document.addEventListener("DOMContentLoaded", async () => {
  const app = document.getElementById("app");

  const hrefReplace = {};

  const components = {
    "{{navbar}}": "/components/navbar.html",
    "{{footer}}": "/components/footer.html",
  };

  const routes = {
    404: "http://php-portal.local/pages/error/404.html",
    "/401": "http://php-portal.local/pages/error/401.html",
    "/": "http://php-portal.local/pages/home.html",
    "/person-of-interest":
      "http://php-portal.local/pages/person-of-interest.html",
    "/coming": "http://php-portal.local/pages/coming.html",
    "/news": "http://php-portal.local/pages/news.html",
    "/about": "http://php-portal.local/pages/about.html",
    "/partners": "http://php-portal.local/pages/partners.html",
    "/shop": "http://php-portal.local/pages/shop.html",
    "/donate": "http://php-portal.local/pages/donate.html",
    "/submit": "http://php-portal.local/pages/submit.html",
    "/change-password":
      "http://php-portal.local/pages/auth/change-password.html",
    "/login/random": "http://php-portal.local/pages/auth/login.html",
    "/logout": "http://php-portal.local/pages/auth/logout.html",
    "/dashboard": "http://php-portal.local/pages/admin/dashboard.html",
    "/master-panel":
      "http://php-portal.local/pages/master-panel/master-panel.html",
    "/master-panel/routes":
      "http://php-portal.local/pages/master-panel/routes.html",
    "/master-panel/users":
      "http://php-portal.local/pages/master-panel/users.html",
    "/master-panel/sensitive-data":
      "http://php-portal.local/pages/master-panel/sensitive-data.html",
      "/documents": "http://php-portal.local/pages/documents.html",
    "/profile": "http://php-portal.local/pages/profile.html",
  };

  const routePermissions = {
    "/logout": 1,
    "/change-password": 1,
    "/shop": 1,
    "/dashboard": 3,
    "/master-panel": 4,
    "/master-panel/routes": 4,
    "/master-panel/users": 4,
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
      if (!user) return false;
      const userPermission = permissionLevel(user.role);
      if (userPermission < requiredRoles) return false;

      return true;
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

      for (const [oldHref, newHref] of Object.entries(hrefReplace)) {
        html = html.replace(new RegExp(oldHref, "g"), newHref);
      }

      app.innerHTML = html;
      window.scrollTo(0, 0);

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

  await fetch("/api/routes/router.php")
    .then((response) => {
      if (response.ok) {
        response.json().then((res) => {
          const dataRoutes = res.data;

          dataRoutes.forEach(({ route, changedRoute }) => {
            if (route !== changedRoute) {
              if (routes[route]) {
                routes[changedRoute] = routes[route];
                delete routes[route];
                hrefReplace[route] = changedRoute;
              }
            }
          });
          router();
        });
      } else {
        throw new Error("Failed to fetch router.php");
      }
    })
    .catch((error) => {
      console.error("Failed to fetch router.php", error);
    });
});

async function checkAuth() {
  const response = await fetch("/api/routes/user.php", {
    method: "GET",
  });

  const data = await response.json();

  if (data.success) {
    return data.user;
  } else {
    return null;
  }
}

function permissionLevel(role) {
  if (!role) return 0;
  return {
    user: 1,
    moderator: 2,
    admin: 3,
    owner: 4,
  }[role];
}
