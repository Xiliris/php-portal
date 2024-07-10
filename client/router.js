const route = (event) => {
  event = event || window.event;
  event.preventDefault();

  window.history.pushState({}, "", event.target.href);
  handleLocation();
};

const routes = {
  404: "./pages/errors/404.html",
  "/client/": "./pages/home.html",
  "/client/about": "./pages/about.html",
  "/client/contact": "./pages/contact.html",
};

const handleLocation = async () => {
  const path = window.location.pathname;
  const route = routes[path] || routes[404];

  console.log("path", path);

  console.log(routes[path]);

  const html = await fetch(route).then((response) => response.text());

  document.getElementById("root").innerHTML = html;
};

window.onpopstate = handleLocation;
window.onload = handleLocation;
window.route = route;
