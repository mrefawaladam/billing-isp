document.addEventListener("DOMContentLoaded", function() {
var at = document.documentElement.getAttribute("data-layout");
if ((at = "vertical")) {

  // ----------------------------------------
  // Active 2 file at same time
  // ----------------------------------------

  var currentURL =
    window.location != window.parent.location
      ? document.referrer
      : document.location.href;

  var link = document.getElementById("get-url");

  if (link) {
    if (currentURL.includes("/main/index.html")) {
      link.setAttribute("href", "../main/index.html");
    } else if (currentURL.includes("/index.html")) {
      link.setAttribute("href", "./index.html");
    } else {
      link.setAttribute("href", "./");
    }
  }

  function findMatchingElement() {
    var currentUrl = window.location.href;
    var anchors = document.querySelectorAll("#sidebarnav a");
    for (var i = 0; i < anchors.length; i++) {
      if (anchors[i].href === currentUrl) {
        return anchors[i];
      }
    }

    return null; // Return null if no matching element is found
  }
  var elements = findMatchingElement();

  // Do something with the matching element
  if (elements) {
    elements.classList.add("active");
  }

  document
    .querySelectorAll("ul#sidebarnav ul li a.active")
    .forEach(function (link) {
      link.closest("ul").classList.add("in");
      link.closest("ul").parentElement.classList.add("selected");
    });

  document.querySelectorAll("#sidebarnav li").forEach(function (li) {
    const isActive = li.classList.contains("selected");
    if (isActive) {
      const anchor = li.querySelector("a");
      if (anchor) {
        anchor.classList.add("active");
      }
    }
  });
  document.querySelectorAll("#sidebarnav a").forEach(function (link) {
    // Skip links that use Bootstrap collapse (has data-bs-toggle="collapse")
    if (link.hasAttribute("data-bs-toggle") && link.getAttribute("data-bs-toggle") === "collapse") {
      return;
    }

    link.addEventListener("click", function (e) {
      const isActive = this.classList.contains("active");
      const parentUl = this.closest("ul");
      if (!isActive) {
        // hide any open menus and remove all other classes
        parentUl.querySelectorAll("ul").forEach(function (submenu) {
          submenu.classList.remove("in");
        });
        parentUl.querySelectorAll("a").forEach(function (navLink) {
          navLink.classList.remove("active");
        });

        // open our new menu and add the open class
        const submenu = this.nextElementSibling;
        if (submenu) {
          submenu.classList.add("in");
        }

        this.classList.add("active");
      } else {
        this.classList.remove("active");
        parentUl.classList.remove("active");
        const submenu = this.nextElementSibling;
        if (submenu) {
          submenu.classList.remove("in");
        }
      }
    });
  });

  // Handle Bootstrap collapse events for dropdown menus
  document.querySelectorAll("#sidebarnav [data-bs-toggle='collapse']").forEach(function (link) {
    link.addEventListener("click", function (e) {
      const targetId = this.getAttribute("data-bs-target");
      const target = document.querySelector(targetId);
      if (target) {
        // Toggle active class on parent
        this.closest("li").classList.toggle("selected");
        // Update aria-expanded
        const isExpanded = target.classList.contains("show");
        this.setAttribute("aria-expanded", !isExpanded);
      }
    });
  });

  // Update collapse state when Bootstrap collapse events fire
  document.querySelectorAll("#sidebarnav .collapse").forEach(function (collapse) {
    collapse.addEventListener("show.bs.collapse", function () {
      const link = document.querySelector(`[data-bs-target="#${this.id}"]`);
      if (link) {
        link.closest("li").classList.add("selected");
        link.setAttribute("aria-expanded", "true");
        this.classList.add("in");
      }
    });

    collapse.addEventListener("hide.bs.collapse", function () {
      const link = document.querySelector(`[data-bs-target="#${this.id}"]`);
      if (link) {
        link.closest("li").classList.remove("selected");
        link.setAttribute("aria-expanded", "false");
        this.classList.remove("in");
      }
    });
  });
}
if ((at = "horizontal")) {
  function findMatchingElement() {
    var currentUrl = window.location.href;
    var anchors = document.querySelectorAll("#sidebarnavh ul#sidebarnav a");
    for (var i = 0; i < anchors.length; i++) {
      if (anchors[i].href === currentUrl) {
        return anchors[i];
      }
    }

    return null; // Return null if no matching element is found
  }
  var elements = findMatchingElement();

  if (elements) {
    elements.classList.add("active");
  }
  document
    .querySelectorAll("#sidebarnavh ul#sidebarnav a.active")
    .forEach(function (link) {
      link.closest("a").parentElement.classList.add("selected");
      link.closest("ul").parentElement.classList.add("selected");
    });
}
});
