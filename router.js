document.addEventListener("DOMContentLoaded", async () => {
  const app = document.getElementById("app");

  const baseUrl = "http://php-portal.local";

  const hrefReplace = {};

  const components = {
    "{{navbar}}": "/components/navbar.html",
    "{{footer}}": "/components/footer.html",
  };

  const routes = {
    404: `${baseUrl}/pages/error/404.html`,
    "/401": `${baseUrl}/pages/error/401.html`,
    "/": `${baseUrl}/pages/home.html`,
    "/person-of-interest": `${baseUrl}/pages/person-of-interest.html`,
    "/coming": `${baseUrl}/pages/coming.html`,
    "/news": `${baseUrl}/pages/news.html`,
    "/about": `${baseUrl}/pages/about.html`,
    "/partners": `${baseUrl}/pages/partners.html`,
    "/shop": `${baseUrl}/pages/shop.html`,
    "/donate": `${baseUrl}/pages/donate.html`,
    "/submit": `${baseUrl}/pages/submit.html`,
    "/change-password": `${baseUrl}/pages/auth/change-password.html`,
    "/login/random": `${baseUrl}/pages/auth/login.html`,
    "/logout": `${baseUrl}/pages/auth/logout.html`,
    "/dashboard": `${baseUrl}/pages/admin/dashboard.html`,
    "/master-panel": `${baseUrl}/pages/master-panel/master-panel.html`,
    "/master-panel/routes": `${baseUrl}/pages/master-panel/routes.html`,
    "/master-panel/users": `${baseUrl}/pages/master-panel/users.html`,
    "/master-panel/sensitive-data": `${baseUrl}/pages/master-panel/sensitive-data.html`,
    "/master-panel/donations": `${baseUrl}/pages/master-panel/donations.html`,
    "/master-panel/footer": `${baseUrl}/pages/master-panel/footer.html`,
    "/documents": `${baseUrl}/pages/documents.html`,
    "/videos": `${baseUrl}/pages/video.html`,
    "/audio": `${baseUrl}/pages/audio.html`,
    "/news/:id/:document": `${baseUrl}/pages/news.html`,
    "/profile/:userid": `${baseUrl}/pages/profile/main.html`,
    "/profile/:userid/:event/document": `${baseUrl}/pages/profile/documents.html`, // profile/1/1929/documents
    "/profile/:userid/:event/video": `${baseUrl}/pages/profile/video.html`, // profile/1/1929/videos
    "/profile/:userid/:event/audio": `${baseUrl}/pages/profile/audio.html`, // profile/1/1929/audio
    "/profile/:userid/:event/image": `${baseUrl}/pages/profile/image.html`, // profile/1/1929/images
    "/profile/:userid/:event/text": `${baseUrl}/pages/profile/text.html`, // profile/1/1929
  };

  const routePermissions = {
    "/logout": 1,
    "/change-password": 1,
    "/shop": 1,
    "/dashboard": 3,
    "/master-panel": 4,
    "/master-panel/routes": 4,
    "/master-panel/users": 4,
    "/master-panel/sensitive-data": 4,
    "/master-panel/donations": 4,
    "/master-panel/footer": 4,
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

  const matchRoute = (path) => {
    console.log("RAN");
    const routeKeys = Object.keys(routes);
    for (const route of routeKeys) {
      const routePattern = new RegExp("^" + route.replace(/:\w+/g, "([\\w-]+)").replace(/\//g, "\\/") + "$");
      const match = path.match(routePattern);
      if (match) {
        return { route, params: match.slice(1) };
      }
    }
    return null;
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
    const matchedRoute = matchRoute(path);
    const routeKey = matchedRoute ? matchedRoute.route : null;
    const params = matchedRoute ? matchedRoute.params : [];
    const request = routeKey ? routes[routeKey] : routes[404];

    try {
      const user = await checkAuth();
      const hasPermission = checkPermissions(user, routeKey || path);

      if (!hasPermission) {
        navigateTo("/401");
        return;
      }

      let html = await fetchAndCache(request);

      params.forEach((param, index) => {
        html = html.replace(new RegExp(`\\$\\{param${index + 1}\\}`, "g"), param);
      });

      for (const [component, componentPath] of Object.entries(components)) {
        const componentHtml = await fetchAndCache(componentPath);
        html = html.replace(new RegExp(component, "g"), componentHtml);
      }

      for (const [color, value] of Object.entries(colors)) {
        html = html.replace(new RegExp(color.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1"), "g"), value);
      }

      for (const [oldHref, newHref] of Object.entries(hrefReplace)) {
        html = html.replace(new RegExp(oldHref, "g"), newHref);
      }

      app.innerHTML = html;
      window.scrollTo(0, 0);

      // Execute embedded scripts
      html.split("<script>").forEach((script) => {
        if (script.includes("</script>")) {
          const scriptContent = script.split("</script>")[0];
          eval(scriptContent);
        }
      });

      // Handle embedded styles
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

  await fetch("/api/routes/router/router.php")
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
  const response = await fetch("/api/routes/auth/user.php", {
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
